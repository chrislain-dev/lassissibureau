{{-- Vérification des erreurs de session --}}
@if (session()->has('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-800">{{ session('error') }}</p>
    </div>
@endif

<div class="max-w-5xl mx-auto">
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Sélection du produit --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="package" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Quel produit vend-on ?</h3>
                    <p class="text-xs text-gray-500">Choisissez le téléphone ou l'accessoire</p>
                </div>
            </div>

            {{-- Toggle accessoire / produit individuel --}}
            <div class="flex gap-3 mb-6">
                <label class="flex-1 relative flex items-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                    {{ !$is_accessoire ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-400' }}">
                    <input wire:model.live="is_accessoire" type="radio" :value="false" value="0"
                        class="sr-only" {{ !$is_accessoire ? 'checked' : '' }}>
                    <input wire:model.live="is_accessoire" type="radio" value="0"
                        class="rounded-full border-gray-300 text-gray-900 focus:ring-gray-900"
                        {{ !$is_accessoire ? 'checked' : '' }}>
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">📱 Téléphone / Tablette / PC</span>
                        <span class="block text-xs text-gray-500">Avec IMEI ou numéro de série</span>
                    </div>
                </label>

                <label class="flex-1 relative flex items-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                    {{ $is_accessoire ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-400' }}">
                    <input wire:model.live="is_accessoire" type="radio" value="1"
                        class="rounded-full border-gray-300 text-gray-900 focus:ring-gray-900"
                        {{ $is_accessoire ? 'checked' : '' }}>
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">🎧 Accessoire</span>
                        <span class="block text-xs text-gray-500">AirPods, câble, coque, etc.</span>
                    </div>
                </label>
            </div>

            @if($is_accessoire)
                {{-- Mode accessoire --}}
                <div class="space-y-4">
                    <div>
                        <label for="product_model_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Accessoire *
                        </label>
                        <select wire:model.live="product_model_id" id="product_model_id"
                            class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="">Sélectionner un accessoire</option>
                            @foreach($accessoireModels as $model)
                                <option value="{{ $model->id }}">
                                    {{ $model->brand ? $model->brand.' — ' : '' }}{{ $model->name }}
                                    (Stock : {{ $model->quantity }} unités)
                                    — {{ number_format($model->prix_vente_default, 0, ',', ' ') }} FCFA
                                </option>
                            @endforeach
                        </select>
                        @error('product_model_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                        @if(empty($accessoireModels) || $accessoireModels->isEmpty())
                            <p class="mt-2 text-xs text-amber-600">⚠ Aucun accessoire avec du stock disponible. Créez un modèle d'accessoire d'abord.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="quantity_vendue" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                                Quantité *
                            </label>
                            <input wire:model="quantity_vendue" type="number" id="quantity_vendue"
                                min="1" max="9999"
                                class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                                placeholder="1">
                            @error('quantity_vendue') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="prix_vente_accessoire" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                                Prix unitaire (FCFA) *
                            </label>
                            <div class="relative">
                                <input wire:model="prix_vente" type="number" id="prix_vente_accessoire"
                                    min="0" step="100"
                                    class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm"
                                    placeholder="0">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-xs text-gray-500 font-medium">FCFA</span>
                                </div>
                            </div>
                            @error('prix_vente') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($product_model_id && $prix_vente && $quantity_vendue)
                        <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm font-medium text-green-900">
                                💰 Total :
                                <strong>{{ number_format((float)$prix_vente * (int)$quantity_vendue, 0, ',', ' ') }} FCFA</strong>
                                ({{ $quantity_vendue }} x {{ number_format((float)$prix_vente, 0, ',', ' ') }} FCFA)
                            </p>
                        </div>
                    @endif
                </div>
            @else
                {{-- Mode produit individuel --}}
                @if($preselectedProduct)
                    <div class="flex items-start gap-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <i data-lucide="{{ $preselectedProduct->productModel->category->icon() }}" class="w-8 h-8 text-gray-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-900">{{ $preselectedProduct->productModel->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $preselectedProduct->productModel->brand }}</p>

                            @if($preselectedProduct->imei)
                                <p class="text-xs font-mono text-gray-500 mt-2">IMEI: {{ $preselectedProduct->imei }}</p>
                            @endif

                            <div class="mt-3">
                                <span class="text-sm font-medium text-gray-700">Prix de vente: </span>
                                <span class="text-lg font-bold text-gray-900">{{ number_format($preselectedProduct->prix_vente, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div>
                        <label for="product_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Produit *
                        </label>
                        <select wire:model.live="product_id" id="product_id" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="">Sélectionner un produit</option>
                            @foreach($availableProducts as $prod)
                                <option value="{{ $prod->id }}">
                                    {{ $prod->productModel->name }} - {{ $prod->productModel->brand }}
                                    @if($prod->imei) ({{ $prod->imei }}) @endif
                                    - {{ number_format($prod->prix_vente, 0, ',', ' ') }} FCFA
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif
            @endif
        </div>


        {{-- Type de vente --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="tag" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Type de Vente</h3>
                    <p class="text-xs text-gray-500">Vente simple ou avec échange</p>
                </div>
            </div>

            <div class="space-y-3">
                <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                    <input wire:model.live="sale_type" type="radio" value="achat_direct" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Vente Simple</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Le client paie et part avec le produit</span>
                    </div>
                </label>

                @if(!$is_accessoire)
                <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                    <input wire:model.live="sale_type" type="radio" value="troc" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Échange / Troc</span>
                        <span class="block text-xs text-gray-500 mt-0.5">On reprend son ancien téléphone + de l'argent</span>
                    </div>
                </label>
                @endif
            </div>
            @error('sale_type') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        {{-- Type d'acheteur --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Qui achète ?</h3>
                    <p class="text-xs text-gray-500">Client normal ou Revendeur</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                    <input wire:model.live="buyer_type" type="radio" value="direct" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Client Normal</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Un client qui vient acheter pour lui</span>
                    </div>
                </label>

                <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                    <input wire:model.live="buyer_type" type="radio" value="reseller" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Revendeur</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Quelqu'un qui achète pour revendre</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Section Revendeur --}}
        @if($buyer_type === 'reseller')
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="store" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Quel Revendeur ?</h3>
                    <p class="text-xs text-gray-500">Qui prend le produit ?</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Revendeur select --}}
                <div>
                    <label for="reseller_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Nom du Revendeur *
                    </label>
                    <select wire:model="reseller_id" id="reseller_id" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        <option value="">Choisir un revendeur</option>
                        @foreach($resellers as $reseller)
                            <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                        @endforeach
                    </select>
                    @error('reseller_id') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label for="date_depot_revendeur" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Date
                    </label>
                    <input wire:model="date_depot_revendeur" type="date" id="date_depot_revendeur" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                    @error('date_depot_revendeur') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Est-ce qu'il paie maintenant ? --}}
                <div class="relative flex items-start p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center h-5">
                        <input wire:model.live="reseller_confirm_immediate" id="reseller_confirm_immediate" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="reseller_confirm_immediate" class="font-medium text-blue-900">Il paie maintenant</label>
                        <p class="text-blue-700">Cochez si le revendeur <strong>paie tout l'argent aujourd'hui</strong>.<br>Laissez vide s'il prend le téléphone <strong>sans payer maintenant</strong> (il paiera plus tard).</p>
                    </div>
                </div>

                {{-- Comment va-t-il payer ? - SEULEMENT s'il ne paie PAS maintenant --}}
                @if(!$reseller_confirm_immediate)
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Comment va-t-il payer ?</h4>
                        <div class="space-y-3">
                            <label class="relative flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                                <input wire:model.live="payment_option" type="radio" value="unpaid" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Il ne paie rien aujourd'hui</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">Il paiera tout plus tard</span>
                                </div>
                            </label>

                            <label class="relative flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-gray-900 transition-colors has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                                <input wire:model.live="payment_option" type="radio" value="partial" class="mt-0.5 rounded-full border-gray-300 text-gray-900 focus:ring-gray-900">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Il paie une partie aujourd'hui</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">Il verse un peu d'argent maintenant</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Combien il paie maintenant --}}
                    @if($payment_option === 'partial')
                        <div>
                            <label for="amount_paid" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                                Combien il paie maintenant *
                            </label>
                            <div class="relative">
                                <input wire:model="amount_paid" type="number" id="amount_paid" min="0" step="1000" class="block w-full py-2.5 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="0">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-xs text-gray-500 font-medium">FCFA</span>
                                </div>
                            </div>
                            @error('amount_paid') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Quand il va payer le reste --}}
                    <div>
                        <label for="payment_due_date" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Quand il va payer le reste
                        </label>
                        <input wire:model="payment_due_date" type="date" id="payment_due_date" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        @error('payment_due_date') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Comment il paie --}}
                    <div>
                        <label for="payment_method" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Comment il paie
                        </label>
                        <select wire:model="payment_method" id="payment_method" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="cash">Argent liquide</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="check">Chèque</option>
                        </select>
                    </div>
                @else
                    {{-- Message quand il paie maintenant --}}
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-green-900">Vente confirmée ✓</h4>
                                <p class="text-xs text-green-700 mt-1">Le revendeur paie tout l'argent aujourd'hui : <strong>{{ number_format($prix_vente ?? 0, 0, ',', ' ') }} FCFA</strong></p>
                            </div>
                        </div>
                    </div>

                    {{-- Comment il paie --}}
                    <div>
                        <label for="payment_method" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                            Comment il paie *
                        </label>
                        <select wire:model="payment_method" id="payment_method" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                            <option value="cash">Argent liquide</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="check">Chèque</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>
        @endif {{-- Fin section Revendeur --}}

        {{-- Informations client --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Infos du client</h3>
                    <p class="text-xs text-gray-500">Nom et téléphone (pas obligatoire)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="client_name" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Nom du client
                    </label>
                    <input wire:model="client_name" type="text" id="client_name" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Nom complet">
                    @error('client_name') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="client_phone" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Téléphone
                    </label>
                    <input wire:model="client_phone" type="tel" id="client_phone" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="+229 XX XX XX XX">
                    @error('client_phone') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($buyer_type === 'direct')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label for="direct_payment_method" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Comment il paie *
                    </label>
                    <select wire:model="payment_method" id="direct_payment_method" class="block w-full md:w-1/2 py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm">
                        <option value="cash">Argent liquide</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Virement bancaire</option>
                        <option value="check">Chèque</option>
                    </select>
                </div>
            @endif
        </div>

        {{-- Section Troc --}}
        @if($has_trade_in)
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="repeat" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Téléphone qu'on reprend</h3>
                    <p class="text-xs text-gray-500">Son ancien téléphone qu'il nous donne</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="trade_in_modele_recu" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Quel téléphone il nous donne *
                    </label>
                    <input wire:model="trade_in_modele_recu" type="text" id="trade_in_modele_recu" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Ex: iPhone 12 64GB">
                    @error('trade_in_modele_recu') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="trade_in_imei_recu" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        IMEI du téléphone *
                    </label>
                    <input wire:model="trade_in_imei_recu" type="text" id="trade_in_imei_recu" maxlength="15" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-mono" placeholder="123456789012345">
                    @error('trade_in_imei_recu') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-xs font-medium text-gray-700 uppercase tracking-wide mb-3">Calcul : Combien il doit payer</p>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Prix du nouveau téléphone</label>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($prix_vente ?? 0, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <label for="trade_in_valeur_reprise" class="block text-xs text-gray-500 mb-1">Prix de son ancien téléphone *</label>
                            <div class="relative">
                                <input wire:model.live="trade_in_valeur_reprise" type="number" id="trade_in_valeur_reprise" min="0" step="0.01" class="block w-full py-2 pr-16 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm font-semibold" placeholder="0">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-xs text-gray-500 font-medium">FCFA</span>
                                </div>
                            </div>
                            @error('trade_in_valeur_reprise') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-300">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-600 uppercase tracking-wide">Argent qu'il doit payer</span>
                            <span class="text-xl font-bold {{ $trade_in_complement_especes >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($trade_in_complement_especes, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="trade_in_etat_recu" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        État de son ancien téléphone
                    </label>
                    <textarea wire:model="trade_in_etat_recu" id="trade_in_etat_recu" rows="3" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Comment est son téléphone ? (rayures, écran cassé, etc.)"></textarea>
                    @error('trade_in_etat_recu') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        @endif {{-- Fin section Troc --}}

        {{-- Notes --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Notes</h3>
                    <p class="text-xs text-gray-500">Autres informations (pas obligatoire)</p>
                </div>
            </div>

            <textarea wire:model="notes" id="notes" rows="3" class="block w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 text-sm" placeholder="Remarques sur la vente..."></textarea>
            @error('notes') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-4 bg-white border border-gray-200 rounded-lg p-6">
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-red-800">Attention, il y a des erreurs</h4>
                        <ul class="text-xs text-red-600 mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Annuler
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 border border-green-600 rounded-md font-medium text-sm text-white hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </span>
                    <span wire:loading wire:target="save">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="save">Enregistrer la vente</span>
                    <span wire:loading wire:target="save">En cours...</span>
                </button>
            </div>
        </div>
    </form
</div>
