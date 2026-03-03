<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('supplier-returns.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Retour fournisseur #{{ $supplierReturn->id }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $supplierReturn->product->productModel->name }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border {{ $supplierReturn->statut->badgeClasses() }}">
                {{ $supplierReturn->statut->label() }}
            </span>
        </div>
    </x-slot>

    <x-alerts.success :message="session('success')" />
    <x-alerts.error :message="session('error')" />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- COLONNE PRINCIPALE --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Détails du retour --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="truck" class="w-4 h-4 text-white"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Informations du retour</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date d'envoi</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ $supplierReturn->date_envoi->format('d/m/Y') }}</dd>
                        </div>
                        @if($supplierReturn->date_retour_prevue)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Retour prévu</dt>
                                <dd class="text-sm font-semibold {{ $supplierReturn->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $supplierReturn->date_retour_prevue->format('d/m/Y') }}
                                    @if($supplierReturn->isOverdue())
                                        <span class="text-xs text-red-500 block">⚠ En retard de {{ $supplierReturn->date_retour_prevue->diffInDays() }} jours</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if($supplierReturn->date_retour_effective)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Retour effectif</dt>
                                <dd class="text-sm font-semibold text-emerald-600">{{ $supplierReturn->date_retour_effective->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Motif du retour</dt>
                            <dd class="text-sm text-gray-900 bg-amber-50 border border-amber-100 rounded-lg p-3">{{ $supplierReturn->motif }}</dd>
                        </div>
                        @if($supplierReturn->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notes</dt>
                                <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3 border border-gray-100">{{ $supplierReturn->notes }}</dd>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                        <span>Traité par {{ $supplierReturn->processor->name ?? 'N/A' }}</span>
                        <span>{{ $supplierReturn->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Produit envoyé --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="package" class="w-4 h-4 text-white"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Produit envoyé au fournisseur</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="smartphone" class="w-6 h-6 text-gray-500"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-base font-bold text-gray-900">{{ $supplierReturn->product->productModel->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $supplierReturn->product->productModel->brand }}</p>
                            @if($supplierReturn->product->imei)
                                <p class="text-xs font-mono text-gray-400 mt-1">IMEI: {{ $supplierReturn->product->imei }}</p>
                            @endif
                        </div>
                        <a href="{{ route('products.show', $supplierReturn->product) }}"
                           class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1 font-medium">
                            Voir <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Retour client lié --}}
            @if($supplierReturn->customerReturn)
                @php $cr = $supplierReturn->customerReturn; @endphp
                <div class="bg-white border border-amber-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-white"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Retour client à l'origine</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500 mb-1">Motif client</dt>
                            <dd class="text-sm text-gray-900">{{ $cr->reason }}</dd>
                        </div>
                        @if($cr->defect_description)
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Défaut constaté</dt>
                                <dd class="text-sm bg-red-50 border border-red-100 rounded-lg p-3 text-gray-900">{{ $cr->defect_description }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Produit de remplacement --}}
            @if($supplierReturn->replacementProduct)
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-emerald-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-4 h-4 text-white"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Produit de remplacement reçu</h3>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="smartphone" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-base font-bold text-emerald-900">{{ $supplierReturn->replacementProduct->productModel->name }}</h4>
                                @if($supplierReturn->replacementProduct->imei)
                                    <p class="text-xs font-mono text-emerald-600 mt-1">IMEI: {{ $supplierReturn->replacementProduct->imei }}</p>
                                @endif
                                <p class="text-xs text-emerald-700 mt-1">En stock — disponible</p>
                            </div>
                            <a href="{{ route('products.show', $supplierReturn->replacementProduct) }}"
                               class="text-sm text-emerald-700 hover:text-emerald-900 flex items-center gap-1 font-medium">
                                Voir <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-5">

            {{-- Actions --}}
            @if(!$supplierReturn->isReplaced())
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @if($supplierReturn->statut->value === 'en_attente')
                            <form method="POST" action="{{ route('supplier-returns.received', $supplierReturn) }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                                    <i data-lucide="package-check" class="w-4 h-4"></i>
                                    Marquer reçu par fournisseur
                                </button>
                            </form>
                        @endif

                        @if(in_array($supplierReturn->statut->value, ['en_attente', 'recu']))
                            <button @click="$dispatch('open-modal-confirm-replacement')"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 rounded-lg text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                Confirmer le remplacement
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Chronologie --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Chronologie</h3>
                </div>
                <div class="p-5">
                    <div class="relative">
                        <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-gray-100"></div>
                        <div class="space-y-5">
                            <div class="relative flex gap-3 pl-9">
                                <div class="absolute left-2.5 top-1.5 w-2.5 h-2.5 rounded-full bg-orange-500 border-2 border-white z-10"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Envoyé au fournisseur</p>
                                    <p class="text-xs text-gray-500">{{ $supplierReturn->date_envoi->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            @if(in_array($supplierReturn->statut->value, ['recu', 'remplace']))
                                <div class="relative flex gap-3 pl-9">
                                    <div class="absolute left-2.5 top-1.5 w-2.5 h-2.5 rounded-full bg-blue-500 border-2 border-white z-10"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Reçu par le fournisseur</p>
                                    </div>
                                </div>
                            @else
                                <div class="relative flex gap-3 pl-9 opacity-40">
                                    <div class="absolute left-2.5 top-1.5 w-2.5 h-2.5 rounded-full bg-gray-300 border-2 border-white z-10"></div>
                                    <div>
                                        <p class="text-sm text-gray-500">En attente de réception</p>
                                        @if($supplierReturn->date_retour_prevue)
                                            <p class="text-xs text-gray-400">Prévu : {{ $supplierReturn->date_retour_prevue->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($supplierReturn->statut->value === 'remplace')
                                <div class="relative flex gap-3 pl-9">
                                    <div class="absolute left-2.5 top-1.5 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white z-10"></div>
                                    <div>
                                        <p class="text-sm font-medium text-emerald-700">Remplacé ✓</p>
                                        @if($supplierReturn->date_retour_effective)
                                            <p class="text-xs text-gray-500">{{ $supplierReturn->date_retour_effective->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="relative flex gap-3 pl-9 opacity-40">
                                    <div class="absolute left-2.5 top-1.5 w-2.5 h-2.5 rounded-full bg-gray-300 border-2 border-white z-10"></div>
                                    <div>
                                        <p class="text-sm text-gray-500">Remplacement confirmé</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmation remplacement --}}
    @if(!$supplierReturn->isReplaced())
        <div
            x-data="{ open: false }"
            x-on:open-modal-confirm-replacement.window="open = true"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50"
        >
            <div x-show="open" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 z-10">
                <div x-show="open"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-gray-100">
                    <form method="POST" action="{{ route('supplier-returns.confirm-replacement', $supplierReturn) }}">
                        @csrf
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="refresh-cw" class="w-5 h-5 text-emerald-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Confirmer le remplacement</h3>
                                    <p class="text-sm text-gray-500">Le nouveau produit sera ajouté au stock</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="new_imei" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        IMEI du nouveau produit
                                    </label>
                                    <input type="text" name="new_imei" id="new_imei" maxlength="15"
                                        placeholder="Entrez le nouvel IMEI (15 chiffres)"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-mono">
                                </div>
                                <div>
                                    <label for="new_serial_number" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Numéro de série (optionnel)
                                    </label>
                                    <input type="text" name="new_serial_number" id="new_serial_number"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                                </div>
                                <div>
                                    <label for="notes" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Notes (optionnel)
                                    </label>
                                    <textarea name="notes" id="notes" rows="2"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                            <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Annuler</button>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i data-lucide="check" class="w-4 h-4"></i> Confirmer le remplacement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</x-app-layout>
