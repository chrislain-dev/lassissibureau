<?php

use function Livewire\Volt\{state};

?>

<div>
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0A0A0A] border-r border-white/[0.08] transform transition-transform duration-300 ease-in-out lg:translate-x-0 shadow-2xl flex flex-col" :class="{ '-translate-x-full': !sidebarOpen }">

        <!-- Logo Area -->
        <div class="h-16 flex items-center px-4 border-b border-white/[0.08] flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group w-full">
                <div class="w-7 h-7 rounded-md bg-white flex items-center justify-center flex-shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-4 h-4 object-contain">
                </div>
                <span class="font-semibold text-gray-200 text-sm tracking-tight truncate">{{ config('app.name') }}</span>
            </a>
        </div>

        <!-- Scrollable Navigation -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent space-y-6">

            <!-- General -->
            <div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('dashboard'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('dashboard'),
                        ])>
                            <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- VENTES Section -->
            @canany(['sales.view', 'sales.create'])
            <div>
                <p class="px-2.5 mb-2 text-[10px] font-medium text-gray-500 uppercase tracking-widest">Ventes</p>
                <ul class="space-y-1">
                    @can('sales.view')
                    <li>
                        <a href="{{ route('sales.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('sales.index'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('sales.index'),
                        ])>
                            <i data-lucide="list" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Toutes les ventes</span>
                        </a>
                    </li>
                    @endcan

                    @can('sales.create')
                    <li>
                        <a href="{{ route('sales.create') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('sales.create'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('sales.create'),
                        ])>
                            <i data-lucide="plus-circle" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Nouvelle vente</span>
                        </a>
                    </li>
                    @endcan

                    @can('resellers.manage')
                    <li>
                        <a href="{{ route('sales.resellers') }}" @class([
                            'flex items-center justify-between px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('sales.resellers'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('sales.resellers'),
                        ])>
                            <div class="flex items-center gap-3">
                                <i data-lucide="store" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                                <span>Ventes revendeurs</span>
                            </div>
                            @php $pendingCount = \App\Models\Sale::pending()->count(); @endphp
                            @if($pendingCount > 0)
                                <span class="flex items-center justify-center px-1.5 h-5 min-w-[1.25rem] text-[10px] font-medium text-blue-300 bg-blue-500/10 rounded-full border border-blue-500/20">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('sales.payments.pending') }}" @class([
                            'flex items-center justify-between px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('sales.payments.pending'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('sales.payments.pending'),
                        ])>
                            <div class="flex items-center gap-3">
                                <i data-lucide="credit-card" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                                <span>Paiements pending</span>
                            </div>
                            @php $unpaidCount = \App\Models\Sale::withPendingPayment()->count(); @endphp
                            @if($unpaidCount > 0)
                                <span class="flex items-center justify-center px-1.5 h-5 min-w-[1.25rem] text-[10px] font-medium text-orange-300 bg-orange-500/10 rounded-full border border-orange-500/20">
                                    {{ $unpaidCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            <!-- REPRISES & RETOURS -->
            @canany(['viewAny-TradeIn', 'returns.manage'])
            @php
                $canTradeIn = auth()->user()->can('viewAny', \App\Models\TradeIn::class);
                $canReturns = auth()->user()->can('returns.manage');
            @endphp
            @if($canTradeIn || $canReturns)
            <div>
                <p class="px-2.5 mb-2 text-[10px] font-medium text-gray-500 uppercase tracking-widest">Reprises & Retours</p>
                <ul class="space-y-1">
                    @if($canTradeIn)
                    <li>
                        <a href="{{ route('trade-ins.pending') }}" @class([
                            'flex items-center justify-between px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('trade-ins.pending'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('trade-ins.pending'),
                        ])>
                            <div class="flex items-center gap-3">
                                <i data-lucide="clock" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                                <span>Trocs en attente</span>
                            </div>
                            @php $pendingTradeIns = \App\Models\TradeIn::pending()->count(); @endphp
                            @if($pendingTradeIns > 0)
                                <span class="flex items-center justify-center px-1.5 h-5 min-w-[1.25rem] text-[10px] font-medium text-amber-300 bg-amber-500/10 rounded-full border border-amber-500/20">
                                    {{ $pendingTradeIns }}
                                </span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trade-ins.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('trade-ins.index'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('trade-ins.index'),
                        ])>
                            <i data-lucide="repeat" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Historique trocs</span>
                        </a>
                    </li>
                    @endif

                    @if($canReturns)
                    <li>
                        <a href="{{ route('returns.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('returns.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('returns.*'),
                        ])>
                            <i data-lucide="arrow-left-circle" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Retours clients</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif
            @endcanany

            <!-- STOCK & CATALOGUE -->
            @canany(['products.view', 'stock.view'])
            <div>
                <p class="px-2.5 mb-2 text-[10px] font-medium text-gray-500 uppercase tracking-widest">Stock</p>
                <ul class="space-y-1">
                    @can('products.view')
                    <li>
                        <a href="{{ route('products.index') }}" @class([
                            'flex items-center justify-between px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('products.*') && !request()->routeIs('product-models.*') && !request()->routeIs('products.supplier-returns'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('products.*') || request()->routeIs('product-models.*') || request()->routeIs('products.supplier-returns'),
                        ])>
                            <div class="flex items-center gap-3">
                                <i data-lucide="smartphone" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                                <span>Stock principal</span>
                            </div>
                            @php $stockCount = \App\Models\Product::where(fn($q) => $q->where('condition', '!=', 'troc')->orWhereNull('condition'))->whereIn('state', [\App\Enums\ProductState::DISPONIBLE->value, \App\Enums\ProductState::REPARE->value])->where('location', \App\Enums\ProductLocation::BOUTIQUE->value)->count(); @endphp
                            @if($stockCount > 0)
                                <span class="flex items-center justify-center px-1.5 h-5 min-w-[1.25rem] text-[10px] font-medium text-gray-300 bg-white/5 rounded-full border border-white/5">
                                    {{ $stockCount > 99 ? '99+' : $stockCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['condition' => 'troc']) }}" @class([
                            'flex items-center justify-between px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('products.index') && request()->get('condition') === 'troc',
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !(request()->routeIs('products.index') && request()->get('condition') === 'troc'),
                        ])>
                            <div class="flex items-center gap-3">
                                <i data-lucide="refresh-cw" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                                <span>Téléphones en troc</span>
                            </div>
                            @php $trocCount = \App\Models\Product::where('condition', 'troc')->whereNull('deleted_at')->count(); @endphp
                            @if($trocCount > 0)
                                <span class="flex items-center justify-center px-1.5 h-5 min-w-[1.25rem] text-[10px] font-medium text-purple-300 bg-purple-500/10 rounded-full border border-purple-500/20">
                                    {{ $trocCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                    @endcan

                    @can('viewAny', App\Models\ProductModel::class)
                    <li>
                        <a href="{{ route('product-models.index') }}" wire:navigate @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('product-models.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('product-models.*'),
                        ])>
                            <i data-lucide="layers" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Catalogue Modèles</span>
                        </a>
                    </li>
                    @endcan

                    @can('stock.view')
                    <li>
                        <a href="{{ route('stock-movements.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('stock-movements.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('stock-movements.*'),
                        ])>
                            <i data-lucide="activity" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Historique stock</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.supplier-returns') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('products.supplier-returns'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('products.supplier-returns'),
                        ])>
                            <i data-lucide="truck" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Retours fournisseurs</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            <!-- RÉSEAU -->
            @can('resellers.manage')
            <div>
                <p class="px-2.5 mb-2 text-[10px] font-medium text-gray-500 uppercase tracking-widest">Réseau</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('resellers.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('resellers.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('resellers.*'),
                        ])>
                            <i data-lucide="users" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Partenaires revendeurs</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            <!-- PILOTAGE -->
            <div>
                <p class="px-2.5 mb-2 text-[10px] font-medium text-gray-500 uppercase tracking-widest">Pilotage</p>
                <ul class="space-y-1">
                    @if(auth()->user()->isVendeur())
                    <li>
                        <a href="{{ route('reports.daily') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('reports.daily'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('reports.daily'),
                        ])>
                            <i data-lucide="calendar" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Mes ventes</span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('reports.daily') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('reports.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('reports.*'),
                        ])>
                            <i data-lucide="bar-chart-3" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Statistiques</span>
                        </a>
                    </li>
                    @endif

                    @can('users.view')
                    <li>
                        <a href="{{ route('users.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('users.*'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('users.*'),
                        ])>
                            <i data-lucide="user-cog" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('activity-logs.index') }}" @class([
                            'flex items-center gap-3 px-2.5 py-2 rounded-md text-sm font-medium transition-colors duration-150',
                            'bg-white/10 text-white' => request()->routeIs('activity-logs.index'),
                            'text-gray-400 hover:text-gray-100 hover:bg-white/[0.04]' => !request()->routeIs('activity-logs.index'),
                        ])>
                            <i data-lucide="shield-check" class="w-4 h-4 flex-shrink-0" stroke-width="1.5"></i>
                            <span>Journal d'activité</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>

        </nav>

        <!-- User Profile Area -->
        <div class="border-t border-white/[0.08] p-3 flex-shrink-0">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-2.5 py-2 rounded-md hover:bg-white/[0.04] transition-colors duration-150 group">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-gray-700 to-gray-600 flex items-center justify-center flex-shrink-0 border border-white/10">
                    <span class="text-white font-medium text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate capitalize">{{ auth()->user()->primary_role }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-500 group-hover:text-gray-300 transition-colors flex-shrink-0" stroke-width="1.5"></i>
            </a>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

</div>