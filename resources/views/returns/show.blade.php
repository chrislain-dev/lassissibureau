<x-app-layout>
    <x-slot name="header">
        Détails du Retour #{{ $customerReturn->id }}
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Résumé --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Retour Client</h3>
                    <p class="text-sm text-gray-500">Enregistré le {{ $customerReturn->created_at->format('d/m/Y à H:i') }} par {{ $customerReturn->processor->name ?? 'N/A' }}</p>
                </div>
                <div class="flex gap-2">
                    @if($customerReturn->is_exchange)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> Échange
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i data-lucide="undo-2" class="w-4 h-4 mr-2"></i> Remboursement
                        </span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $customerReturn->status->badgeClasses() ?? 'bg-amber-100 text-amber-800 border-amber-200' }}">
                        {{ $customerReturn->status->label() ?? 'En attente' }}
                    </span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Produit Retourné</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="font-medium text-gray-900">{{ $customerReturn->returnedProduct->productModel->name }}</p>
                        <p class="text-sm text-gray-600 mt-1">IMEI: {{ $customerReturn->returnedProduct->imei ?: $customerReturn->returnedProduct->serial_number }}</p>
                        <p class="text-sm text-gray-600">Vente origine : #{{ $customerReturn->original_sale_id }}</p>
                    </div>
                </div>

                @if($customerReturn->is_exchange && $customerReturn->exchangeProduct)
                    <div>
                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Produit Donné en Échange</h4>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <p class="font-medium text-gray-900">{{ $customerReturn->exchangeProduct->productModel->name }}</p>
                            <p class="text-sm text-gray-600 mt-1">IMEI: {{ $customerReturn->exchangeProduct->imei }}</p>
                            <p class="text-sm text-gray-600">
                                Nouvelle vente : 
                                <a href="{{ route('sales.show', $customerReturn->exchange_sale_id) }}" class="text-blue-600 hover:underline">
                                    #{{ $customerReturn->exchange_sale_id }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Motif du retour</h4>
                <div class="bg-white border border-gray-200 rounded p-4 text-gray-700 italic">
                    "{{ $customerReturn->reason }}"
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('returns.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Retour à la liste
            </a>
            <a href="{{ route('sales.show', $customerReturn->original_sale_id) }}" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-gray-800">
                Voir la vente d'origine
            </a>
        </div>
    </div>
</x-app-layout>
