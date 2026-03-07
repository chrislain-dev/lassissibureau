<?php

namespace App\Livewire\Sales;

use App\Enums\SaleType;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Reseller;
use App\Services\SaleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateSale extends Component
{
    // ---------------------------------------------------------------
    // Mode : accessoire (stock par quantité) vs produit individuel
    // ---------------------------------------------------------------
    public bool $is_accessoire = false;

    // Accessoire
    public $product_model_id = null;  // pour les accessoires
    public int $quantity_vendue = 1;

    // Produit individuel
    public ?Product $preselectedProduct = null;

    public $product_id = null;

    public $prix_vente = null;

    public $prix_achat_produit = null;

    // Type de vente
    public $sale_type = 'achat_direct';

    // Type d'acheteur
    public $buyer_type = 'direct';

    // Client
    public $client_name = null;

    public $client_phone = null;

    // Revendeur
    public $reseller_id = null;

    public $date_depot_revendeur = null;

    public $reseller_confirm_immediate = false; // Nouvelle option

    // Paiement
    public $payment_status = 'unpaid';

    public $payment_option = 'unpaid';

    public $amount_paid = 0;

    public $payment_due_date = null;

    public $payment_method = 'cash';

    // Troc
    public $has_trade_in = false;

    public $trade_in_modele_recu = null;

    public $trade_in_imei_recu = null;

    public $trade_in_valeur_reprise = 0;

    public $trade_in_complement_especes = 0;

    public $trade_in_etat_recu = null;

    // Notes
    public $notes = null;

    // Collections
    public $availableProducts;

    public $accessoireModels;

    public $resellers;

    public function mount(?int $productId = null, ?int $productModelId = null)
    {
        $productId = $productId
            ?? (int) request()->query('productId')
            ?: (int) request()->query('product')
            ?: null;

        $productModelId = $productModelId
            ?? (int) request()->query('productModelId')
            ?: null;

        $this->date_depot_revendeur = now()->format('Y-m-d');
        $this->payment_due_date = now()->addDays(30)->format('Y-m-d');

        // Charger les produits disponibles (téléphones, tablettes, PC)
        $this->availableProducts = Product::availableForSale()
            ->with('productModel')
            ->get();

        // Charger les modèles d'accessoires avec du stock disponible
        $this->accessoireModels = ProductModel::where('category', 'accessoire')
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        // Charger les revendeurs actifs
        $this->resellers = Reseller::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Si modèle d'accessoire présélectionné
        if ($productModelId) {
            $model = ProductModel::find($productModelId);
            if ($model && $model->category->value === 'accessoire') {
                $this->is_accessoire = true;
                $this->product_model_id = $model->id;
                $this->prix_vente = $this->buyer_type === 'reseller'
                    ? $model->prix_vente_revendeur
                    : $model->prix_vente_default;
                $this->prix_achat_produit = $model->prix_revient_default;
                $this->sale_type = 'achat_direct';
            }
        }
        // Si produit présélectionné
        elseif ($productId) {
            $this->preselectedProduct = Product::with('productModel')->find($productId);
            if ($this->preselectedProduct) {
                if ($this->preselectedProduct->productModel && $this->preselectedProduct->productModel->category->value === 'accessoire') {
                    $this->is_accessoire = true;
                    $this->product_model_id = $this->preselectedProduct->product_model_id;
                    $this->prix_vente = $this->buyer_type === 'reseller'
                        ? $this->preselectedProduct->productModel->prix_vente_revendeur
                        : $this->preselectedProduct->productModel->prix_vente_default;
                    $this->prix_achat_produit = $this->preselectedProduct->productModel->prix_revient_default;
                    $this->sale_type = 'achat_direct'; // Force achat direct pour les accessoires
                } else {
                    $this->product_id = $this->preselectedProduct->id;
                    $this->prix_vente = $this->buyer_type === 'reseller'
                        ? $this->preselectedProduct->prix_vente_revendeur
                        : $this->preselectedProduct->prix_vente;
                    $this->prix_achat_produit = $this->preselectedProduct->prix_achat;
                }
            }
        }
    }

    /**
     * Quand on change le mode accessoire/produit
     */
    public function updatedIsAccessoire(bool $value): void
    {
        // Réinitialiser les champs liés à l'autre mode
        if ($value) {
            $this->product_id         = null;
            $this->preselectedProduct = null;
            $this->prix_achat_produit = null;
            $this->quantity_vendue    = 1;
            $this->sale_type          = 'achat_direct'; // Forcer la vente directe pour les accessoires
            $this->has_trade_in       = false;
        } else {
            $this->product_model_id = null;
            $this->prix_vente       = null;
            $this->quantity_vendue  = 1;
        }
    }

    /**
     * Quand on sélectionne un modèle d'accessoire
     */
    public function updatedProductModelId($value): void
    {
        if ($value && $this->is_accessoire) {
            $model = ProductModel::find($value);
            if ($model) {
                $this->prix_vente         = $model->prix_vente_default;
                $this->prix_achat_produit = $model->prix_revient_default;
            }
        }
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $product = Product::find($value);
            if ($product) {
                $this->prix_vente = $this->buyer_type === 'reseller'
                    ? $product->prix_vente_revendeur
                    : $product->prix_vente;
                $this->prix_achat_produit = $product->prix_achat;
                $this->calculateComplement();
            }
        }
    }

    public function updatedSaleType($value)
    {
        $this->has_trade_in = ($value === 'troc');
        if ($this->has_trade_in) {
            $this->calculateComplement();
        }
    }

    public function updatedBuyerType()
    {
        if ($this->buyer_type === 'direct') {
            $this->reseller_confirm_immediate = false;
        }

        if ($this->is_accessoire) {
            $model = ProductModel::find($this->product_model_id);
            if ($model) {
                $this->prix_vente = $this->buyer_type === 'reseller'
                    ? $model->prix_vente_revendeur
                    : $model->prix_vente_default;
            }
        } elseif ($this->product_id) {
            $product = Product::find($this->product_id);
            if ($product) {
                $this->prix_vente = $this->buyer_type === 'reseller'
                    ? $product->prix_vente_revendeur
                    : $product->prix_vente;
            }
        }

        if ($this->isImmediateSale()) {
            $this->payment_status = 'paid';
            $this->payment_option = 'paid';
            $this->amount_paid = $this->prix_vente;
            $this->payment_due_date = null;
            return;
        }
    }

    public function updatedResellerConfirmImmediate()
    {
        if ($this->isImmediateSale()) {
            $this->payment_status = 'paid';
            $this->payment_option = 'paid';
            $this->amount_paid = $this->prix_vente;
            $this->payment_due_date = null;
        } else {
            $this->payment_status = 'unpaid';
            $this->payment_option = 'unpaid';
            $this->amount_paid = 0;
        }
    }

    public function updatedPaymentOption($value)
    {
        if ($this->isImmediateSale()) {
            $this->payment_option = 'paid';
            $this->payment_status = 'paid';
            $this->amount_paid = $this->prix_vente;
            return;
        }

        $this->payment_status = $value;
        if ($value === 'unpaid') {
            $this->amount_paid = 0;
        }
    }

    public function updatedTradeInValeurReprise($value)
    {
        $this->calculateComplement();
    }

    public function updatedPrixVente($value)
    {
        $this->calculateComplement();
    }

    private function isImmediateSale(): bool
    {
        return
            $this->buyer_type === 'direct'
            || ($this->buyer_type === 'reseller' && $this->reseller_confirm_immediate);
    }

    private function calculateComplement()
    {
        if ($this->has_trade_in) {
            $prixVente = (float) ($this->prix_vente ?? 0);
            $valeurReprise = (float) ($this->trade_in_valeur_reprise ?? 0);
            $this->trade_in_complement_especes = max(0, $prixVente - $valeurReprise);
        }
    }

    public function save(SaleService $saleService)
    {
        // --- Mode accessoire ---
        if ($this->is_accessoire) {
            $this->validate([
                'product_model_id' => ['required', 'exists:product_models,id'],
                'quantity_vendue'  => ['required', 'integer', 'min:1', 'max:9999'],
                'prix_vente'       => ['required', 'numeric', 'min:0'],
                'client_name'      => ['nullable', 'string', 'max:255'],
                'client_phone'     => ['nullable', 'string', 'max:20'],
                'payment_method'   => ['required', 'string'],
            ]);

            try {
                $data = [
                    'is_accessoire'        => true,
                    'product_model_id'     => $this->product_model_id,
                    'quantity_vendue'      => $this->quantity_vendue,
                    'sale_type'            => SaleType::ACHAT_DIRECT->value,
                    'prix_vente'           => $this->prix_vente,
                    'prix_achat_produit'   => $this->prix_achat_produit,
                    'client_name'          => $this->client_name,
                    'client_phone'         => $this->client_phone,
                    'date_vente_effective' => now()->format('Y-m-d'),
                    'is_confirmed'         => true,
                    'payment_method'       => $this->payment_method,
                    'sold_by'              => Auth::id(),
                    'notes'                => $this->notes,
                ];

                $sale = $saleService->createSale($data);

                session()->flash('success', 'Vente d\'accessoire enregistrée avec succès.');

                return redirect()->route('sales.show', $sale);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erreur vente accessoire: '.$e->getMessage());
                session()->flash('error', 'Erreur : '.$e->getMessage());
            }

            return;
        }

        // --- Mode produit individuel (téléphone, tablette, PC) ---
        // 1. Préparer les données pour la validation (incluant les mappages Livewire -> Request)
        $dataForValidation = array_merge($this->all(), [
            'product_id' => $this->product_id,
            'payment_status' => $this->isImmediateSale() ? 'paid' : $this->payment_option,
            'date_vente_effective' => now()->format('Y-m-d'),
            'is_confirmed' => $this->buyer_type === 'direct' || ($this->buyer_type === 'reseller' && $this->reseller_confirm_immediate),
        ]);

        // Si troc, mapper les champs plats vers nested pour StoreSaleRequest
        if ($this->has_trade_in) {
            $dataForValidation['trade_in'] = [
                'modele_recu' => $this->trade_in_modele_recu,
                'imei_recu' => $this->trade_in_imei_recu,
                'valeur_reprise' => $this->trade_in_valeur_reprise,
                'complement_especes' => $this->trade_in_complement_especes,
                'etat_recu' => $this->trade_in_etat_recu,
            ];
        }

        // 2. Valider
        $request = new StoreSaleRequest;
        // On merge les data dans le request pour que $this->product_id fonctionne dans StoreSaleRequest
        $request->merge($dataForValidation);
        
        $validator = \Illuminate\Support\Facades\Validator::make(
            $dataForValidation, 
            $request->rules(), 
            $request->messages()
        );
        $request->withValidator($validator);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());
            return;
        }

        $isImmediate = $this->isImmediateSale();

        try {
            // 3. Préparer les données pour le service
            $data = [
                'product_id' => $this->product_id,
                'sale_type' => $this->sale_type,
                'prix_vente' => $this->prix_vente,
                'prix_achat_produit' => $this->prix_achat_produit,
                'client_name' => $this->client_name,
                'client_phone' => $this->client_phone,
                'reseller_id' => $this->buyer_type === 'reseller' ? $this->reseller_id : null,
                'date_depot_revendeur' => $this->buyer_type === 'reseller' ? $this->date_depot_revendeur : null,
                'date_vente_effective' => $dataForValidation['date_vente_effective'],
                'payment_status' => $isImmediate ? 'paid' : $this->payment_option,
                'amount_paid' => $isImmediate ? $this->prix_vente : ($this->amount_paid ?? 0),
                'payment_due_date' => $isImmediate ? null : $this->payment_due_date,
                'is_confirmed' => $isImmediate,
                'payment_method' => $this->payment_method,
                'sold_by' => Auth::id(),
                'notes' => $this->notes,
            ];

            // Ajouter les données de troc si nécessaire
            if ($this->has_trade_in && $this->sale_type === 'troc') {
                $data['has_trade_in'] = true;
                $data['trade_in'] = [
                    'modele_recu' => $this->trade_in_modele_recu,
                    'imei_recu' => $this->trade_in_imei_recu,
                    'valeur_reprise' => $this->trade_in_valeur_reprise,
                    'complement_especes' => $this->trade_in_complement_especes,
                    'etat_recu' => $this->trade_in_etat_recu,
                ];
            }

            // Créer la vente
            $sale = $saleService->createSale($data);

            session()->flash('success', 'Vente enregistrée avec succès.');

            return redirect()->route('sales.show', $sale);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur CreateSale: '.$e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sales.create-sale')
            ->title('Nouvelle vente')
            ->layout('layouts.app', [
                'header' => 'Nouvelle vente',
            ]);
    }
}
