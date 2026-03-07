<?php
$file = '/home/monsieur_okana/projects/lassissi/lassissiitech/resources/views/livewire/products-table.blade.php';
$content = file_get_contents($file);

$desktopStart = '<table class="min-w-full divide-y divide-gray-200">';
$desktopEnd = '</table>';
$desktopSectionStartPos = strpos($content, $desktopStart);
$desktopSectionEndPos = strpos($content, $desktopEnd, $desktopSectionStartPos) + strlen($desktopEnd);

$desktopNew = <<<'HTML'
<table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Modèle</th>
                                @if($selectedCategory !== 'accessoire')
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">IMEI / Série</th>
                                @else
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source / Condition</th>
                                @if($selectedCategory !== 'accessoire')
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">État & Loc.</th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($products as $item)
                                @if($selectedCategory === 'accessoire')
                                    <tr class="hover:bg-gray-50 transition-colors" wire:key="model-{{ $item->id }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i data-lucide="box" class="w-5 h-5 text-gray-600"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $item->brand }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->quantity }} dispo</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $item->quantity_sold }} vendus</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->condition_type)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 w-fit">
                                                    {{ ucfirst($item->condition_type->value ?? $item->condition_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-sm font-semibold text-gray-900">{{ number_format($item->prix_vente_default, 0, ',', ' ') }}</div>
                                            <div class="text-xs text-gray-500">FCFA</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('product-models.show', $item) }}" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    Voir
                                                </a>
                                                @can('update', clone $item)
                                                    <a href="{{ route('product-models.edit', $item) }}" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                        Modifier
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    @php $product = $item; @endphp
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
                                                @can('update', clone $product)
                                                    <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                        Modifier
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
HTML;

$content = substr_replace($content, $desktopNew, $desktopSectionStartPos, $desktopSectionEndPos - $desktopSectionStartPos);

$mobileStart = '<div class="lg:hidden space-y-3">';
$mobileEnd = '@if($products->hasPages())';
$mobileSectionStartPos = strpos($content, $mobileStart);
$mobileSectionEndPos = strpos($content, $mobileEnd, $mobileSectionStartPos);

$mobileNew = <<<'HTML'
<div class="lg:hidden space-y-3">
                @foreach($products as $item)
                    @if($selectedCategory === 'accessoire')
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all" wire:key="model-mobile-{{ $item->id }}">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="box" class="w-5 h-5 text-gray-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $item->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->brand }}</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($item->prix_vente_default, 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-500">FCFA</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                                <div>
                                    <span class="text-gray-500">Stock:</span>
                                    <span class="text-gray-900 block mt-0.5 font-medium">{{ $item->quantity }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Vendus:</span>
                                    <div class="text-gray-900 mt-0.5">{{ $item->quantity_sold }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                                <a href="{{ route('product-models.show', $item) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Voir
                                </a>
                                @can('update', clone $item)
                                    <a href="{{ route('product-models.edit', $item) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Modifier
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @else
                        @php $product = $item; @endphp
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all" wire:key="product-mobile-{{ $product->id }}">
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

                            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                                <a href="{{ route('products.show', $product) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Voir
                                </a>
                                @can('update', clone $product)
                                    <a href="{{ route('products.edit', $product) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 rounded-md transition-colors">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        Modifier
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endif
                @endforeach

                
HTML;

$content = substr_replace($content, $mobileNew, $mobileSectionStartPos, $mobileSectionEndPos - $mobileSectionStartPos);

file_put_contents($file, $content);
echo "Blade updated!\n";
