<x-app-layout>
    <x-slot name="header">
        {{ $reseller->name }}
    </x-slot>

    <x-slot name="actions">
        <div class="flex items-center gap-2">
            <a href="{{ route('resellers.statistics', $reseller) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white hover:bg-gray-50">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Statistiques
            </a>

            <a href="{{ route('resellers.edit', $reseller) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg bg-gray-900 text-white hover:bg-gray-800">
                <i data-lucide="edit" class="w-4 h-4"></i>
                Modifier
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-8 bg-gray-50 p-6 rounded-xl">

        {{-- HEADER --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ $reseller->name }}
                    </h2>

                    <div class="flex items-center gap-2">
                        @if($reseller->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                Actif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                Inactif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="text-sm text-gray-600 space-y-1 text-right">
                    <div class="flex items-center gap-1 justify-end">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                        {{ $reseller->phone }}
                    </div>
                    @if($reseller->address)
                        <div class="flex items-center gap-1 justify-end">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            {{ $reseller->address }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- STATISTIQUES --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Ventes</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['nombre_ventes'] }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Chiffre d'affaires</p>
                <p class="text-xl font-semibold text-gray-900">
                    {{ number_format($stats['total_sales'], 0, ',', ' ') }} FCFA
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Produits en cours</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $stats['produits_en_cours'] }}
                </p>
            </div>

            @if(auth()->user()->isAdmin() && isset($stats['total_benefice']))
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Bénéfice</p>
                    <p class="text-xl font-semibold text-gray-900">
                        {{ number_format($stats['total_benefice'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
            @endif
        </div>

        {{-- NOTES --}}
        @if($reseller->notes)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Notes</p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    {{ $reseller->notes }}
                </p>
            </div>
        @endif

        {{-- HISTORIQUE DES PAIEMENTS --}}
        @if($payments->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide">
                        Historique des 20 derniers paiements
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moyen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->payment_date->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $payment->sale->product->productModel->name ?? 'Produit inconnu' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($payment->payment_method) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $payment->notes }}">
                                        {{ $payment->notes ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- VENTES RÉCENTES --}}
        @if($reseller->sales->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wide">
                        Ventes récentes
                    </h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($reseller->sales->take(10) as $sale)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $sale->product->productModel->name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $sale->date_vente_effective->format('d/m/Y') }}
                                </p>
                            </div>

                            <div class="text-right space-y-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ number_format($sale->prix_vente, 0, ',', ' ') }} FCFA
                                </p>

                                @if($sale->is_confirmed)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-md bg-green-100 text-green-700">
                                        Confirmée
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-md bg-gray-100 text-gray-600">
                                        En attente
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
