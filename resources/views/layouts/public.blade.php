<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · ' : '' }}eCard Kotakia — Kad Jemputan Digital</title>
    <meta name="description" content="Kad jemputan perkahwinan digital dengan RSVP, lagu latar, galeri & video. Bayar online, terus dapat pautan untuk dikongsi.">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Great+Vibes&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans">
    <header class="sticky top-0 z-40 border-b border-gold-100/70 bg-cream/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Kotakia" class="size-9">
                <span class="leading-tight">
                    <span class="block font-serif text-xl font-semibold tracking-wide text-ink">Kotakia</span>
                    <span class="-mt-0.5 block text-[10px] tracking-[0.25em] text-gold-600 uppercase">eCard Jemputan</span>
                </span>
            </a>
            <nav class="hidden items-center gap-7 text-sm text-stone-600 md:flex">
                <a href="{{ route('home') }}#ciri" class="hover:text-gold-700">Ciri-ciri</a>
                <a href="{{ route('home') }}#tema" class="hover:text-gold-700">Tema</a>
                <a href="{{ route('home') }}#pakej" class="hover:text-gold-700">Pakej</a>
                <a href="{{ route('home') }}#soalan" class="hover:text-gold-700">Soalan Lazim</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm">Log Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-gold-100 bg-ink text-stone-400">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="size-9">
                    <span class="font-serif text-xl text-gold-200">eCard Kotakia</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed">Kad jemputan perkahwinan digital yang elegan, mesra alam & mudah dikongsi.</p>
            </div>
            <div class="text-sm">
                <p class="mb-2 font-medium text-stone-200">Pautan</p>
                <ul class="space-y-1.5">
                    <li><a href="{{ route('home') }}#pakej" class="hover:text-gold-300">Pakej & Harga</a></li>
                    <li><a href="{{ route('home') }}#tema" class="hover:text-gold-300">Tema Kad</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-gold-300">Cipta Kad</a></li>
                </ul>
            </div>
            <div class="text-sm">
                <p class="mb-2 font-medium text-stone-200">Bayaran Selamat</p>
                <p>FPX & kad debit/kredit melalui ToyyibPay.</p>
            </div>
        </div>
        <div class="border-t border-white/5 py-5 text-center text-xs">&copy; {{ date('Y') }} eCard Kotakia. Hak cipta terpelihara.</div>
    </footer>
</body>
</html>
