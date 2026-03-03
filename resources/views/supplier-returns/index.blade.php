<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Retours fournisseurs</h1>
                <p class="text-sm text-gray-500 mt-1">Produits envoyés pour réparation ou remplacement</p>
            </div>
            <button @click="$dispatch('open-modal-new-supplier-return')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 rounded-lg text-sm font-medium text-white hover:bg-gray-700 transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nouveau retour
            </button>
        </div>
    </x-slot>

    <x-alerts.success :message="session('success')" />
    <x-alerts.error :message="session('error')" />

    {{-- Stats rapides --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <dt class="text-xs text-gray-500">En attente</dt>
            <dd class="text-2xl font-bold text-amber-600">{{ $supplierReturns->where('statut.value', 'en_attente')->count() }}</dd>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <dt class="text-xs text-gray-500">Reçus fournisseur</dt>
            <dd class="text-2xl font-bold text-blue-600">{{ $supplierReturns->where('statut.value', 'recu')->count() }}</dd>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <dt class="text-xs text-gray-500">Remplacés</dt>
            <dd class="text-2xl font-bold text-emerald-600">{{ $supplierReturns->where('statut.value', 'remplace')->count() }}</dd>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <dt class="text-xs text-gray-500">Total</dt>
            <dd class="text-2xl font-bold text-gray-900">{{ $supplierReturns->total() }}</dd>
        </div>
    </div>

    {{-- Liste --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($supplierReturns->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Produit</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Motif</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Envoyé le</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Retour prévu</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Traité par</th>
                            <th class="relative px-5 py-3.5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($supplierReturns as $sr)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="smartphone" class="w-4 h-4 text-gray-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $sr->product->productModel->name }}</p>
                                            @if($sr->product->imei)
                                                <p class="text-xs font-mono text-gray-500">{{ $sr->product->imei }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-gray-700 max-w-xs truncate">{{ $sr->motif }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $sr->date_envoi->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($sr->date_retour_prevue)
                                        <span class="text-sm {{ $sr->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $sr->date_retour_prevue->format('d/m/Y') }}
                                            @if($sr->isOverdue())
                                                <span class="text-xs block text-red-500">En retard</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sr->statut->badgeClasses() }}">
                                        {{ $sr->statut->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500">{{ $sr->processor->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($sr->statut->value === 'en_attente')
                                            <form method="POST" action="{{ route('supplier-returns.received', $sr) }}">
                                                @csrf
                                                <button type="submit" class="text-xs px-2.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                                                    Marquer reçu
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($sr->statut->value, ['en_attente', 'recu']))
                                            <a href="{{ route('supplier-returns.show', $sr) }}"
                                               class="text-xs px-2.5 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors font-medium">
                                                Confirmer rempl.
                                            </a>
                                        @endif
                                        <a href="{{ route('supplier-returns.show', $sr) }}"
                                           class="text-xs px-2.5 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                            Voir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $supplierReturns->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="truck" class="w-7 h-7 text-gray-400"></i>
                </div>
                <p class="text-base font-medium text-gray-900">Aucun retour fournisseur</p>
                <p class="text-sm text-gray-500 mt-1">Créez le premier retour avec le bouton ci-dessus.</p>
            </div>
        @endif
    </div>
</x-app-layout>
