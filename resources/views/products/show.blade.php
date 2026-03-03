<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Produits</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span class="text-sm text-gray-900 font-medium">{{ $product->productModel->name }}</span>
                </div>
                <h1 class="text-xl lg:text-2xl font-bold text-gray-900 truncate">
                    {{ $product->productModel->brand }} — {{ $product->productModel->name }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <x-products.state-badge :state="$product->state" class="text-xs px-2.5 py-1" />
                    <x-products.location-badge :location="$product->location" class="text-xs px-2.5 py-1" />
                    @if($product->productModel->condition_type)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $product->productModel->condition_type->value === 'occasion' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                            {{ $product->productModel->condition_type->label() }}
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">#{{ $product->id }}</span>
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0 flex-wrap">
                @can('update', $product)
                    <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        Modifier
                    </a>
                @endcan
                @can('sell', $product)
                    @if($product->isAvailable())
                        <a href="{{ route('sales.create', ['productId' => $product->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-900 rounded-lg text-sm font-medium text-white hover:bg-gray-700 transition-colors shadow-sm">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                            Vendre
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <x-alerts.success :message="session('success')" />
    <x-alerts.error :message="session('error')" />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ============================================================ --}}
        {{-- COLONNE PRINCIPALE --}}
        {{-- ============================================================ --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- TIMELINE COMPLÈTE : Fil de vie du produit --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="git-branch" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Historique complet</h3>
                        <p class="text-xs text-gray-500">Entrée → Sortie du stock</p>
                    </div>
                    <span class="ml-auto text-xs font-medium bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                        {{ $product->stockMovements->count() }} événements
                    </span>
                </div>

                <div class="p-5">
                    @if($product->stockMovements->isNotEmpty())
                        <div class="relative">
                            {{-- Ligne verticale --}}
                            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-100"></div>

                            <div class="space-y-1">
                                @foreach($product->stockMovements as $movement)
                                    @php
                                        $colors = [
                                            'green'  => 'bg-emerald-500',
                                            'blue'   => 'bg-blue-500',
                                            'orange' => 'bg-orange-500',
                                            'yellow' => 'bg-amber-500',
                                            'red'    => 'bg-red-500',
                                            'purple' => 'bg-purple-500',
                                        ];
                                        $dotColor = $colors[$movement->type->color()] ?? 'bg-gray-400';
                                    @endphp
                                    <div class="relative flex gap-4 pl-12 pb-5">
                                        {{-- Point sur la timeline --}}
                                        <div class="absolute left-3.5 top-1.5 w-3 h-3 rounded-full border-2 border-white shadow {{ $dotColor }} flex-shrink-0 z-10"></div>

                                        {{-- Carte événement --}}
                                        <div class="flex-1 bg-gray-50 rounded-xl border border-gray-100 p-4 hover:border-gray-200 transition-colors">
                                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold
                                                            {{ str_replace('bg-', 'bg-', $dotColor) }} text-white">
                                                            <i data-lucide="{{ $movement->type->icon() }}" class="w-3 h-3"></i>
                                                            {{ $movement->type->label() }}
                                                        </span>
                                                    </div>
                                                    @if($movement->notes)
                                                        <p class="text-sm text-gray-600 mt-2">{{ $movement->notes }}</p>
                                                    @endif

                                                    {{-- Transition état / localisation --}}
                                                    @if($movement->state_before || $movement->location_before)
                                                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                                                            @if($movement->location_before)
                                                                <span class="bg-gray-100 px-2 py-0.5 rounded">{{ \App\Enums\ProductLocation::from($movement->location_before)->label() }}</span>
                                                            @endif
                                                            @if($movement->location_after && $movement->location_after !== $movement->location_before)
                                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                                <span class="bg-gray-900 text-white px-2 py-0.5 rounded">{{ \App\Enums\ProductLocation::from($movement->location_after)->label() }}</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-400">
                                                        <i data-lucide="user" class="w-3 h-3"></i>
                                                        <span>{{ $movement->user->name ?? 'Système' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right flex-shrink-0">
                                                    <time class="text-xs font-medium text-gray-700 block">
                                                        {{ $movement->created_at->format('d/m/Y') }}
                                                    </time>
                                                    <span class="text-xs text-gray-400">{{ $movement->created_at->format('H:i') }}</span>
                                                    <div class="mt-1 text-xs text-gray-400 italic">{{ $movement->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="inbox" class="w-6 h-6 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500">Aucun mouvement enregistré</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- VENTE ASSOCIÉE --}}
            @if($product->sale)
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="receipt" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Vente associée</h3>
                            <p class="text-xs text-gray-500">{{ $product->sale->sale_type->label() }}</p>
                        </div>
                        @if($product->sale->is_confirmed)
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full border border-emerald-200">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Confirmée
                            </span>
                        @else
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full border border-amber-200">
                                <i data-lucide="clock" class="w-3 h-3"></i> En attente
                            </span>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Prix de vente</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ number_format($product->sale->prix_vente, 0, ',', ' ') }} <span class="text-xs font-normal text-gray-500">FCFA</span></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payé</dt>
                                <dd class="text-lg font-bold text-emerald-600">{{ number_format($product->sale->amount_paid, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></dd>
                            </div>
                            @if($product->sale->amount_remaining > 0)
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Reste dû</dt>
                                    <dd class="text-lg font-bold text-red-600">{{ number_format($product->sale->amount_remaining, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></dd>
                                </div>
                            @endif
                            @if($product->sale->client_name)
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Client</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $product->sale->client_name }}</dd>
                                    @if($product->sale->client_phone)
                                        <dd class="text-xs text-gray-500">{{ $product->sale->client_phone }}</dd>
                                    @endif
                                </div>
                            @endif
                            @if($product->sale->reseller)
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Revendeur</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $product->sale->reseller->name }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Vendu par</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $product->sale->seller->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date de vente</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $product->sale->date_vente_effective?->format('d/m/Y') ?? 'N/A' }}</dd>
                            </div>
                        </div>

                        {{-- Paiements --}}
                        @if($product->sale->payments->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">Historique des paiements</h4>
                                <div class="space-y-2">
                                    @foreach($product->sale->payments as $payment)
                                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <i data-lucide="banknote" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                                    <p class="text-xs text-gray-500">{{ $payment->payment_method->label() }} @if($payment->reference) · {{ $payment->reference }} @endif</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-medium text-gray-700">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                                <p class="text-xs text-gray-400">{{ $payment->recorder->name ?? 'Système' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('sales.show', $product->sale) }}" class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Voir la vente complète
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- RETOUR CLIENT --}}
            @if($product->customerReturn)
                @php $cr = $product->customerReturn; @endphp
                <div class="bg-white border border-amber-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Retour client</h3>
                            <p class="text-xs text-gray-500">Depuis la vente #{{ $cr->original_sale_id }}</p>
                        </div>
                        @if($cr->status)
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full border {{ $cr->status->badgeClasses() }}">
                                {{ $cr->status->label() }}
                            </span>
                        @endif
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Motif</dt>
                            <dd class="text-sm text-gray-900">{{ $cr->reason }}</dd>
                        </div>
                        @if($cr->defect_description)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Défaut constaté</dt>
                                <dd class="text-sm text-gray-900 bg-red-50 border border-red-100 rounded-lg p-3">{{ $cr->defect_description }}</dd>
                            </div>
                        @endif
                        @if($cr->repair_notes)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notes de réparation</dt>
                                <dd class="text-sm text-gray-900 bg-blue-50 border border-blue-100 rounded-lg p-3">{{ $cr->repair_notes }}</dd>
                            </div>
                        @endif

                        {{-- Retour fournisseur associé --}}
                        @if($cr->supplierReturn)
                            @php $sr = $cr->supplierReturn; @endphp
                            <div class="mt-3 pt-3 border-t border-amber-100">
                                <div class="flex items-center gap-2 mb-2">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-orange-500"></i>
                                    <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Retour fournisseur</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $sr->statut->badgeClasses() }}">
                                        {{ $sr->statut->label() }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <dt class="text-xs text-gray-500">Envoyé le</dt>
                                        <dd class="font-medium text-gray-900">{{ $sr->date_envoi->format('d/m/Y') }}</dd>
                                    </div>
                                    @if($sr->date_retour_prevue)
                                        <div>
                                            <dt class="text-xs text-gray-500">Retour prévu</dt>
                                            <dd class="font-medium {{ $sr->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $sr->date_retour_prevue->format('d/m/Y') }}
                                                @if($sr->isOverdue()) <span class="text-xs">(en retard)</span> @endif
                                            </dd>
                                        </div>
                                    @endif
                                    @if($sr->replacementProduct)
                                        <div class="col-span-2">
                                            <dt class="text-xs text-gray-500">Produit de remplacement</dt>
                                            <dd>
                                                <a href="{{ route('products.show', $sr->replacementProduct) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                                    {{ $sr->replacementProduct->productModel->name }}
                                                    @if($sr->replacementProduct->imei) · IMEI: {{ $sr->replacementProduct->imei }} @endif
                                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                                </a>
                                            </dd>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-gray-400">
                                Traité par {{ $cr->processor->name ?? 'N/A' }} · {{ $cr->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TROC --}}
            @if($product->tradeIn)
                @php $ti = $product->tradeIn; @endphp
                <div class="bg-white border border-purple-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-purple-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="repeat" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Troc associé</h3>
                            <p class="text-xs text-gray-500">Téléphone reçu en échange</p>
                        </div>
                        @if($ti->needs_repair)
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full border border-amber-200">
                                <i data-lucide="wrench" class="w-3 h-3"></i>
                                {{ $ti->repair_status ?? 'À réparer' }}
                            </span>
                        @endif
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Modèle reçu</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $ti->modele_recu }}</dd>
                        </div>
                        @if($ti->imei_recu)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">IMEI reçu</dt>
                                <dd class="text-sm font-mono text-gray-900">{{ $ti->imei_recu }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Valeur de reprise</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ number_format($ti->valeur_reprise, 0, ',', ' ') }} FCFA</dd>
                        </div>
                        @if($ti->complement_especes > 0)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Complément espèces</dt>
                                <dd class="text-sm font-bold text-emerald-600">{{ number_format($ti->complement_especes, 0, ',', ' ') }} FCFA</dd>
                            </div>
                        @endif
                        @if($ti->etat_recu)
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">État reçu</dt>
                                <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-2.5 border border-gray-100">{{ $ti->etat_recu }}</dd>
                            </div>
                        @endif
                        @if($ti->repair_notes)
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notes de réparation</dt>
                                <dd class="text-sm text-gray-900 bg-amber-50 rounded-lg p-2.5 border border-amber-100">{{ $ti->repair_notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- ============================================================ --}}
        {{-- SIDEBAR --}}
        {{-- ============================================================ --}}
        <div class="space-y-5">

            {{-- INFORMATIONS DU PRODUIT --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="info" class="w-4 h-4 text-white"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Informations</h3>
                </div>
                <div class="p-5 space-y-4">
                    {{-- IMEI --}}
                    @if($product->imei)
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                                <i data-lucide="hash" class="w-3 h-3"></i> IMEI
                            </dt>
                            <dd class="relative group">
                                <div class="text-sm font-mono font-semibold bg-gray-50 px-3 py-2.5 rounded-lg border border-gray-200 tracking-wider break-all">
                                    {{ $product->imei }}
                                </div>
                                <button onclick="navigator.clipboard.writeText('{{ $product->imei }}'); this.textContent = 'Copié !'; setTimeout(() => this.textContent = 'Copier', 2000);"
                                    class="absolute top-1.5 right-1.5 px-2 py-1 bg-white border border-gray-200 rounded text-xs text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Copier
                                </button>
                            </dd>
                        </div>
                    @endif

                    @if($product->serial_number)
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1.5">N° Série</dt>
                            <dd class="text-sm font-mono text-gray-900 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200 break-all">{{ $product->serial_number }}</dd>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <dt class="text-xs text-gray-500 mb-1">Condition</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->condition ?: 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 mb-1">Date d'entrée</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $product->date_achat?->format('d/m/Y') ?: 'N/A' }}</dd>
                        </div>
                        @if(auth()->user()->hasRole('admin'))
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Fournisseur</dt>
                                <dd class="text-sm text-gray-900">{{ $product->fournisseur ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Jours en stock</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $stats['days_in_stock_human'] ?? 'N/A' }}</dd>
                            </div>
                        @endif
                    </div>

                    @if($product->defauts)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1.5">Défauts</dt>
                            <dd class="text-sm text-gray-900 bg-red-50 border border-red-100 rounded-lg p-3">{{ $product->defauts }}</dd>
                        </div>
                    @endif
                    @if($product->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1.5">Notes</dt>
                            <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3 border border-gray-100">{{ $product->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- PRIX (du modèle) --}}
            @if(auth()->user()->hasRole('admin'))
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="coins" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Prix & Marges</h3>
                            <p class="text-xs text-gray-500">Hérités du modèle</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-xs text-gray-500 mb-1">Prix de revient</dt>
                            <dd class="text-xl font-bold text-gray-900">{{ number_format($product->prix_achat, 0, ',', ' ') }} <span class="text-xs font-normal text-gray-500">FCFA</span></dd>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                            <dt class="text-xs text-blue-700 mb-1">Prix vente client</dt>
                            <dd class="text-xl font-bold text-blue-900">{{ number_format($product->prix_vente, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></dd>
                        </div>
                        <div class="bg-purple-50 border border-purple-100 rounded-lg p-3">
                            <dt class="text-xs text-purple-700 mb-1">Prix vente revendeur</dt>
                            <dd class="text-xl font-bold text-purple-900">{{ number_format($product->prix_vente_revendeur, 0, ',', ' ') }} <span class="text-xs font-normal">FCFA</span></dd>
                        </div>
                        <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                            <div>
                                <dt class="text-xs text-emerald-700">Bénéfice potentiel</dt>
                                <dd class="text-lg font-bold text-emerald-700">+{{ number_format($product->benefice_potentiel, 0, ',', ' ') }} FCFA</dd>
                            </div>
                            <div class="text-right">
                                <dt class="text-xs text-gray-500">Marge</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ $product->marge_percentage }}%</dd>
                            </div>
                        </div>
                        <div class="pt-2 text-center">
                            <a href="{{ route('product-models.edit', $product->productModel) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                Modifier les prix du modèle
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="tag" class="w-4 h-4 text-gray-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">Prix de vente</h3>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($product->prix_vente, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">FCFA</span></div>
                </div>
            @endif

            {{-- TRACABILITÉ --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Traçabilité</h3>
                </div>
                <div class="p-5 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Enregistré par</span>
                        <span class="font-medium text-gray-900">{{ $product->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Date création</span>
                        <span class="font-medium text-gray-900">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($product->updater && $product->updater->id !== $product->creator?->id)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Modifié par</span>
                            <span class="font-medium text-gray-900">{{ $product->updater->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Dernière modif.</span>
                            <span class="font-medium text-gray-900">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-gray-500">Mouvements</span>
                        <span class="font-bold text-gray-900">{{ $stats['total_movements'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ZONE DE DANGER --}}
            @can('delete', $product)
                <div class="bg-white border border-red-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-red-100 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                        <h3 class="text-sm font-semibold text-red-900">Zone de danger</h3>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-gray-600 mb-4">La suppression est irréversible. Toutes les données associées seront perdues.</p>
                        <button
                            type="button"
                            @click="$dispatch('open-modal-delete-product')"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700 transition-colors"
                        >
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            Supprimer ce produit
                        </button>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    {{-- Modal de confirmation suppression --}}
    @can('delete', $product)
        <x-confirm-modal
            id="delete-product"
            title="Supprimer ce produit ?"
            message="Cette action est irréversible. L'historique des mouvements sera également supprimé."
            confirmText="Oui, supprimer"
            :danger="true"
        >
            <x-slot name="form">
                <form method="POST" action="{{ route('products.destroy', $product) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        Oui, supprimer définitivement
                    </button>
                </form>
            </x-slot>
        </x-confirm-modal>
    @endcan

</x-app-layout>