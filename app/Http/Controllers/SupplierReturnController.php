<?php

namespace App\Http\Controllers;

use App\Enums\CustomerReturnStatus;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Enums\SupplierReturnStatus;
use App\Models\CustomerReturn;
use App\Models\Product;
use App\Models\SupplierReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    /**
     * Liste des retours fournisseurs
     */
    public function index()
    {
        $this->authorize('admin');

        $supplierReturns = SupplierReturn::with([
            'product.productModel',
            'customerReturn',
            'replacementProduct.productModel',
            'processor',
        ])->latest()->paginate(20);

        return view('supplier-returns.index', compact('supplierReturns'));
    }

    /**
     * Afficher un retour fournisseur
     */
    public function show(SupplierReturn $supplierReturn)
    {
        $supplierReturn->load([
            'product.productModel',
            'customerReturn.returnedProduct.productModel',
            'customerReturn.originalSale',
            'replacementProduct.productModel',
            'processor',
        ]);

        return view('supplier-returns.show', compact('supplierReturn'));
    }

    /**
     * Créer un retour fournisseur depuis un retour client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_return_id' => ['nullable', 'exists:customer_returns,id'],
            'product_id'         => ['required', 'exists:products,id'],
            'motif'              => ['required', 'string', 'min:10'],
            'date_envoi'         => ['required', 'date'],
            'date_retour_prevue' => ['nullable', 'date', 'after:date_envoi'],
            'notes'              => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $supplierReturn = SupplierReturn::create([
                ...$validated,
                'statut'       => SupplierReturnStatus::EN_ATTENTE,
                'processed_by' => Auth::id(),
            ]);

            // Mettre à jour le statut du retour client si lié
            if ($validated['customer_return_id']) {
                CustomerReturn::find($validated['customer_return_id'])->update([
                    'status'             => CustomerReturnStatus::RETOUR_FOURNISSEUR,
                    'supplier_return_id' => $supplierReturn->id,
                ]);
            }

            // Changer l'état du produit → envoi au fournisseur
            $product = Product::findOrFail($validated['product_id']);
            $product->changeStateAndLocation(
                StockMovementType::RETOUR_FOURNISSEUR->value,
                ProductState::RETOUR,
                ProductLocation::BOUTIQUE, // il sort physiquement, on garde trace
                Auth::id(),
                ['notes' => 'Envoyé au fournisseur : ' . $validated['motif']]
            );
        });

        return redirect()->route('supplier-returns.index')
            ->with('success', 'Retour fournisseur enregistré avec succès.');
    }

    /**
     * Confirmer la réception du remplacement par le fournisseur
     * Le nouveau produit remplace totalement l'ancien
     */
    public function confirmReplacement(Request $request, SupplierReturn $supplierReturn)
    {
        $validated = $request->validate([
            'new_imei'         => ['nullable', 'string'],
            'new_serial_number' => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
        ]);

        if ($supplierReturn->isReplaced()) {
            return back()->with('error', 'Ce retour a déjà été remplacé.');
        }

        DB::transaction(function () use ($validated, $supplierReturn) {
            $oldProduct = $supplierReturn->product;

            // 1. Archiver l'ancien produit
            $oldProduct->changeStateAndLocation(
                StockMovementType::RETOUR_FOURNISSEUR->value,
                ProductState::RETOUR,
                $oldProduct->location,
                Auth::id(),
                ['notes' => 'Remplacé par nouveau produit fournisseur']
            );
            $oldProduct->delete(); // soft delete

            // 2. Créer le nouveau produit (même modèle, nouvelles infos)
            $newProduct = Product::create([
                'product_model_id' => $oldProduct->product_model_id,
                'imei'             => $validated['new_imei'] ?? null,
                'serial_number'    => $validated['new_serial_number'] ?? null,
                'state'            => ProductState::DISPONIBLE->value,
                'location'         => ProductLocation::BOUTIQUE->value,
                'date_achat'       => now(),
                'notes'            => $validated['notes'] ?? 'Produit de remplacement fournisseur - Retour #' . $supplierReturn->id,
                'fournisseur'      => $oldProduct->fournisseur,
                'condition'        => $oldProduct->condition,
                'created_by'       => Auth::id(),
            ]);

            // 3. Mouvement de stock d'entrée
            $newProduct->changeStateAndLocation(
                StockMovementType::RECEPTION_FOURNISSEUR->value,
                ProductState::DISPONIBLE,
                ProductLocation::BOUTIQUE,
                Auth::id(),
                ['notes' => 'Produit de remplacement reçu du fournisseur - Retour #' . $supplierReturn->id]
            );

            // 4. Mettre à jour le retour fournisseur
            $supplierReturn->update([
                'statut'                 => SupplierReturnStatus::REMPLACE,
                'replacement_product_id' => $newProduct->id,
                'date_retour_effective'  => now(),
            ]);

            // 5. Mettre à jour le retour client si lié
            if ($supplierReturn->customerReturn) {
                $supplierReturn->customerReturn->update([
                    'status' => CustomerReturnStatus::REMPLACE,
                ]);
            }
        });

        return redirect()->route('supplier-returns.show', $supplierReturn)
            ->with('success', 'Remplacement confirmé. Le nouveau produit est en stock.');
    }

    /**
     * Marquer le produit comme reçu par le fournisseur (sans remplacement encore)
     */
    public function markAsReceived(SupplierReturn $supplierReturn)
    {
        $supplierReturn->update(['statut' => SupplierReturnStatus::RECU]);

        return back()->with('success', 'Retour marqué comme reçu par le fournisseur.');
    }
}
