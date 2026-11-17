<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}eCard Kotakia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Great+Vibes&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $isAdmin = auth()->user()->isAdmin();
    $inAdmin = request()->routeIs('admin.*');
    $nav = $inAdmin
        ? [
            ['admin.dashboard', 'Ringkasan', 'chart', 'admin.dashboard'],
            ['admin.payments.index', 'Bayaran', 'cash', 'admin.payments.*'],
            ['admin.invitations.index', 'Kad Jemputan', 'card', 'admin.invitations.*'],
            ['admin.users.index', 'Pelanggan', 'users', 'admin.users.*'],
            ['admin.packages.index', 'Pakej', 'tag', 'admin.packages.*'],
            ['admin.themes.index', 'Tema', 'swatch', 'admin.themes.*'],
        ]
        : [
            ['dashboard', 'Dashboard', 'home', 'dashboard'],
            ['invitations.create', 'Cipta Kad Baru', 'plus', 'invitations.create'],
            ['payments.index', 'Sejarah Bayaran', 'cash', 'payments.index'],
            ['profile.edit', 'Profil', 'user', 'profile.*'],
        ];
@endphp
<body class="min-h-screen font-sans">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 hidden w-64 flex-col bg-ink lg:flex">
            <div class="flex h-16 items-center gap-2.5 border-b border-white/5 px-5">
                <img src="{{ asset('images/logo.png') }}" alt="" class="size-8">
                <div class="leading-tight">
                    <span class="block font-serif text-lg text-gold-200">Kotakia</span>
                    <span class="block text-[10px] tracking-[0.2em] text-stone-500 uppercase">{{ $inAdmin ? 'Panel Admin' : 'Ruang Pengantin' }}</span>
                </div>
                <button class="ml-auto text-stone-400 lg:hidden" data-toggle="#sidebar"><x-icon name="x" /></button>
            </div>
            <nav class="flex-1 space-y-1 p-3">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                        <x-icon :name="$icon" /> {{ $label }}
                    </a>
                @endforeach
                @if ($isAdmin)
                    <div class="my-3 border-t border-white/5"></div>
                    @if ($inAdmin)
                        <a href="{{ route('dashboard') }}" class="nav-link"><x-icon name="home" /> Paparan Pelanggan</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="nav-link"><x-icon name="cog" /> Panel Admin</a>
                    @endif
                @endif
                <a href="{{ route('home') }}" class="nav-link" target="_blank"><x-icon name="external" /> Laman Utama</a>
            </nav>
            <div class="border-t border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-full bg-gold-500/20 font-medium text-gold-200">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-stone-200">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-stone-500">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-stone-500 hover:text-gold-300" title="Log keluar"><x-icon name="logout" /></button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-stone-200/70 bg-cream/90 px-4 backdrop-blur sm:px-8">
                <button class="text-stone-600 lg:hidden" data-toggle="#sidebar"><x-icon name="menu" class="size-6" /></button>
                <h1 class="truncate font-serif text-2xl font-semibold text-ink">{{ $title ?? 'Dashboard' }}</h1>
                <div class="ml-auto flex items-center gap-2">@yield('actions')</div>
            </header>
            <main class="flex-1 px-4 py-6 sm:px-8">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
    <style>@media (max-width: 1023px) { #sidebar:not(.hidden) { display: flex; } }</style>
</body>
</html>
