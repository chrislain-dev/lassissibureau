<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-gray-900 to-gray-700 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                <i data-lucide="box" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 tracking-tight truncate">{{ $productModel->name }}</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 truncate">{{ $productModel->brand }}</p>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        @can('update', $productModel)
            <a href="{{ route('product-models.edit', $productModel) }}"
               class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 bg-gray-900 text-white rounded-lg font-medium text-xs sm:text-sm hover:bg-gray-800 active:bg-gray-950 transition-all hover:shadow-lg hover:scale-105 w-full sm:w-auto">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                <span class="hidden xs:inline">Modifier</span>
            </a>
        @endcan
        @if($productModel->category->value === 'accessoire' && $productModel->quantity > 0)
            <a href="{{ route('sales.create', ['productModelId' => $productModel->id]) }}"
               class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium text-xs sm:text-sm hover:bg-emerald-700 active:bg-emerald-800 transition-all hover:shadow-lg hover:scale-105 w-full sm:w-auto">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span class="hidden xs:inline">Vendre</span>
            </a>
        @endif
    </x-slot>

    <x-alerts.success :message="session('success')" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">

            {{-- Informations générales --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900">Informations générales</h3>
                </div>

                <div class="p-4 sm:p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-1">
                            <dt class="text-xs font-medium text-gray-500">Nom du modèle</dt>
                            <dd class="text-sm font-medium text-gray-900 break-words">{{ $productModel->name }}</dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium text-gray-500">Marque</dt>
                            <dd class="text-sm font-medium text-gray-900 break-words">{{ $productModel->brand }}</dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium text-gray-500">Catégorie</dt>
                            <dd>
                                @php
                                    $categoryLabels = [
                                        'telephone' => ['icon' => '📱', 'label' => 'Téléphone'],
                                        'tablette' => ['icon' => '💻', 'label' => 'Tablette'],
                                        'pc' => ['icon' => '🖥️', 'label' => 'Ordinateur'],
                                        'accessoire' => ['icon' => '🎧', 'label' => 'Accessoire'],
                                    ];
                                    $category = $categoryLabels[$productModel->category->value] ?? ['icon' => '📦', 'label' => ucfirst($productModel->category->value)];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200 hover:border-gray-300 transition-colors">
                                    <span>{{ $category['icon'] }}</span>
                                    {{ $category['label'] }}
                                </span>
                            </dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium text-gray-500">Statut</dt>
                            <dd>
                                @if($productModel->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        <span class="relative flex h-2 w-2 flex-shrink-0">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                        </span>
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-500 border border-gray-200">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></span>
                                        Inactif
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500">Stock minimum</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $productModel->stock_minimum }} unités</dd>
                        </div>
                    </dl>

                    @if($productModel->description)
                        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-100">
                            <dt class="text-xs font-medium text-gray-500 mb-2">Description</dt>
                            <dd class="text-sm text-gray-600 leading-relaxed break-words">{{ $productModel->description }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Prix avec design moderne --}}
            <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 sm:p-6">
                    <h3 class="text-sm sm:text-base font-semibold text-white mb-4 sm:mb-6">Tarification par défaut</h3>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
                            <dt class="text-xs font-medium text-gray-300 mb-1 sm:mb-2">Prix d'achat</dt>
                            <dd class="text-lg sm:text-2xl font-bold text-white break-all">{{ number_format($productModel->prix_revient_default, 0, ',', ' ') }}</dd>
                            <dd class="text-xs text-gray-400 mt-1">FCFA</dd>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
                            <dt class="text-xs font-medium text-gray-300 mb-1 sm:mb-2">Prix client</dt>
                            <dd class="text-lg sm:text-2xl font-bold text-white break-all">{{ number_format($productModel->prix_vente_default, 0, ',', ' ') }}</dd>
                            <dd class="text-xs text-gray-400 mt-1">FCFA</dd>
                        </div>

                        <div class="bg-blue-500/20 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-blue-400/30">
                            <dt class="text-xs font-medium text-blue-300 mb-1 sm:mb-2">Prix revendeur</dt>
                            <dd class="text-lg sm:text-2xl font-bold text-blue-100 break-all">
                                {{ $productModel->prix_vente_revendeur ? number_format($productModel->prix_vente_revendeur, 0, ',', ' ') : '—' }}
                            </dd>
                            <dd class="text-xs text-blue-300 mt-1">
                                @if($productModel->prix_vente_revendeur && $productModel->prix_vente_default > 0)
                                    -{{ number_format((1 - $productModel->prix_vente_revendeur / $productModel->prix_vente_default) * 100, 0) }}% vs client
                                @else
                                    FCFA
                                @endif
                            </dd>
                        </div>

                        <div class="bg-green-500/20 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-green-400/30">
                            @php
                                $benefice = $productModel->prix_vente_default - $productModel->prix_revient_default;
                                $marge = $productModel->prix_revient_default > 0 ? ($benefice / $productModel->prix_revient_default) * 100 : 0;
                            @endphp
                            <dt class="text-xs font-medium text-green-300 mb-1 sm:mb-2">Bénéfice client</dt>
                            <dd class="text-lg sm:text-2xl font-bold text-green-100 break-all">{{ number_format($benefice, 0, ',', ' ') }}</dd>
                            <dd class="text-xs text-green-300 mt-1">+{{ number_format($marge, 1) }}% ROI</dd>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Bloc accessoire : stock restant + total vendu --}}
            @if($productModel->isAccessoire())
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Inventaire</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Stock et ventes en temps réel</p>
                        </div>
                        <i data-lucide="box" class="w-5 h-5 text-gray-400 flex-shrink-0"></i>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100 text-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="package" class="w-5 h-5 text-white"></i>
                                </div>
                                <p class="text-3xl font-black text-blue-900">{{ $stats['total_stock'] }}</p>
                                <p class="text-xs font-medium text-blue-700 mt-1 uppercase tracking-wide">Stock restant</p>
                                <p class="text-xs text-blue-500 mt-0.5">unités disponibles</p>
                            </div>

                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-4 border border-emerald-100 text-center">
                                <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="trending-up" class="w-5 h-5 text-white"></i>
                                </div>
                                <p class="text-3xl font-black text-emerald-900">{{ $stats['total_sold'] }}</p>
                                <p class="text-xs font-medium text-emerald-700 mt-1 uppercase tracking-wide">Déjà vendus</p>
                                <p class="text-xs text-emerald-500 mt-0.5">unités écoulées</p>
                            </div>
                        </div>

                        @php
                            $totalOriginal = $stats['total_stock'] + $stats['total_sold'];
                            $percentSold = $totalOriginal > 0 ? round(($stats['total_sold'] / $totalOriginal) * 100) : 0;
                        @endphp
                        @if($totalOriginal > 0)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs text-gray-500">Progression des ventes</span>
                                    <span class="text-xs font-semibold text-gray-700">{{ $percentSold }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentSold }}%"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $stats['total_sold'] }} vendu(s) sur {{ $totalOriginal }} unités au total</p>
                            </div>
                        @endif

                        @if($stats['total_stock'] <= $productModel->stock_minimum)
                            <div class="mt-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
                                <p class="text-xs text-red-700 font-medium">Stock sous le seuil minimum ({{ $productModel->stock_minimum }} unités)</p>
                            </div>
                        @endif
                    </div>
                </div>

            @else
            {{-- Liste des produits individuels (téléphones, tablettes, PC) --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Produits en stock</h3>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                {{ $productModel->products->count() }} {{ $productModel->products->count() > 1 ? 'produits' : 'produit' }} au total
                            </p>
                        </div>
                        @if($productModel->products->count() > 0)
                            <a href="{{ route('products.index', ['product_model_id' => $productModel->id]) }}"
                               class="text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors flex-shrink-0">
                                Voir tout →
                            </a>
                        @endif
                    </div>
                </div>

                @if($productModel->products->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 sm:py-16 px-6">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3 sm:mb-4">
                            <i data-lucide="package" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900 mb-1">Aucun produit</p>
                        <p class="text-xs sm:text-sm text-gray-500 text-center">
                            Commencez par ajouter des produits à ce modèle
                        </p>
                    </div>
                @else
                    @php
                        $groupedProducts = $productModel->products
                            ->sortByDesc('created_at')
                            ->take(50)
                            ->groupBy(function($product) {
                                return $product->created_at->format('Y-m-d H:i');
                            });

                        $displayedGroups = 0;
                        $maxGroups = 10;
                    @endphp

                    <div class="divide-y divide-gray-100">
                        @foreach($groupedProducts as $dateTime => $products)
                            @if($displayedGroups < $maxGroups)
                                @php
                                    $firstProduct = $products->first();
                                    $productCount = $products->count();
                                    $displayedGroups++;
                                @endphp

                                <div class="p-3 sm:p-4 hover:bg-gray-50 transition-colors">
                                    {{-- En-tête du groupe --}}
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center border border-blue-100 flex-shrink-0">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium text-gray-900 truncate">
                                                    {{ $firstProduct->created_at->locale('fr')->translatedFormat('d F Y à H:i') }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ $productCount }} {{ $productCount > 1 ? 'produits' : 'produit' }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 flex-shrink-0">
                                            {{ $productCount }}
                                        </span>
                                    </div>

                                    {{-- Liste des produits du groupe --}}
                                    <div class="ml-0 sm:ml-9 space-y-2">
                                        @foreach($products as $product)
                                            <a
                                                href="{{ route('products.show', $product) }}"
                                                class="group flex items-center gap-2 sm:gap-3 p-2.5 sm:p-3 rounded-lg hover:bg-white border border-transparent hover:border-gray-200 active:bg-gray-50 transition-all"
                                            >
                                                <div class="flex-shrink-0">
                                                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-900 transition-colors">
                                                        <i data-lucide="smartphone" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500 group-hover:text-white transition-colors"></i>
                                                    </div>
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs sm:text-sm font-medium text-gray-900 font-mono truncate mb-1">
                                                        {{ $product->imei ?: $product->serial_number ?: 'N/A' }}
                                                    </p>

                                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                        <x-products.state-badge :state="$product->state" class="text-[10px] sm:text-xs" />
                                                        <x-products.location-badge :location="$product->location" class="text-[10px] sm:text-xs" />
                                                    </div>
                                                </div>

                                                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors flex-shrink-0"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if($productModel->products->count() > 50 || $groupedProducts->count() > $maxGroups)
                        <div class="p-3 sm:p-4 border-t border-gray-100 bg-gray-50">
                            <a
                                href="{{ route('products.index', ['product_model_id' => $productModel->id]) }}"
                                class="flex items-center justify-center gap-2 text-xs sm:text-sm font-medium text-gray-700 hover:text-gray-900 active:text-black py-2 transition-colors"
                            >
                                Voir tous les produits
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @endif
                 @endif
            </div>
            @endif {{-- @else accessoire --}}

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4 sm:space-y-6">

            {{-- Statistiques --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900">Statistiques</h3>
                </div>

                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-3 sm:p-4 border border-blue-100">
                        <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                            <dt class="text-xs font-medium text-blue-700">Stock actuel</dt>
                            <i data-lucide="package" class="w-4 h-4 text-blue-400 flex-shrink-0"></i>
                        </div>
                        <dd class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $stats['total_stock'] }}</dd>
                        <p class="text-xs text-blue-600 mt-1">unités disponibles</p>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 sm:p-4 border border-green-100">
                        <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                            <dt class="text-xs font-medium text-green-700">Total vendu</dt>
                            <i data-lucide="trending-up" class="w-4 h-4 text-green-400 flex-shrink-0"></i>
                        </div>
                        <dd class="text-2xl sm:text-3xl font-bold text-green-900">{{ $stats['total_sold'] }}</dd>
                        <p class="text-xs text-green-600 mt-1">ventes réalisées</p>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-3 sm:p-4 border border-purple-100">
                        <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                            <dt class="text-xs font-medium text-purple-700">Prix moyen</dt>
                            <i data-lucide="coins" class="w-4 h-4 text-purple-400 flex-shrink-0"></i>
                        </div>
                        <dd class="text-xl sm:text-2xl font-bold text-purple-900 break-all">
                            {{ number_format($stats['average_price'] ?? 0, 0, ',', ' ') }}
                        </dd>
                        <p class="text-xs text-purple-600 mt-1">FCFA par unité</p>
                    </div>
                </div>
            </div>

            {{-- Alerte stock --}}
            @if($stats['total_stock'] < $productModel->stock_minimum)
                <div class="bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-200 rounded-xl p-4 sm:p-6 shadow-sm">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="alert-triangle" class="w-4 h-4 sm:w-5 sm:h-5 text-red-600"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-semibold text-red-900 mb-1">⚠️ Stock critique</h4>
                            <p class="text-xs sm:text-sm text-red-700 leading-relaxed break-words">
                                Seuil minimum atteint : <strong>{{ $stats['total_stock'] }}/{{ $productModel->stock_minimum }}</strong> unités
                            </p>
                            <button class="mt-2 sm:mt-3 text-xs font-medium text-red-700 hover:text-red-900 active:text-red-950 underline underline-offset-2">
                                Réapprovisionner →
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 sm:p-6">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-green-600"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-green-900 mb-1">✓ Stock optimal</h4>
                            <p class="text-xs sm:text-sm text-green-700">
                                Le niveau de stock est satisfaisant
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Actions dangereuses --}}
            @can('delete', $productModel)
                <div class="bg-white border-2 border-red-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="alert-octagon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-red-600"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Zone dangereuse</h3>
                        </div>
                        <p class="text-xs text-gray-600 mb-3 sm:mb-4 leading-relaxed">
                            La suppression est définitive et irréversible
                        </p>

                        <form method="POST" action="{{ route('product-models.destroy', $productModel) }}"
                              onsubmit="return confirm('⚠️ Confirmer la suppression de ce modèle ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white border-2 border-red-600 rounded-lg font-medium text-xs sm:text-sm text-red-600 hover:bg-red-600 hover:text-white active:bg-red-700 transition-all hover:shadow-md">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span class="hidden xs:inline">Supprimer définitivement</span>
                                <span class="xs:hidden">Supprimer</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>