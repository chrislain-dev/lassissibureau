@props([
    'id',
    'title' => 'Confirmer l\'action',
    'message' => 'Êtes-vous sûr de vouloir effectuer cette action ?',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'danger' => true,
])

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-show="open"
    x-cloak
    class="relative z-50"
    aria-labelledby="modal-title-{{ $id }}"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
        @click="open = false"
    ></div>

    {{-- Panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6 border border-gray-100"
        >
            {{-- Icône --}}
            <div class="flex items-start gap-4 mb-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                    {{ $danger ? 'bg-red-100' : 'bg-amber-100' }}">
                    <i data-lucide="{{ $danger ? 'alert-triangle' : 'help-circle' }}"
                       class="w-5 h-5 {{ $danger ? 'text-red-600' : 'text-amber-600' }}"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900" id="modal-title-{{ $id }}">
                        {{ $title }}
                    </h3>
                    @if($message)
                        <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
                    @endif
                    {{ $slot }}
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button
                    type="button"
                    @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ $cancelText }}
                </button>

                {{-- Le form est injecté via le slot 'form' si fourni, sinon bouton simple --}}
                @if(isset($form))
                    {{ $form }}
                @else
                    <button
                        type="button"
                        @click="open = false; $dispatch('confirm-{{ $id }}')"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors
                            {{ $danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}"
                    >
                        {{ $confirmText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
