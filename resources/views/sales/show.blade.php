<x-app-layout>
    <x-slot name="header">
        Vente #{{ $sale->id }}
    </x-slot>

    <x-slot name="actions">
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour
            </a>

            @if(!$sale->is_confirmed && $sale->reseller_id)
                @can('returnFromReseller', $sale)
                    <button @click="$dispatch('open-modal-return-sale')" class="inline-flex items-center gap-2 px-3 py-2 bg-orange-600 rounded-lg text-sm font-medium text-white hover:bg-orange-700 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Retour stock
                    </button>
                @endcan

                @can('confirm', $sale)
                    <button @click="$dispatch('open-modal-confirm-sale')" class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-600 rounded-lg text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Confirmer
                    </button>
                @endcan
            @endif

            @can('delete', $sale)
                <button @click="$dispatch('open-modal-delete-sale')" class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700 transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Supprimer
                </button>
            @endcan
        </div>
    </x-slot>

    <x-alerts.success :message="session('success')" />
    <x-alerts.error :message="session('error')" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informations de la vente --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Informations de la vente</h3>
                        <p class="text-xs text-gray-500">Détails de la transaction</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Type de vente</dt>
                        <dd>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $sale->sale_type->value === 'achat_direct' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                {{ $sale->sale_type->label() }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Date de vente</dt>
                        <dd class="text-sm text-gray-900 font-medium">{{ $sale->date_vente_effective->format('d/m/Y') }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Vendu par</dt>
                        <dd class="text-sm text-gray-900">{{ $sale->seller->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Statut</dt>
                        <dd>
                            @if($sale->is_confirmed)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    Confirmée
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    En attente
                                </span>
                            @endif
                        </dd>
                    </div>

                    @if($sale->reseller_id)
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Revendeur</dt>
                            <dd class="text-sm text-gray-900 font-medium">{{ $sale->reseller->name }}</dd>
                            @if($sale->date_depot_revendeur)
                                <p class="text-xs text-gray-500 mt-1">Déposé le {{ $sale->date_depot_revendeur->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Produit vendu --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Produit vendu</h3>
                        <p class="text-xs text-gray-500">Détails du produit</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @php
                            $categoryIcons = ['telephone' => 'smartphone', 'tablette' => 'tablet', 'pc' => 'monitor', 'accessoire' => 'box'];
                            $categoryValue = $sale->product ? $sale->product->productModel->category->value : ($sale->productModel ? $sale->productModel->category->value : 'accessoire');
                            $icon = $categoryIcons[$categoryValue] ?? 'box';
                            
                            $modelName = $sale->product ? $sale->product->productModel->name : ($sale->productModel ? $sale->productModel->name : 'N/A');
                            $modelBrand = $sale->product ? $sale->product->productModel->brand : ($sale->productModel ? $sale->productModel->brand : 'N/A');
                        @endphp
                        <i data-lucide="{{ $icon }}" class="w-8 h-8 text-gray-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-gray-900">{{ $modelName }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $modelBrand }}
                            @if($sale->product_model_id && $sale->quantity_vendue > 1)
                                &mdash; Quantité : <span class="font-semibold">{{ $sale->quantity_vendue }}</span>
                            @endif
                        </p>

                        @if($sale->product && $sale->product->imei)
                            <p class="text-xs font-mono text-gray-500 mt-2 bg-gray-50 inline-block px-2 py-1 rounded">
                                IMEI: {{ $sale->product->imei }}
                            </p>
                        @endif

                        <div class="mt-3">
                            @if($sale->product)
                                <a href="{{ route('products.show', $sale->product) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Voir le produit →
                                </a>
                            @elseif($sale->productModel)
                                <a href="{{ route('product-models.show', $sale->productModel) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Voir le modèle d'accessoire →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Client --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Informations client</h3>
                        <p class="text-xs text-gray-500">Coordonnées de l'acheteur</p>
                    </div>
                </div>

                @if($sale->client_name || $sale->client_phone)
                    <dl class="space-y-3">
                        @if($sale->client_name)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Nom</dt>
                                <dd class="text-sm text-gray-900">{{ $sale->client_name }}</dd>
                            </div>
                        @endif

                        @if($sale->client_phone)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Téléphone</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $sale->client_phone }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="text-sm text-gray-500">Client anonyme</p>
                @endif
            </div>

            @if($sale->tradeIn)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                                <i data-lucide="repeat" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Troc avec reprise</h3>
                                <p class="text-xs text-gray-500">Produit reçu en échange</p>
                            </div>
                        </div>

                        {{-- Statut du troc --}}
                        @if($sale->tradeIn->isPending())
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                En attente de traitement
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Produit créé
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Modèle reçu</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $sale->tradeIn->modele_recu }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">IMEI reçu</p>
                            <p class="text-sm font-mono text-gray-900">{{ $sale->tradeIn->imei_recu }}</p>
                        </div>

                        @if($sale->tradeIn->etat_recu)
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">État constaté</p>
                                <p class="text-sm text-gray-700">{{ $sale->tradeIn->etat_recu }}</p>
                            </div>
                        @endif

                        <div class="md:col-span-2 grid grid-cols-3 gap-4 p-4 bg-red-50 rounded-lg">
                            <div>
                                <p class="text-xs text-red-700 uppercase tracking-wide mb-1">Valeur de reprise</p>
                                <p class="text-lg font-bold text-red-900">{{ number_format($sale->tradeIn->valeur_reprise, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div>
                                <p class="text-xs text-red-700 uppercase tracking-wide mb-1">Complément espèces</p>
                                <p class="text-lg font-bold text-red-900">{{ number_format($sale->tradeIn->complement_especes, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div>
                                <p class="text-xs text-red-700 uppercase tracking-wide mb-1">Total</p>
                                <p class="text-lg font-bold text-red-900">{{ number_format($sale->prix_vente, 0, ',', ' ') }} FCFA</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action selon le statut --}}
                    @if($sale->tradeIn->isPending())
                        @can('create', App\Models\TradeIn::class)
                            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="text-sm font-medium text-amber-900">Produit en attente de création</p>
                                            <p class="text-xs text-amber-700 mt-1">Le téléphone reçu en troc doit être ajouté au stock pour être disponible à la vente.</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('trade-ins.create-product', $sale->tradeIn) }}" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        Créer le produit
                                    </a>
                                </div>
                            </div>
                        @endcan
                    @else
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                                    <p class="text-sm font-medium text-green-900">Produit créé et ajouté au stock</p>
                                </div>
                                <a href="{{ route('products.show', $sale->tradeIn->productReceived) }}" class="inline-flex items-center gap-2 text-sm text-green-700 hover:text-green-900 font-medium">
                                    Voir le produit
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Notes --}}
            @if($sale->notes)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Notes</h3>
                    </div>
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $sale->notes }}</p>
                </div>
            @endif
        </div>

            {{-- Paiements --}}
            @if($sale->payments->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="banknote" class="w-4 h-4 text-white"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Historique des paiements</h3>
                        <span class="ml-auto text-xs bg-gray-100 text-gray-600 font-medium px-2 py-1 rounded-full">{{ $sale->payments->count() }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($sale->payments as $payment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $payment->payment_method->label() }}
                                            @if($payment->reference) · <span class="font-mono">{{ $payment->reference }}</span> @endif
                                        </p>
                                        @if($payment->notes)
                                            <p class="text-xs text-gray-400 italic">{{ $payment->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-700">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $payment->created_at->format('H:i') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $payment->recorder->name ?? 'Système' }}</p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Total payé --}}
                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                            <span class="text-sm font-semibold text-gray-700">Total payé</span>
                            <span class="text-sm font-bold text-emerald-600">{{ number_format($sale->amount_paid, 0, ',', ' ') }} FCFA</span>
                        </div>
                        @if($sale->amount_remaining > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Reste dû</span>
                                <span class="text-sm font-bold text-red-600">{{ number_format($sale->amount_remaining, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if($sale->payment_due_date)
                                <p class="text-xs text-gray-500 text-right">
                                    Échéance : {{ $sale->payment_due_date->format('d/m/Y') }}
                                    @if($sale->payment_due_date->isPast()) <span class="text-red-500 font-medium">(dépassée)</span> @endif
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- Prix et montants (sidebar existante) --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Récapitulatif financier</h3>
                <dl class="space-y-3">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Prix de vente</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ number_format($sale->prix_vente, 0, ',', ' ') }} <span class="text-xs font-normal text-gray-500">FCFA</span></dd>
                    </div>

                    @if($sale->hasTradeIn() && $sale->tradeIn)
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-red-700 uppercase tracking-wide">Valeur reprise</span>
                                <span class="text-sm font-semibold text-red-700">{{ number_format($sale->tradeIn->valeur_reprise, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-red-300">
                                <span class="text-xs text-red-700 uppercase tracking-wide font-medium">Espèces reçues</span>
                                <span class="text-lg font-bold text-red-700">{{ number_format($sale->tradeIn->complement_especes, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->hasRole('admin'))
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                            <dt class="text-xs font-medium text-emerald-700 uppercase tracking-wide mb-1 flex items-center gap-1">
                                <i data-lucide="trending-up" class="w-3 h-3"></i> Bénéfice
                            </dt>
                            <dd class="text-2xl font-bold text-emerald-600">+{{ number_format($sale->benefice, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></dd>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Marge</span>
                            <span class="text-lg font-bold text-gray-900">{{ $sale->marge_percentage }}%</span>
                        </div>
                    @endif
                </dl>
            </div>
    </div>

    {{-- Modals Alpine.js --}}
    @if(!$sale->is_confirmed && $sale->reseller_id)
        {{-- Modal Confirmation vente --}}
        @can('confirm', $sale)
            <x-confirm-modal
                id="confirm-sale"
                title="Confirmer cette vente ?"
                message="Confirmez que cette vente a bien été effectuée par le revendeur."
                :danger="false"
            >
                <x-slot name="form">
                    <form method="POST" action="{{ route('sales.confirm', $sale) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                            Confirmer la vente
                        </button>
                    </form>
                </x-slot>
            </x-confirm-modal>
        @endcan

        {{-- Modal Retour stock --}}
        @can('returnFromReseller', $sale)
            <div
                x-data="{ open: false }"
                x-on:open-modal-return-sale.window="open = true"
                x-show="open"
                x-cloak
                class="fixed inset-0 z-50"
            >
                <div x-show="open" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="open = false"></div>
                <div class="fixed inset-0 flex items-center justify-center p-4 z-10">
                    <div x-show="open"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-gray-100">
                        <form method="POST" action="{{ route('sales.return', $sale) }}">
                            @csrf
                            <div class="p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="rotate-ccw" class="w-5 h-5 text-orange-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">Retourner au stock</h3>
                                        <p class="text-sm text-gray-500">Cela annulera la vente et remettra le produit en stock.</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="reason" class="block text-xs font-medium text-gray-700 mb-1.5">Motif du retour *</label>
                                    <textarea name="reason" id="reason" rows="3" required minlength="10" placeholder="Ex: Produit non vendu par le revendeur..." class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"></textarea>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Annuler</button>
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i data-lucide="check" class="w-4 h-4"></i> Valider le retour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    {{-- Modal Supprimer vente --}}
    @can('delete', $sale)
        <div
            x-data="{ open: false }"
            x-on:open-modal-delete-sale.window="open = true"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50"
        >
            <div x-show="open" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 z-10">
                <div x-show="open"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-gray-100">
                    <form method="POST" action="{{ route('sales.destroy', $sale) }}">
                        @csrf
                        @method('DELETE')
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Supprimer cette vente ?</h3>
                                    <p class="text-sm text-gray-500">La vente #{{ $sale->id }} sera supprimée et le produit retournera en stock.</p>
                                </div>
                            </div>
                            <div>
                                <label for="delete_reason" class="block text-xs font-medium text-gray-700 mb-1.5">Motif de suppression (optionnel)</label>
                                <textarea name="reason" id="delete_reason" rows="3" placeholder="Ex: Erreur de saisie, client a changé d'avis..." class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                            <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Annuler</button>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer définitivement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

</x-app-layout>
