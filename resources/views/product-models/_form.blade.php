<div class="space-y-6 sm:space-y-8" x-data="{
    achat: {{ old('prix_revient_default', $productModel->prix_revient_default ?? 0) }},
    vente: {{ old('prix_vente_default', $productModel->prix_vente_default ?? 0) }},
    category: '{{ old('category', $productModel->category->value ?? '') }}',
    get marge() {
        return this.vente - this.achat;
    },
    get pourcentage() {
        if (this.achat === 0) return 0;
        return ((this.marge / this.achat) * 100).toFixed(1);
    }
}">
    {{-- Section: Informations générales --}}
    <div>
        <div class="mb-3 sm:mb-4 pb-2 sm:pb-3 border-b border-gray-200">
            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Informations générales</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Détails du modèle de produit</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <x-input-label for="name" value="Nom du modèle *" class="font-medium text-xs sm:text-sm" />
                <x-text-input
                    type="text"
                    name="name"
                    id="name"
                    :value="old('name', $productModel->name ?? '')"
                    class="mt-1.5 sm:mt-2 block w-full text-sm"
                    placeholder="Ex: iPhone 15 Pro Max 256GB"
                    required
                />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5 sm:mt-2" />
            </div>

            <div>
                <x-input-label for="brand" value="Marque *" class="font-medium text-xs sm:text-sm" />
                <select
                    name="brand"
                    id="brand"
                    required
                    class="mt-1.5 sm:mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 text-sm"
                >
                    <option value="">Sélectionner une marque</option>
                    @php
                        $brands = [
                            'Apple', 'Samsung', 'Xiaomi', 'Huawei', 'Oppo', 'Vivo',
                            'Realme', 'OnePlus', 'Google', 'Nokia', 'Motorola', 'Sony',
                            'Asus', 'Lenovo', 'HP', 'Dell', 'Acer', 'Microsoft', 'LG', 'Anker', 'Générique'
                        ];
                    @endphp
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" {{ old('brand', $productModel->brand ?? '') == $brand ? 'selected' : '' }}>
                            {{ $brand }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('brand')" class="mt-1.5 sm:mt-2" />

            </div>
        </div>

        <div class="mt-4 sm:mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <x-input-label for="category" value="Catégorie *" class="font-medium text-xs sm:text-sm" />
                <select
                    name="category"
                    id="category"
                    required
                    x-model="category"
                    class="mt-1.5 sm:mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 text-sm"
                >
                    <option value="">Sélectionner une catégorie</option>
                    <option value="telephone" {{ old('category', $productModel->category->value ?? '') == 'telephone' ? 'selected' : '' }}>
                        📱 Téléphone
                    </option>
                    <option value="tablette" {{ old('category', $productModel->category->value ?? '') == 'tablette' ? 'selected' : '' }}>
                        💻 Tablette
                    </option>
                    <option value="pc" {{ old('category', $productModel->category->value ?? '') == 'pc' ? 'selected' : '' }}>
                        🖥️ Ordinateur
                    </option>
                    <option value="accessoire" {{ old('category', $productModel->category->value ?? '') == 'accessoire' ? 'selected' : '' }}>
                        🎧 Accessoire
                    </option>
                </select>
                <x-input-error :messages="$errors->get('category')" class="mt-1.5 sm:mt-2" />
            </div>

            <div>
                <x-input-label for="condition_type" value="État du produit *" class="font-medium text-xs sm:text-sm" />
                <select
                    name="condition_type"
                    id="condition_type"
                    required
                    class="mt-1.5 sm:mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 text-sm"
                >
                    <option value="neuf" {{ old('condition_type', $productModel->condition_type->value ?? 'neuf') == 'neuf' ? 'selected' : '' }}>
                        ✨ Neuf
                    </option>
                    <option value="venu" {{ old('condition_type', $productModel->condition_type->value ?? '') == 'venu' ? 'selected' : '' }}>
                        📦 Venu
                    </option>
                    <option value="occasion" {{ old('condition_type', $productModel->condition_type->value ?? '') == 'occasion' ? 'selected' : '' }}>
                        ♻️ Occasion
                    </option>
                </select>
                <x-input-error :messages="$errors->get('condition_type')" class="mt-1.5 sm:mt-2" />
            </div>
        </div>

        <div class="mt-4 sm:mt-6">
            <x-input-label for="description" value="Description" class="font-medium text-xs sm:text-sm" />
            <textarea
                name="description"
                id="description"
                rows="4"
                placeholder="Caractéristiques principales, points forts du produit..."
                class="mt-1.5 sm:mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 text-sm"
            >{{ old('description', $productModel->description ?? '') }}</textarea>
            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500">Optionnel - Ajoutez une description pour faciliter l'identification</p>
            <x-input-error :messages="$errors->get('description')" class="mt-1.5 sm:mt-2" />
        </div>

        @if(!isset($productModel))
        <template x-if="category === 'accessoire'">
            <div class="mt-4 sm:mt-6 p-4 sm:p-5 bg-blue-50 border border-blue-100 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <i data-lucide="package-plus" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <x-input-label for="quantity" value="Quantité initiale en stock" class="font-medium text-sm text-blue-900" />
                        <p class="text-xs text-blue-700 mt-1 mb-3">Saisissez la quantité pour générer automatiquement les produits en stock (IMEI et numéro de série vides).</p>
                        
                        <x-text-input
                            type="number"
                            name="quantity"
                            id="quantity"
                            value="{{ old('quantity', '') }}"
                            class="block w-full sm:max-w-xs text-sm border-blue-200 focus:border-blue-500 focus:ring-blue-500 bg-white"
                            min="1"
                            step="1"
                            placeholder="Ex: 50"
                        />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-1.5 sm:mt-2" />
                    </div>
                </div>
            </div>
        </template>
        @endif
    </div>

    {{-- Section: Tarification --}}
    <div>
        <div class="mb-3 sm:mb-4 pb-2 sm:pb-3 border-b border-gray-200">
            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Tarification</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Prix par défaut appliqués aux nouveaux produits</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <x-input-label for="prix_revient_default" value="Prix d'achat par défaut *" class="font-medium text-xs sm:text-sm" />
                <div class="relative mt-1.5 sm:mt-2">
                    <x-text-input
                        type="number"
                        name="prix_revient_default"
                        id="prix_revient_default"
                        :value="old('prix_revient_default', $productModel->prix_revient_default ?? '')"
                        class="block w-full pr-14 sm:pr-16 text-sm"
                        min="0"
                        step="100"
                        placeholder="0"
                        required
                        x-model.number="achat"
                    />
                    <div class="absolute inset-y-0 right-0 pr-2 sm:pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-xs sm:text-sm font-medium">FCFA</span>
                    </div>
                </div>
                <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500">Coût d'acquisition du produit</p>
                <x-input-error :messages="$errors->get('prix_revient_default')" class="mt-1.5 sm:mt-2" />
            </div>

            <div>
                <x-input-label for="prix_vente_default" value="Prix de vente par défaut *" class="font-medium text-xs sm:text-sm" />
                <div class="relative mt-1.5 sm:mt-2">
                    <x-text-input
                        type="number"
                        name="prix_vente_default"
                        id="prix_vente_default"
                        :value="old('prix_vente_default', $productModel->prix_vente_default ?? '')"
                        class="block w-full pr-14 sm:pr-16 text-sm"
                        min="0"
                        step="100"
                        placeholder="0"
                        required
                        x-model.number="vente"
                    />
                    <div class="absolute inset-y-0 right-0 pr-2 sm:pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-xs sm:text-sm font-medium">FCFA</span>
                    </div>
                </div>
                <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500">Prix proposé aux clients</p>
                <x-input-error :messages="$errors->get('prix_vente_default')" class="mt-1.5 sm:mt-2" />
            </div>

            <div>
                <x-input-label for="prix_vente_revendeur" value="Prix revendeur *" class="font-medium text-xs sm:text-sm" />
                <div class="relative mt-1.5 sm:mt-2">
                    <x-text-input
                        type="number"
                        name="prix_vente_revendeur"
                        id="prix_vente_revendeur"
                        :value="old('prix_vente_revendeur', $productModel->prix_vente_revendeur ?? '')"
                        class="block w-full pr-14 sm:pr-16 text-sm"
                        min="0"
                        step="100"
                        placeholder="0"
                        required
                    />
                    <div class="absolute inset-y-0 right-0 pr-2 sm:pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-xs sm:text-sm font-medium">FCFA</span>
                    </div>
                </div>
                <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500">Prix proposé aux partenaires revedeurs</p>
                <x-input-error :messages="$errors->get('prix_vente_revendeur')" class="mt-1.5 sm:mt-2" />
            </div>
        </div>

        {{-- Marge calculée --}}
        <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-700">Marge prévisionnelle</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">Calculée automatiquement</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-base sm:text-lg font-semibold text-gray-900 break-all" x-text="marge.toLocaleString('fr-FR') + ' FCFA'"></p>
                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5" x-text="'(' + pourcentage + '%)'"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Gestion du stock --}}
    <div>
        <div class="mb-3 sm:mb-4 pb-2 sm:pb-3 border-b border-gray-200">
            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Gestion du stock</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Paramètres d'alerte de stock</p>
        </div>

        <div>
            <x-input-label for="stock_minimum" value="Seuil d'alerte de stock *" class="font-medium text-xs sm:text-sm" />
            <x-text-input
                type="number"
                name="stock_minimum"
                id="stock_minimum"
                :value="old('stock_minimum', $productModel->stock_minimum ?? 5)"
                class="mt-1.5 sm:mt-2 block w-full sm:max-w-xs text-sm"
                min="0"
                placeholder="5"
                required
            />
            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500">
                <span class="inline-flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0"></i>
                    <span>Une alerte sera affichée si le stock descend en dessous de cette valeur</span>
                </span>
            </p>
            <x-input-error :messages="$errors->get('stock_minimum')" class="mt-1.5 sm:mt-2" />
        </div>
    </div>

    {{-- Section: Statut --}}
    <div>
        <div class="mb-3 sm:mb-4 pb-2 sm:pb-3 border-b border-gray-200">
            <h3 class="text-sm sm:text-base font-semibold text-gray-900">Statut</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">Disponibilité du modèle</p>
        </div>

        <div class="flex items-start gap-2 sm:gap-3">
            <div class="flex items-center h-5">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    value="1"
                    {{ old('is_active', $productModel->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 focus:ring-1"
                >
            </div>
            <div class="min-w-0 flex-1">
                <label for="is_active" class="font-medium text-xs sm:text-sm text-gray-900 cursor-pointer">
                    Modèle actif
                </label>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 sm:mt-1">
                    Les modèles inactifs ne seront pas proposés lors de l'ajout de nouveaux produits
                </p>
            </div>
        </div>
        <x-input-error :messages="$errors->get('is_active')" class="mt-1.5 sm:mt-2" />
    </div>

    {{-- Actions --}}
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 sm:pt-6 border-t border-gray-200">
        <a
            href="{{ route('product-models.index') }}"
            class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-xs sm:text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
        >
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Annuler
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs sm:text-sm text-white hover:bg-gray-800 active:bg-gray-950 transition-all hover:shadow-lg hover:scale-105"
        >
            <i data-lucide="save" class="w-4 h-4"></i>
            <span class="hidden sm:inline">{{ isset($productModel) ? 'Mettre à jour le modèle' : 'Créer le modèle' }}</span>
            <span class="sm:hidden">{{ isset($productModel) ? 'Mettre à jour' : 'Créer' }}</span>
        </button>
    </div>
</div>