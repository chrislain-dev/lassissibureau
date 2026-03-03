<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900">Modifier le produit</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">ID #{{ $product->id }}</p>
            </div>
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs sm:text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                <span class="hidden sm:inline">Retour</span>
            </a>
        </div>
    </x-slot>

    <x-alerts.success :message="session('success')" />
    <x-alerts.error :message="session('error')" />

    <div class="max-w-7xl mx-auto">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            {{-- Sélection du modèle --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="box" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wide">Informations du modèle</h3>
                        <p class="text-xs text-gray-500 hidden sm:block">Sélectionnez le modèle de produit</p>
                    </div>
                </div>

                <div>
                    <label for="product_model_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Modèle de produit *
                    </label>
                    <select name="product_model_id" id="product_model_id" required
                            class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        <option value="">Sélectionner un modèle</option>
                        @foreach($productModels as $model)
                            <option value="{{ $model->id }}" {{ old('product_model_id', $product->product_model_id) == $model->id ? 'selected' : '' }}>
                                {{ $model->name }} - {{ $model->brand }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('product_model_id')" class="mt-2" />
                </div>
            </div>

            {{-- Identification --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="hash" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wide">Identification</h3>
                        <p class="text-xs text-gray-500 hidden sm:block">IMEI et numéro de série</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="imei" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            IMEI
                        </label>
                        <input
                            type="text"
                            name="imei"
                            id="imei"
                            value="{{ old('imei', $product->imei) }}"
                            class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-mono"
                            maxlength="15"
                            pattern="[0-9]{15}"
                            placeholder="123456789012345"
                        />
                        <p class="mt-1.5 text-xs text-gray-500">15 chiffres (requis pour téléphones)</p>
                        <x-input-error :messages="$errors->get('imei')" class="mt-2" />
                    </div>

                    <div>
                        <label for="serial_number" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Numéro de série
                        </label>
                        <input
                            type="text"
                            name="serial_number"
                            id="serial_number"
                            value="{{ old('serial_number', $product->serial_number) }}"
                            class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-mono"
                        />
                        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- État et Localisation --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wide">État & Localisation</h3>
                        <p class="text-xs text-gray-500 hidden sm:block">Statut actuel du produit</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="state" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            État *
                        </label>
                        <select name="state" id="state" required
                                class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            @foreach($states as $state)
                                <option value="{{ $state['value'] }}" {{ old('state', $product->state->value) == $state['value'] ? 'selected' : '' }}>
                                    {{ $state['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                    </div>

                    <div>
                        <label for="location" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Localisation *
                        </label>
                        <select name="location" id="location" required
                                class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            @foreach($locations as $location)
                                <option value="{{ $location['value'] }}" {{ old('location', $product->location->value) == $location['value'] ? 'selected' : '' }}>
                                    {{ $location['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Prix --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="coins" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wide">Tarification</h3>
                        <p class="text-xs text-gray-500 hidden sm:block">Prix d'achat et de vente</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="prix_achat" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                                Prix d'achat (FCFA) *
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    name="prix_achat"
                                    id="prix_achat"
                                    value="{{ old('prix_achat', $product->prix_achat) }}"
                                    class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                                    min="0"
                                    step="1"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-xs text-gray-500 font-medium">FCFA</span>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('prix_achat')" class="mt-2" />
                        </div>
                    @else
                        {{-- CHAMP CACHÉ POUR QUE LA VALIDATION PASSE TOUT DE MÊME LORS DE LA MODIFICATION PAR LE VENDEUR --}}
                        <input type="hidden" name="prix_achat" value="{{ $product->prix_achat }}">
                    @endif

                    <div>
                        <label for="prix_vente" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Prix de vente (FCFA) *
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                name="prix_vente"
                                id="prix_vente"
                                value="{{ old('prix_vente', $product->prix_vente) }}"
                                class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                                min="0"
                                step="1"
                                required
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-xs text-gray-500 font-medium">FCFA</span>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 hidden sm:block">
                            @if(auth()->user()->isAdmin())
                            Doit être ≥ au prix d'achat
                            @endif
                        </p>
                        <x-input-error :messages="$errors->get('prix_vente')" class="mt-2" />
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                {{-- Aperçu de la marge --}}
                <div id="margin-preview" class="mt-4 sm:mt-6 p-3 sm:p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bénéfice</p>
                            <p id="benefice-value" class="text-base sm:text-lg font-bold text-gray-900">{{ number_format($product->benefice_potentiel, 0, ',', ' ') }} <span class="text-xs sm:text-sm font-normal">FCFA</span></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Marge</p>
                            <p id="marge-value" class="text-base sm:text-lg font-bold text-gray-900">{{ $product->marge_percentage }}%</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Détails complémentaires --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="info" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wide">Détails complémentaires</h3>
                        <p class="text-xs text-gray-500 hidden sm:block">Condition et informations d'achat</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div>
                        <label for="condition" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Condition
                        </label>
                        <select name="condition" id="condition"
                                class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="">Sélectionner une condition</option>
                            @foreach($conditions as $condition)
                                <option value="{{ $condition }}" {{ old('condition', $product->condition) == $condition ? 'selected' : '' }}>
                                    {{ $condition }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('condition')" class="mt-2" />
                    </div>

                    <div>
                        <label for="date_achat" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Date d'entrée
                        </label>
                        <input
                            type="date"
                            name="date_achat"
                            id="date_achat"
                            value="{{ old('date_achat', $product->date_achat?->format('Y-m-d')) }}"
                            class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                            max="{{ now()->format('Y-m-d') }}"
                        />
                        <x-input-error :messages="$errors->get('date_achat')" class="mt-2" />
                    </div>
                </div>

                <div class="mb-4 sm:mb-6">
                    <label for="fournisseur" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Fournisseur
                    </label>
                    <input
                        type="text"
                        name="fournisseur"
                        id="fournisseur"
                        value="{{ old('fournisseur', $product->fournisseur) }}"
                        class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                        placeholder="Nom du fournisseur"
                    />
                    <x-input-error :messages="$errors->get('fournisseur')" class="mt-2" />
                </div>

                <div class="mb-4 sm:mb-6">
                    <label for="defauts" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Défauts constatés
                    </label>
                    <textarea
                        name="defauts"
                        id="defauts"
                        rows="3"
                        class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm resize-none"
                        placeholder="Décrivez les défauts..."
                    >{{ old('defauts', $product->defauts) }}</textarea>
                    <x-input-error :messages="$errors->get('defauts')" class="mt-2" />
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Notes
                    </label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm resize-none"
                        placeholder="Notes additionnelles..."
                    >{{ old('notes', $product->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4 bg-white border border-gray-200 rounded-lg p-4 sm:p-6 sticky bottom-0 sm:static shadow-lg sm:shadow-none">
                <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Annuler
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-black border border-black rounded-md font-medium text-sm text-white hover:bg-gray-800 transition-colors shadow-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Mettre à jour le produit</span>
                    <span class="sm:hidden">Enregistrer</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    @if(auth()->user()->isAdmin())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prixAchatInput = document.getElementById('prix_achat');
            const prixVenteInput = document.getElementById('prix_vente');
            const marginPreview = document.getElementById('margin-preview');
            const beneficeValue = document.getElementById('benefice-value');
            const margeValue = document.getElementById('marge-value');

            prixAchatInput.addEventListener('input', calculateMargin);
            prixVenteInput.addEventListener('input', calculateMargin);

            function calculateMargin() {
                const prixAchat = parseFloat(prixAchatInput.value) || 0;
                const prixVente = parseFloat(prixVenteInput.value) || 0;

                if (prixAchat > 0 && prixVente > 0) {
                    const benefice = prixVente - prixAchat;
                    const marge = ((benefice / prixVente) * 100);

                    // Format responsive
                    const isMobile = window.innerWidth < 640;
                    beneficeValue.innerHTML = benefice.toLocaleString('fr-FR') + ' <span class="text-xs ' + (isMobile ? '' : 'sm:text-sm') + ' font-normal">FCFA</span>';
                    margeValue.textContent = marge.toFixed(2) + '%';

                    // Color code based on margin
                    margeValue.classList.remove('text-gray-900', 'text-yellow-600', 'text-green-600', 'text-red-600');
                    if (marge >= 30) {
                        margeValue.classList.add('text-green-600');
                    } else if (marge >= 15) {
                        margeValue.classList.add('text-yellow-600');
                    } else if (marge < 0) {
                        margeValue.classList.add('text-red-600');
                    } else {
                        margeValue.classList.add('text-gray-900');
                    }

                    marginPreview.classList.remove('hidden');
                } else {
                    marginPreview.classList.add('hidden');
                }
            }

            // Trigger on page load
            calculateMargin();
        });
    </script>
    @endif
    @endpush
</x-app-layout>