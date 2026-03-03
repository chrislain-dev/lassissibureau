<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Créer un produit</h2>
                <p class="text-sm text-gray-500 mt-1">Ajoutez un ou plusieurs produits à votre inventaire</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 hover:shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Retour</span>
            </a>
        </div>
    </x-slot>

    <div class="w-full">
        <form wire:submit="save" class="space-y-4 sm:space-y-6">

            {{-- Layout responsive : Stack sur mobile, Grid sur desktop --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

                {{-- Colonne gauche : Formulaire --}}
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                    {{-- Sélection du modèle --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-4 sm:mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-900 to-gray-700 rounded-lg flex items-center justify-center shadow-sm">
                                <i data-lucide="box" class="w-5 h-5 text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide truncate">Informations du modèle</h3>
                                <p class="text-xs text-gray-500 truncate">Sélectionnez le modèle de produit</p>
                            </div>
                        </div>

                        <div>
                            <label for="product_model_id" class="block text-xs font-medium text-gray-700 mb-2">
                                Modèle de produit <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="product_model_id" id="product_model_id" required
                                    wire:loading.attr="disabled"
                                    class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200">
                                <option value="">Sélectionner un modèle</option>
                                @foreach($this->productModels as $model)
                                    <option value="{{ $model->id }}">
                                        {{ $model->name }} - {{ $model->brand }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_model_id')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if($this->selectedModel)
                            <div class="mt-4 p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"></i>
                                    <div class="text-xs text-blue-700 min-w-0">
                                        <div class="flex flex-wrap gap-x-3 gap-y-1">
                                            <span><strong>Catégorie:</strong> {{ $this->selectedModel->category }}</span>
                                            @if($this->selectedModel->storage)
                                                <span><strong>Stockage:</strong> {{ $this->selectedModel->storage }}</span>
                                            @endif
                                            @if($this->selectedModel->color)
                                                <span><strong>Couleur:</strong> {{ $this->selectedModel->color }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- État et Localisation --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-4 sm:mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-900 to-gray-700 rounded-lg flex items-center justify-center shadow-sm">
                                <i data-lucide="map-pin" class="w-5 h-5 text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide truncate">État & Localisation</h3>
                                <p class="text-xs text-gray-500 truncate">Statut actuel des produits</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label for="state" class="block text-xs font-medium text-gray-700 mb-2">
                                    État <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="state" id="state" required
                                        class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200">
                                    @foreach($this->states() as $stateOption)
                                        <option value="{{ $stateOption['value'] }}">{{ $stateOption['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="location" class="block text-xs font-medium text-gray-700 mb-2">
                                    Localisation <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="location" id="location" required
                                        class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200">
                                    @foreach($this->locations() as $locationOption)
                                        <option value="{{ $locationOption['value'] }}">
                                            {{ $locationOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    {{-- Détails complémentaires --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-4 sm:mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-900 to-gray-700 rounded-lg flex items-center justify-center shadow-sm">
                                <i data-lucide="info" class="w-5 h-5 text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide truncate">Détails complémentaires</h3>
                                <p class="text-xs text-gray-500 truncate">Condition et informations d'achat</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label for="condition" class="block text-xs font-medium text-gray-700 mb-2">
                                    Condition
                                </label>
                                {{-- ✅ FIX: Utiliser wire:model.live pour conversion immédiate --}}
                                <select wire:model.live="condition" id="condition"
                                        class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200">
                                    {{-- ✅ FIX: Option vide avec value vide (sera convertie en null par updatedCondition) --}}
                                    <option value="">Sélectionner une condition</option>
                                    @foreach($conditions as $cond)
                                        <option value="{{ $cond }}">{{ $cond }}</option>
                                    @endforeach
                                </select>
                                @error('condition')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="date_achat" class="block text-xs font-medium text-gray-700 mb-2">
                                    Date d'entrée
                                </label>
                                <input
                                    type="date"
                                    wire:model="date_achat"
                                    id="date_achat"
                                    class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200"
                                    max="{{ now()->format('Y-m-d') }}"
                                />
                                @error('date_achat')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 sm:mb-6">
                            <label for="fournisseur" class="block text-xs font-medium text-gray-700 mb-2">
                                Fournisseur
                            </label>
                            <input
                                type="text"
                                wire:model="fournisseur"
                                id="fournisseur"
                                class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200"
                                placeholder="Nom du fournisseur"
                            />
                            @error('fournisseur')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-4 sm:mb-6">
                            <label for="defauts" class="block text-xs font-medium text-gray-700 mb-2">
                                Défauts constatés
                            </label>
                            <textarea
                                wire:model="defauts"
                                id="defauts"
                                rows="3"
                                class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200"
                                placeholder="Décrivez les défauts ou problèmes des produits..."
                            ></textarea>
                            @error('defauts')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea
                                wire:model="notes"
                                id="notes"
                                rows="3"
                                class="block w-full py-2.5 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-sm transition-all duration-200"
                                placeholder="Notes additionnelles..."
                            ></textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Colonne droite : Section IMEI (sticky sur desktop) --}}
                <div class="lg:col-span-1">
                    <div class="lg:sticky lg:top-6">
                        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-4 sm:mb-6">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-gray-900 to-gray-700 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                                        <i data-lucide="hash" class="w-5 h-5 text-white"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide truncate">Identifications</h3>
                                        <p class="text-xs text-gray-500 truncate">IMEI et numéros de série</p>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="addProduct"
                                class="w-full mb-4 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-lg text-sm font-medium hover:from-gray-800 hover:to-gray-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Ajouter un produit
                            </button>

                            @error('products')
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex items-center gap-2 text-xs text-red-700">
                                        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror

                            <div class="space-y-3 max-h-[400px] sm:max-h-[500px] lg:max-h-[calc(100vh-20rem)] overflow-y-auto pr-1">
                                @foreach($products as $index => $product)
                                    <div class="relative p-3 sm:p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-all duration-200 bg-gray-50 hover:shadow-sm"
                                         wire:key="product-{{ $product['id'] }}">

                                        {{-- En-tête du produit --}}
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-xs font-semibold text-gray-700">Produit #{{ $index + 1 }}</span>
                                            @if(count($products) > 1)
                                                <button
                                                    type="button"
                                                    wire:click="removeProduct({{ $index }})"
                                                    class="w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center transition-colors duration-200">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- IMEI --}}
                                        <div class="mb-3">
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                                IMEI
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="products.{{ $index }}.imei"
                                                class="block w-full py-2 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-xs font-mono bg-white transition-all duration-200"
                                                maxlength="15"
                                                placeholder="123456789012345"
                                            />
                                            @error("products.{$index}.imei")
                                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        {{-- Numéro de série --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                                N° de série
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="products.{{ $index }}.serial_number"
                                                class="block w-full py-2 px-3 rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 text-xs font-mono bg-white transition-all duration-200"
                                                placeholder="SN123456789"
                                            />
                                            @error("products.{$index}.serial_number")
                                                <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($products) > 1)
                                <div class="mt-4 p-3 bg-gradient-to-br from-green-50 to-green-100/50 border border-green-200 rounded-lg">
                                    <div class="flex items-center gap-2 text-xs text-green-700">
                                        <i data-lucide="package-check" class="w-4 h-4 flex-shrink-0"></i>
                                        <span><strong>{{ count($products) }}</strong> produit{{ count($products) > 1 ? 's' : '' }} à créer</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="sticky bottom-0 z-10 bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-lg">
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 transition-all duration-200 hover:shadow-sm">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Annuler
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-black to-gray-900 border border-black rounded-lg font-medium text-sm text-white hover:from-gray-900 hover:to-gray-800 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ count($products) > 1 ? 'Créer les ' . count($products) . ' produits' : 'Créer le produit' }}</span>
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            <span>Création en cours...</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>