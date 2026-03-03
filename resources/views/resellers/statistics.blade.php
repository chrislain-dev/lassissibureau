<x-app-layout>
    <x-slot name="header">
        Statistiques - {{ $reseller->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('resellers.show', $reseller) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Filtres de période --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('resellers.statistics', $reseller) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Date de début
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}" class="block w-full py-2 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Date de fin
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}" class="block w-full py-2 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-900 border border-gray-900 rounded-md font-medium text-sm text-white hover:bg-gray-800 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filtrer
                    </button>
                </div>
            </form>
        </div>

        {{-- Statistiques principales --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nombre de ventes</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['sales']->count() }}</p>
                <p class="text-xs text-gray-500 mt-2">
                    Sur la période sélectionnée
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['sales_amount'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500 mt-1">FCFA</p>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bénéfice</p>
                <p class="text-2xl font-bold text-emerald-600">+{{ number_format($stats['benefice'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500 mt-1">FCFA</p>
            </div>
            @endif
        </div>

        {{-- Détail des ventes --}}
        @if($stats['sales']->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Détail des ventes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produit
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Prix de vente
                                </th>
                                @if(auth()->user()->isAdmin())
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bénéfice
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stats['sales'] as $sale)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->date_vente_effective->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $sale->date_vente_effective->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $sale->product->productModel->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $sale->product->productModel->brand }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sale->sale_type->value === 'achat_direct' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $sale->sale_type->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-semibold text-gray-900">{{ number_format($sale->prix_vente, 0, ',', ' ') }} FCFA</div>
                                    </td>
                                    @if(auth()->user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-semibold text-green-600">+{{ number_format($sale->benefice, 0, ',', ' ') }} FCFA</div>
                                    </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($sale->is_confirmed)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Confirmée
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                En attente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900 uppercase">
                                    Total
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($stats['sales_amount'], 0, ',', ' ') }} FCFA</div>
                                </td>
                                @if(auth()->user()->isAdmin())
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-bold text-green-600">+{{ number_format($stats['benefice'], 0, ',', ' ') }} FCFA</div>
                                </td>
                                @endif
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <i data-lucide="bar-chart-x" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-sm text-gray-500">Aucune vente sur cette période</p>
            </div>
        @endif
    </div>
</x-app-layout>
