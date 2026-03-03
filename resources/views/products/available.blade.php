<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Produits disponibles à la vente') }}
            </h2>
            <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Tous les produits
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Stats rapides --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <x-stat-card
                    title="Disponibles"
                    :value="$products->count()"
                    icon="check-circle"
                    color="green"
                />
                <x-stat-card
                    title="Valeur totale"
                    :value="number_format($products->sum('prix_vente'), 0, ',', ' ') . ' FCFA'"
                    icon="currency-dollar"
                    color="blue"
                />
                @if(auth()->user()->isAdmin())
                <x-stat-card
                    title="Bénéfice potentiel"
                    :value="number_format($products->sum('benefice_potentiel'), 0, ',', ' ') . ' FCFA'"
                    icon="trending-up"
                    color="green"
                />
                @endif
            </div>

            @if($products->isEmpty())
                <x-empty-state
                    title="Aucun produit disponible"
                    description="Il n'y a actuellement aucun produit disponible à la vente."
                    :action="auth()->user()->can('create', App\Models\Product::class) ? route('products.create') : null"
                    actionLabel="Ajouter un produit"
                />
            @else
                {{-- Grille de produits --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <x-products.product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
