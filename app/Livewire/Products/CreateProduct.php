<?php

namespace App\Livewire\Products;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Http\Requests\StoreProductRequest;
use App\Models\ProductModel;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateProduct extends Component
{
    // Informations communes
    public $product_model_id = null;
    
    public $state = 'disponible';
    public $location = 'boutique';
    public $condition = null;
    
    public $date_achat;
    public $fournisseur = '';
    public $defauts = '';
    public $notes = '';

    // Produits multiples
    public $products = [];

    // ✅ Protection contre les soumissions multiples
    public $isSaving = false;

    // Conditions disponibles
    public $conditions = [
        'Neuf',
        'Comme neuf',
        'Très bon état',
        'Bon état',
        'État correct',
        'Pour pièces',
    ];

    public function mount()
    {
        $this->date_achat = now()->format('Y-m-d');
        $this->addProduct();
    }

    // ✅ FIX CRITIQUE: Hook Livewire pour convertir string vide en null
    public function updatedCondition($value)
    {
        // Si la valeur est une chaîne vide, on la force à null
        if ($value === '' || $value === null) {
            $this->condition = null;
        }
    }

    // ✅ PROTECTION: Empêcher la validation automatique lors de la sélection du modèle
    public function updatedProductModelId($value)
    {
        // Ne rien faire ici, juste charger le modèle sélectionné
        // La validation se fera uniquement lors de la soumission
    }

    #[Computed]
    public function productModels()
    {
        return ProductModel::orderBy('brand')->orderBy('name')->get();
    }

    #[Computed]
    public function selectedModel()
    {
        if (!$this->product_model_id) {
            return null;
        }
        return ProductModel::find($this->product_model_id);
    }

    #[Computed]
    public function states()
    {
        return ProductState::options();
    }

    #[Computed]
    public function locations()
    {
        return ProductLocation::options();
    }

    public function addProduct()
    {
        $this->products[] = [
            'id' => uniqid(),
            'imei' => '',
            'serial_number' => '',
        ];
    }

    public function removeProduct($index)
    {
        if (count($this->products) > 1) {
            unset($this->products[$index]);
            $this->products = array_values($this->products);
        }
    }

    public function rules()
    {
        $request = new StoreProductRequest;
        $baseRules = $request->rules();

        $rules = [
            'product_model_id' => $baseRules['product_model_id'],
            'state' => $baseRules['state'],
            'location' => $baseRules['location'],
            'condition' => ['nullable', 'string', 'in:' . implode(',', $this->conditions)],
            'date_achat' => $baseRules['date_achat'],
            'fournisseur' => $baseRules['fournisseur'],
            'defauts' => $baseRules['defauts'],
            'notes' => $baseRules['notes'],
        ];

        // Validation dynamique des produits
        foreach ($this->products as $index => $product) {
            $rules["products.{$index}.imei"] = $baseRules['imei'];
            $rules["products.{$index}.serial_number"] = $baseRules['serial_number'];
        }

        return $rules;
    }

    public function messages()
    {
        $request = new StoreProductRequest;
        $messages = $request->messages();

        $messages['condition.in'] = 'La condition sélectionnée n\'est pas valide.';

        // Messages pour les produits
        foreach ($this->products as $index => $product) {
            $messages["products.{$index}.imei.size"] = "L'IMEI du produit ".($index + 1).' doit contenir exactement 15 chiffres.';
            $messages["products.{$index}.imei.unique"] = "L'IMEI du produit ".($index + 1).' existe déjà dans la base de données.';
            $messages["products.{$index}.imei.regex"] = "L'IMEI du produit ".($index + 1).' est invalide.';
            $messages["products.{$index}.serial_number.unique"] = 'Le numéro de série du produit '.($index + 1).' existe déjà.';
        }

        return $messages;
    }

    public function save(ProductService $productService)
    {
        // ✅ Protection contre double soumission
        if ($this->isSaving) {
            return;
        }

        $this->isSaving = true;

        try {
            $this->validate();

            $userId = Auth::id();
            $createdProducts = [];
            $category = null;

            DB::transaction(function () use ($productService, $userId, &$createdProducts, &$category) {
                foreach ($this->products as $productData) {
                    if (!empty($productData['imei']) || !empty($productData['serial_number'])) {

                        $imei = isset($productData['imei']) ? Str::replaceMatches('/[^0-9]/', '', $productData['imei']) : null;

                        $data = [
                            'product_model_id' => $this->product_model_id,
                            'imei' => $imei,
                            'serial_number' => $productData['serial_number'] ?? null,
                            'state' => $this->state,
                            'location' => $this->location,
                            'condition' => $this->condition ?: null,
                            'date_achat' => $this->date_achat ?: null,
                            'fournisseur' => $this->fournisseur ?: null,
                            'defauts' => $this->defauts ?: null,
                            'notes' => $this->notes ?: null,
                            'created_by' => $userId,
                        ];

                        $product = $productService->createProduct($data);
                        $createdProducts[] = $product;
                        
                        // Récupérer la catégorie du produit créé
                        if ($category === null && $product->productModel) {
                            $category = $product->productModel->category->value;
                        }
                    }
                }
            });

            $count = count($createdProducts);

            session()->flash('success', $count > 1
                ? "{$count} produits ont été créés avec succès (historique de stock généré) !"
                : 'Le produit a été créé avec succès (historique de stock généré) !');

            // Redirection avec step=2 et selectedCategory
            return redirect()->route('products.index', [
                'step' => 2,
                'selectedCategory' => $category ?? 'telephone'
            ]);
        } finally {
            $this->isSaving = false;
        }
    }

    public function render()
    {
        return view('livewire.products.create-product')->layout('layouts.app', ['title' => 'Créer un produit']);
    }
}