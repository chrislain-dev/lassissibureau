<div>
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-6">

        {{-- Mode de réception --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="truck" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Type de réception</h3>
                    <p class="text-xs text-gray-500">Nouveau produit ou retour fournisseur</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors @if($mode === 'new') border-gray-900 bg-gray-50 @else border-gray-200 hover:border-gray-900 @endif">
                    <input type="radio" wire:model.live="mode" value="new" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Nouveau produit</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Première réception du fournisseur</span>
                    </div>
                </label>

                <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors @if($mode === 'existing') border-gray-900 bg-gray-50 @else border-gray-200 hover:border-gray-900 @endif">
                    <input type="radio" wire:model.live="mode" value="existing" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Produit existant</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Retour fournisseur</span>
                    </div>
                </label>
            </div>
            @error('mode') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        @if($mode === 'new')
            {{-- Nouveau produit --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="package-plus" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Informations produit</h3>
                        <p class="text-xs text-gray-500">Détails du nouveau produit</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="product_model_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Modèle *
                        </label>
                        <select wire:model="product_model_id" id="product_model_id" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="">Sélectionner un modèle</option>
                            @foreach($productModels as $model)
                                <option value="{{ $model->id }}">{{ $model->brand }} {{ $model->name }} - {{ $model->storage ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('product_model_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="imei" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            IMEI * <span class="text-gray-400 font-normal">(15 chiffres)</span>
                        </label>
                        <input type="text" wire:model.blur="imei" id="imei" maxlength="15" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-mono" placeholder="123456789012345">
                        @error('imei') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="serial_number" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Numéro de série
                        </label>
                        <input type="text" wire:model="serial_number" id="serial_number" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="SN123456">
                        @error('serial_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if(auth()->user()->isAdmin())
                    <div>
                        <label for="prix_achat" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Prix d'achat *
                        </label>
                        <div class="relative">
                            <input type="number" wire:model.live="prix_achat" id="prix_achat" min="0" step="0.01" class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-semibold" placeholder="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-xs text-gray-500 font-medium">FCFA</span>
                            </div>
                        </div>
                        @error('prix_achat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="marge_percentage" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Marge (%)
                        </label>
                        <input type="number" wire:model.live="marge_percentage" id="marge_percentage" min="0" max="100" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="20">
                        @error('marge_percentage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div>
                        <label for="prix_vente" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Prix de vente *
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="prix_vente" id="prix_vente" min="0" step="0.01" class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-semibold" placeholder="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-xs text-gray-500 font-medium">FCFA</span>
                            </div>
                        </div>
                        @if(auth()->user()->isAdmin() && $prix_achat && $prix_vente)
                            <p class="mt-1 text-xs text-gray-500">
                                Bénéfice: <span class="font-semibold text-green-600">+{{ number_format($prix_vente - $prix_achat, 0, ',', ' ') }} FCFA</span>
                            </p>
                        @endif
                        @error('prix_vente') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="fournisseur" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Fournisseur
                        </label>
                        <input type="text" wire:model="fournisseur" id="fournisseur" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Nom du fournisseur">
                        @error('fournisseur') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="date_achat" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Date d'entrée *
                        </label>
                        <input type="date" wire:model="date_achat" id="date_achat" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        @error('date_achat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="condition" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            État général
                        </label>
                        <input type="text" wire:model="condition" id="condition" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Ex: Neuf, Excellent, Bon...">
                        @error('condition') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="defauts" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Défauts constatés
                        </label>
                        <textarea wire:model="defauts" id="defauts" rows="2" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Détails des défauts ou problèmes..."></textarea>
                        @error('defauts') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @else
            {{-- Produit existant --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Sélection du produit</h3>
                        <p class="text-xs text-gray-500">Produit en retour fournisseur</p>
                    </div>
                </div>

                <div>
                    <label for="existing_product_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Produit *
                    </label>
                    <select wire:model="existing_product_id" id="existing_product_id" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        <option value="">Sélectionner un produit</option>
                        @foreach($existingProducts as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->productModel->brand }} {{ $product->productModel->name }}
                                @if($product->imei) - IMEI: {{ $product->imei }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('existing_product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- Notes --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Notes</h3>
                    <p class="text-xs text-gray-500">Informations complémentaires</p>
                </div>
            </div>

            <textarea wire:model="notes" id="notes" rows="3" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Notes sur la réception..."></textarea>
            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-4 bg-white border border-gray-200 rounded-lg p-6">
            <a href="{{ route('stock-movements.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
                Annuler
            </a>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 border border-green-600 rounded-md font-medium text-sm text-white hover:bg-green-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="submit">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </span>
                <span wire:loading wire:target="submit">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="submit">Enregistrer la réception</span>
                <span wire:loading wire:target="submit">Traitement...</span>
            </button>
        </div>
    </form>
</div>
