@props(['product', 'showActions' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden']) }}>
    <div class="p-4">
        {{-- En-tête --}}
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 text-lg">
                    {{ $product->productModel->name }}
                </h3>
                <p class="text-sm text-gray-500">{{ $product->productModel->brand }}</p>
            </div>
            @if($product->condition)
                <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                    {{ $product->condition }}
                </span>
            @endif
        </div>

        {{-- Badges État et Localisation --}}
        <div class="flex flex-wrap gap-2 mb-3">
            <x-products.state-badge :state="$product->state" />
            <x-products.location-badge :location="$product->location" />
        </div>

        {{-- Informations --}}
        <div class="space-y-2 text-sm">
            @if($product->imei)
                <div class="flex items-center text-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-mono">{{ $product->imei }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between pt-2 border-t">
                <span class="text-gray-600">Prix de vente</span>
                <span class="text-lg font-bold text-gray-900">
                    {{ number_format($product->prix_vente, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div class="mt-4 flex flex-col gap-2">
                @if(auth()->user()->isAdmin() && $product->benefice_potentiel > 0)
                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-green-50 text-green-700 text-xs font-semibold w-fit">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        Bénéfice espéré:
                        <span class="font-bold">
                            +{{ number_format($product->benefice_potentiel, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        @if($showActions)
            <div class="mt-4 pt-4 border-t flex gap-2">
                <a href="{{ route('products.show', $product) }}"
                   class="flex-1 text-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition-colors">
                    Détails
                </a>

                @can('sell', $product)
                    @if($product->isAvailable())
                        <a href="{{ route('products.quick-sell', $product) }}"
                           class="flex-1 text-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                            Vendre
                        </a>
                    @endif
                @endcan
            </div>
        @endif
    </div>
</div>
