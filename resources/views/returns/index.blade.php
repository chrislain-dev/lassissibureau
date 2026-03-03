<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-900">Retours Clients / SAV</h2>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4 sm:space-y-6">
        {{-- En-tête --}}
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Historique des retours</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Gérez ici les produits rapportés par les clients pour échange ou réparation.</p>
                </div>
                <a href="{{ route('returns.create') }}" class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 bg-gray-900 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-gray-800 active:bg-gray-950 transition-all hover:shadow-lg hover:scale-105 w-full sm:w-auto flex-shrink-0">
                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                    <span class="hidden xs:inline">Nouveau Retour</span>
                    <span class="xs:hidden">Nouveau</span>
                </a>
            </div>
        </div>

        {{-- Vue Desktop (Tableau) --}}
        <div class="hidden lg:block bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produit Retourné</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Motif</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th scope="col" class="px-4 xl:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($returns as $return)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 xl:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ $return->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 xl:px-6 py-4 whitespace-nowrap">
                                    @if($return->is_exchange)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                            <i data-lucide="repeat" class="w-3 h-3"></i>
                                            Échange
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            <i data-lucide="arrow-left-circle" class="w-3 h-3"></i>
                                            Remboursement
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 xl:px-6 py-4">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $return->returnedProduct->productModel->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono truncate">{{ $return->returnedProduct->imei }}</div>
                                    </div>
                                </td>
                                <td class="px-4 xl:px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 truncate block max-w-xs">{{ $return->originalSale->client_name }}</span>
                                </td>
                                <td class="px-4 xl:px-6 py-4">
                                    <span class="text-sm text-gray-500 line-clamp-2 max-w-xs" title="{{ $return->reason }}">
                                        {{ $return->reason }}
                                    </span>
                                </td>
                                <td class="px-4 xl:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $return->status->badgeClasses() ?? 'bg-amber-100 text-amber-800 border-amber-200' }}">
                                        {{ $return->status->label() ?? 'En attente' }}
                                    </span>
                                </td>
                                <td class="px-4 xl:px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('returns.show', $return) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span class="hidden xl:inline">Voir</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center mb-3">
                                            <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">Aucun retour client</p>
                                        <p class="text-xs text-gray-500 mt-1">Les retours apparaîtront ici</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
                <div class="px-4 xl:px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>

        {{-- Vue Mobile/Tablet (Cards) --}}
        <div class="lg:hidden space-y-3">
            @forelse($returns as $return)
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3 mb-3 pb-3 border-b border-gray-100">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-500 flex-shrink-0"></i>
                                <span class="text-sm font-semibold text-gray-900">{{ $return->created_at->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-xs text-gray-600 truncate">{{ $return->returnedProduct->productModel->name }}</p>
                        </div>
                        @if($return->is_exchange)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-800 border border-purple-200 flex-shrink-0">
                                <i data-lucide="repeat" class="w-3 h-3"></i>
                                <span class="hidden sm:inline">Échange</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-800 border border-gray-200 flex-shrink-0">
                                <i data-lucide="arrow-left-circle" class="w-3 h-3"></i>
                                <span class="hidden sm:inline">Remb.</span>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $return->status->badgeClasses() ?? 'bg-amber-100 text-amber-800 border-amber-200' }} flex-shrink-0">
                            {{ $return->status->label() ?? 'En attente' }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start gap-2">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Produit</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $return->returnedProduct->productModel->name }}</p>
                                <p class="text-xs text-gray-500 font-mono truncate">{{ $return->returnedProduct->imei }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Client</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $return->originalSale->client_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500 mb-0.5">Motif</p>
                                <p class="text-xs text-gray-700 line-clamp-2">{{ $return->reason }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <a href="{{ route('returns.show', $return) }}" class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 transition-colors w-full">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Voir les détails</span>
                    </a>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-xl p-8 text-center shadow-sm">
                    <div class="w-12 h-12 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="inbox" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900">Aucun retour client</p>
                    <p class="text-xs text-gray-500 mt-1">Les retours apparaîtront ici</p>
                    <a href="{{ route('returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-xs font-medium rounded-lg hover:bg-gray-800 active:bg-gray-950 transition-colors mt-4">
                        <i data-lucide="undo-2" class="w-4 h-4"></i>
                        Nouveau Retour
                    </a>
                </div>
            @endforelse

            @if($returns->hasPages())
                <div class="px-3 py-2.5 bg-white border border-gray-200 rounded-lg">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>