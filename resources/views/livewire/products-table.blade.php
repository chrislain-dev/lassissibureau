<div>
    {{-- ÉTAPE 1 : Sélection de la catégorie --}}
    @if($step === 1)
        <div class="max-w-7xl mx-auto py-6 sm:py-12">
            <div class="text-center mb-8 sm:mb-12 px-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2 sm:mb-3">Choisir une catégorie</h2>
                <p class="text-sm sm:text-base text-gray-600">Sélectionnez une catégorie pour afficher les produits</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 px-4">
                @forelse($categories as $category)
                    <button
                        wire:click="selectCategory('{{ $category['value'] }}')"
                        class="group bg-white border-2 border-gray-200 hover:border-black rounded-lg sm:rounded-xl p-4 sm:p-8 transition-all duration-200 hover:shadow-lg"
                    >
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 group-hover:bg-black rounded-lg sm:rounded-xl flex items-center justify-center mb-3 sm:mb-4 transition-colors">
                                <i data-lucide="{{ $category['icon'] }}" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-600 group-hover:text-white transition-colors"></i>
                            </div>
                            <h3 class="text-sm sm:text-lg font-semibold text-gray-900 mb-2">{{ $category['label'] }}</h3>
                            <div class="flex flex-col sm:items-center gap-1 sm:gap-3 text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="layers" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                    {{ $category['models_count'] }} <span class="hidden sm:inline">modèles</span>
                                </span>
                                <span class="flex items-center justify-center gap-1">
                                    <i data-lucide="package" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                    {{ $category['products_count'] }} <span class="hidden sm:inline">produits</span>
                                </span>
                            </div>

                            <div class="w-full border-t border-gray-100 pt-3 mt-auto">
                                <div class="grid grid-cols-1 gap-1.5 text-xs text-left">
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span>Revenu espéré:</span>
                                        <span class="font-bold text-gray-900">{{ number_format($category['revenu'], 0, ',', ' ') }} F</span>
                                    </div>
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span>Investissement:</span>
                                        <span class="font-medium text-red-600">{{ number_format($category['investissement'], 0, ',', ' ') }} F</span>
                                    </div>
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span>Bénéfice espéré:</span>
                                        <span class="font-bold text-green-600">{{ number_format($category['benefice'], 0, ',', ' ') }} F</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="inbox" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400"></i>
                        </div>
                        <p class="text-sm sm:text-base text-gray-500">Aucune catégorie disponible</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ÉTAPE 2 : Liste des produits --}}
    @if($step === 2)
        {{-- Bouton retour --}}
        <div class="mb-4 sm:mb-6">
            <button
                wire:click="backToCategories"
                class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                <span class="hidden sm:inline">Retour aux catégories</span>
                <span class="sm:hidden">Retour</span>
            </button>
        </div>

        {{-- Statistiques --}}
        <div class="mb-4 sm:mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Stock</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="package" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Dispos</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['available'] }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1"><span class="hidden sm:inline">Chez </span>Revend.</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['chez_revendeur'] }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Réparer</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['a_reparer'] }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="wrench" class="w-4 h-4 sm:w-5 sm:h-5 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white border border-dashed border-gray-300 rounded-lg mb-4 sm:mb-6 p-4 sm:p-6">
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide mb-3 sm:mb-4">Filtres</h3>

            <div class="space-y-3 sm:space-y-4">
                {{-- Recherche --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher..."
                        class="block w-full pl-10 pr-4 py-2 text-sm rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                    >
                </div>

                {{-- Grille de filtres --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">État</label>
                        <select wire:model.live="state" class="block w-full py-2 text-sm rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Tous</option>
                            @foreach($states as $stateOption)
                                <option value="{{ $stateOption['value'] }}">{{ $stateOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Localisation</label>
                        <select wire:model.live="location" class="block w-full py-2 text-sm rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Toutes</option>
                            @foreach($locations as $locationOption)
                                <option value="{{ $locationOption['value'] }}">{{ $locationOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Modèle</label>
                        <select wire:model.live="product_model_id" class="block w-full py-2 text-sm rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Tous</option>
                            @foreach($productModels as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Condition</label>
                        <select wire:model.live="condition" class="block w-full py-2 text-sm rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Toutes</option>
                            @foreach($conditions as $conditionOption)
                                <option value="{{ $conditionOption }}">{{ $conditionOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-0 pt-2">
                    <button wire:click="resetFilters" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 rounded-md font-medium text-sm text-gray-700 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Réinitialiser
                    </button>
                    <a href="{{ route('products.export', ['search' => $search, 'state' => $state, 'location' => $location, 'product_model_id' => $product_model_id, 'condition' => $condition]) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-black hover:bg-gray-800 border border-black rounded-md font-medium text-sm text-white transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Exporter CSV</span>
                        <span class="sm:hidden">Export</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Tableau Desktop / Cards Mobile --}}
        @if($products->isEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-8 sm:p-12">
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">Aucun produit trouvé</h3>
                    <p class="text-xs text-gray-500 mb-4">Aucun produit ne correspond à vos critères</p>
                    @can('create', App\Models\Product::class)
                        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-black hover:bg-gray-800 text-white text-sm font-medium rounded-md transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Créer un produit
                        </a>
                    @endcan
                </div>
            </div>
        @else
            {{-- Vue Desktop (Tableau) --}}
            <div class="hidden lg:block bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Modèle</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">IMEI / Série</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">État & Loc.</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50 transition-colors" wire:key="product-{{ $product->id }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                @php
                                                    $categoryIcons = ['telephone' => 'smartphone', 'tablette' => 'tablet', 'pc' => 'monitor', 'accessoire' => 'box'];
                                                    $icon = $categoryIcons[$product->productModel->category->value] ?? 'box';
                                                @endphp
                                                <i data-lucide="{{ $icon }}" class="w-5 h-5 text-gray-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $product->productModel->name }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    {{ $product->productModel->brand }}
                                                    @if($product->condition)
                                                        • {{ $product->condition }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if($product->imei)
                                            <code class="text-xs font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">{{ $product->imei }}</code>
                                        @else
                                            <span class="text-xs text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            @if($product->condition === 'troc')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 w-fit">
                                                    <i data-lucide="refresh-cw" class="w-3 h-3 mr-1"></i>
                                                    Troc
                                                </span>
                                            @elseif($product->condition === 'neuf')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 w-fit">
                                                    Neuf
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 w-fit">
                                                    {{ ucfirst($product->condition ?: 'Standard') }}
                                                </span>
                                            @endif

                                            @if($product->fournisseur)
                                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <i data-lucide="truck" class="w-3 h-3"></i>
                                                    {{ $product->fournisseur }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 items-center">
                                            <x-products.state-badge :state="$product->state" />
                                            <x-products.location-badge :location="$product->location" />
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-semibold text-gray-900">{{ number_format($product->prix_vente, 0, ',', ' ') }}</div>
                                        <div class="text-xs text-gray-500">FCFA</div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                Voir
                                            </a>
                                            @can('update', $product)
                                                <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                    Modifier
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

            {{-- Vue Mobile/Tablet (Cards) --}}
            <div class="lg:hidden space-y-3">
                @foreach($products as $product)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all" wire:key="product-mobile-{{ $product->id }}">
                        {{-- Header --}}
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                @php
                                    $categoryIcons = ['telephone' => 'smartphone', 'tablette' => 'tablet', 'pc' => 'monitor', 'accessoire' => 'box'];
                                    $icon = $categoryIcons[$product->productModel->category->value] ?? 'box';
                                @endphp
                                <i data-lucide="{{ $icon }}" class="w-5 h-5 text-gray-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $product->productModel->name }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $product->productModel->brand }}</p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <div class="text-sm font-bold text-gray-900">{{ number_format($product->prix_vente, 0, ',', ' ') }}</div>
                                <div class="text-xs text-gray-500">FCFA</div>
                            </div>
                        </div>

                        {{-- Info Grid --}}
                        <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                            <div>
                                <span class="text-gray-500">IMEI:</span>
                                @if($product->imei)
                                    <code class="text-gray-900 bg-gray-100 px-1.5 py-0.5 rounded block mt-0.5 font-mono truncate">{{ $product->imei }}</code>
                                @else
                                    <span class="text-gray-400 block mt-0.5">N/A</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-500">Condition:</span>
                                <div class="mt-0.5">
                                    @if($product->condition === 'troc')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            Troc
                                        </span>
                                    @elseif($product->condition === 'neuf')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Neuf
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($product->condition ?: 'Standard') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <x-products.state-badge :state="$product->state" />
                            <x-products.location-badge :location="$product->location" />
                            @if($product->fournisseur)
                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded">
                                    <i data-lucide="truck" class="w-3 h-3"></i>
                                    {{ $product->fournisseur }}
                                </span>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                            <a href="{{ route('products.show', $product) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                Voir
                            </a>
                            @can('update', $product)
                                <a href="{{ route('products.edit', $product) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    Modifier
                                </a>
                            @endcan
                        </div>
                    </div>
                @endforeach

                @if($products->hasPages())
                    <div class="px-4 py-3 bg-white border border-gray-200 rounded-lg">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>