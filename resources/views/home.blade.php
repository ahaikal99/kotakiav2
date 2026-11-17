@extends('layouts.public')

@section('content')
    {{-- Hero --}}
    <section class="pattern-geo relative overflow-hidden">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 md:grid-cols-2 md:py-24">
            <div>
                <span class="badge bg-gold-100 text-gold-800"><x-icon name="sparkles" class="size-3.5" /> Kad jemputan perkahwinan digital</span>
                <h1 class="mt-5 font-serif text-5xl leading-[1.05] font-semibold text-ink md:text-6xl">
                    Jemputan indah,<br>
                    <span class="font-script text-6xl font-normal text-gold-600 md:text-7xl">sekali klik</span>
                </h1>
                <p class="mt-6 max-w-lg text-lg leading-relaxed text-stone-600">
                    Pilih tema, isi butiran majlis, bayar secara online — dan terus dapat <strong class="text-ink">pautan kad beserta ayat jemputan</strong>
                    untuk dikongsi di WhatsApp. Lengkap dengan RSVP, lagu latar, galeri & video pra-perkahwinan.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary px-7 py-3 text-base">Cipta Kad Sekarang <x-icon name="arrow-right" class="size-4" /></a>
                    @if ($themes->first())
                        <a href="{{ route('theme.demo', $themes->first()) }}" target="_blank" class="btn-outline px-7 py-3 text-base">Lihat Contoh</a>
                    @endif
                </div>
                <div class="mt-8 flex items-center gap-6 text-sm text-stone-500">
                    <span class="flex items-center gap-1.5"><x-icon name="check-circle" class="size-4 text-gold-600" /> Siap dalam 10 minit</span>
                    <span class="flex items-center gap-1.5"><x-icon name="check-circle" class="size-4 text-gold-600" /> Bayaran FPX</span>
                </div>
            </div>

            {{-- Mockup telefon --}}
            <div class="relative mx-auto">
                <div class="absolute -inset-10 rounded-full bg-gold-200/40 blur-3xl"></div>
                <div class="relative w-72 rounded-[2.6rem] border-[10px] border-ink bg-ink shadow-2xl shadow-gold-900/30">
                    <div class="overflow-hidden rounded-[2rem] bg-[#f8f3ea]">
                        <div class="relative flex h-[520px] flex-col items-center justify-center px-6 text-center">
                            <img src="{{ asset('images/logo.png') }}" class="absolute -top-14 -left-14 size-40 opacity-15" alt="">
                            <img src="{{ asset('images/logo.png') }}" class="absolute -right-14 -bottom-14 size-40 opacity-15" alt="">
                            <p class="text-[10px] tracking-[0.35em] text-gold-700 uppercase">Walimatulurus</p>
                            <p class="mt-4 font-script text-5xl text-gold-600">Adam</p>
                            <p class="font-serif text-2xl text-gold-700">&</p>
                            <p class="font-script text-5xl text-gold-600">Hawa</p>
                            <div class="mx-auto my-5 h-px w-24 bg-gold-300"></div>
                            <p class="font-serif text-lg text-stone-700">Sabtu, 12 Disember</p>
                            <p class="text-xs text-stone-500">Dewan Seri Kenanga, Kuala Lumpur</p>
                            <div class="mt-6 grid grid-cols-4 gap-2 text-center">
                                @foreach (['24' => 'Hari', '08' => 'Jam', '15' => 'Minit', '42' => 'Saat'] as $n => $l)
                                    <div class="rounded-lg bg-white px-2 py-1.5 shadow-sm"><p class="font-serif text-lg text-gold-700">{{ $n }}</p><p class="text-[9px] text-stone-500">{{ $l }}</p></div>
                                @endforeach
                            </div>
                            <span class="mt-6 rounded-full bg-gold-500 px-5 py-2 text-xs text-white">Sahkan Kehadiran</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="text-center">
                <p class="text-sm tracking-[0.25em] text-gold-600 uppercase">Mudah & Pantas</p>
                <h2 class="mt-2 font-serif text-4xl font-semibold text-ink">4 langkah ke kad impian</h2>
            </div>
            <div class="mt-12 grid gap-6 md:grid-cols-4">
                @foreach ([
                    ['Daftar akaun', 'Cipta akaun percuma dalam beberapa saat.', 'user'],
                    ['Pilih pakej & tema', 'Pilih rekaan yang sesuai dengan tema majlis anda.', 'swatch'],
                    ['Isi butiran', 'Nama, tarikh, lokasi, lagu, galeri & video.', 'pencil'],
                    ['Bayar & kongsi', 'Bayar online, terus dapat pautan + ayat jemputan.', 'share'],
                ] as $i => [$t, $d, $icon])
                    <div class="relative rounded-2xl border border-gold-100 bg-cream p-6">
                        <span class="absolute top-5 right-5 font-serif text-4xl text-gold-200">0{{ $i + 1 }}</span>
                        <div class="flex size-11 items-center justify-center rounded-xl bg-gold-500 text-white"><x-icon :name="$icon" /></div>
                        <h3 class="mt-4 font-serif text-xl font-semibold text-ink">{{ $t }}</h3>
                        <p class="mt-1 text-sm text-stone-600">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ciri --}}
    <section id="ciri" class="py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="text-center">
                <p class="text-sm tracking-[0.25em] text-gold-600 uppercase">Ciri-ciri</p>
                <h2 class="mt-2 font-serif text-4xl font-semibold text-ink">Semua yang majlis anda perlukan</h2>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['check-circle', 'RSVP Automatik', 'Tetamu sahkan kehadiran & bilangan pax terus dari kad. Eksport ke Excel bila-bila masa.'],
                    ['chat', 'Buku Ucapan', 'Kumpul doa dan ucapan tetamu, dipaparkan cantik di dalam kad.'],
                    ['music', 'Lagu Latar', 'Muat naik lagu kegemaran anda untuk dimainkan semasa kad dibuka.'],
                    ['photo', 'Galeri Foto', 'Kongsi momen indah sesi pra-perkahwinan anda.'],
                    ['video', 'Video Pra-Perkahwinan', 'Sisipkan video YouTube atau muat naik video anda sendiri.'],
                    ['map', 'Google Maps & Waze', 'Tetamu sampai ke lokasi tanpa sesat, satu klik sahaja.'],
                    ['calendar', 'Kira Detik & Kalendar', 'Kira detik ke hari bahagia dan simpan tarikh ke kalendar telefon.'],
                    ['gift', 'Salam Kaut', 'Paparkan maklumat akaun bank atau kod QR DuitNow.'],
                    ['chart', 'Dashboard Pemantauan', 'Pantau jumlah paparan, kehadiran & ucapan secara masa nyata.'],
                ] as [$icon, $t, $d])
                    <div class="card p-6 transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex size-10 items-center justify-center rounded-full bg-gold-100 text-gold-700"><x-icon :name="$icon" /></div>
                        <h3 class="mt-4 font-semibold text-ink">{{ $t }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-stone-600">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Tema --}}
    <section id="tema" class="bg-ink py-20 text-white">
        <div class="mx-auto max-w-6xl px-4">
            <div class="text-center">
                <p class="text-sm tracking-[0.25em] text-gold-400 uppercase">Koleksi Tema</p>
                <h2 class="mt-2 font-serif text-4xl font-semibold text-gold-100">Pilih rekaan kegemaran anda</h2>
                <p class="mt-2 text-stone-400">Klik untuk melihat contoh kad sebenar.</p>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($themes as $theme)
                    <a href="{{ route('theme.demo', $theme) }}" target="_blank" class="group overflow-hidden rounded-2xl border border-white/10 bg-white/5 transition hover:border-gold-400/50">
                        <div class="relative flex h-44 flex-col items-center justify-center" style="background: {{ $theme->color('bg') }}">
                            <div class="absolute inset-3 rounded-xl border" style="border-color: {{ $theme->color('primary') }}55"></div>
                            <span class="text-[10px] tracking-[0.3em] uppercase" style="color: {{ $theme->color('muted') }}">Walimatulurus</span>
                            <span class="mt-1 font-script text-4xl" style="color: {{ $theme->color('primary') }}">Adam & Hawa</span>
                            @if ($theme->is_premium)
                                <span class="badge absolute top-5 right-5 bg-gold-500 text-white">Premium</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <p class="font-medium text-gold-100">{{ $theme->name }}</p>
                                <p class="text-xs text-stone-400">{{ $theme->description }}</p>
                            </div>
                            <div class="flex -space-x-1.5">
                                @foreach (['primary', 'accent', 'bg'] as $c)
                                    <span class="size-5 rounded-full border-2 border-ink" style="background: {{ $theme->color($c) }}"></span>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pakej --}}
    <section id="pakej" class="pattern-geo py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="text-center">
                <p class="text-sm tracking-[0.25em] text-gold-600 uppercase">Pakej & Harga</p>
                <h2 class="mt-2 font-serif text-4xl font-semibold text-ink">Bayar sekali, tanpa caj tersembunyi</h2>
            </div>
            <div class="mt-12 grid items-start gap-6 {{ $packages->count() >= 3 ? 'md:grid-cols-3' : ($packages->count() == 2 ? 'md:grid-cols-2 max-w-3xl mx-auto' : 'max-w-sm mx-auto') }}">
                @foreach ($packages as $package)
                    <div class="relative rounded-3xl border p-7 {{ $package->is_popular ? 'border-gold-400 bg-ink text-white shadow-xl shadow-gold-900/20 md:-mt-4' : 'border-gold-100 bg-white' }}">
                        @if ($package->is_popular)
                            <span class="badge absolute -top-3 left-1/2 -translate-x-1/2 bg-gold-500 px-4 py-1 text-white">Paling Popular</span>
                        @endif
                        <h3 class="font-serif text-2xl font-semibold {{ $package->is_popular ? 'text-gold-200' : 'text-ink' }}">{{ $package->name }}</h3>
                        <p class="text-sm {{ $package->is_popular ? 'text-stone-400' : 'text-stone-500' }}">{{ $package->tagline }}</p>
                        <div class="mt-5 flex items-end gap-2">
                            <span class="font-serif text-5xl font-semibold {{ $package->is_popular ? 'text-white' : 'text-ink' }}">RM{{ number_format($package->price, 0) }}</span>
                            @if ($package->original_price > $package->price)
                                <span class="mb-1.5 text-sm text-stone-400 line-through">RM{{ number_format($package->original_price, 0) }}</span>
                            @endif
                        </div>
                        <ul class="mt-6 space-y-2.5 text-sm">
                            @foreach ($package->featureList() as [$label, $has])
                                <li class="flex items-start gap-2.5 {{ $has ? '' : 'opacity-40 line-through' }}">
                                    <x-icon :name="$has ? 'check' : 'x'" class="mt-0.5 size-4 shrink-0 {{ $has ? 'text-gold-500' : '' }}" />
                                    {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('invitations.create', ['pakej' => $package->slug]) }}"
                           class="{{ $package->is_popular ? 'btn-primary' : 'btn-outline' }} mt-7 w-full">Pilih {{ $package->name }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="soalan" class="bg-white py-20">
        <div class="mx-auto max-w-3xl px-4">
            <h2 class="text-center font-serif text-4xl font-semibold text-ink">Soalan Lazim</h2>
            <div class="mt-10 space-y-3">
                @foreach ([
                    ['Bilakah kad saya boleh dikongsi?', 'Sebaik sahaja pembayaran berjaya, kad anda diaktifkan secara automatik dan anda akan menerima pautan beserta ayat jemputan yang sedia untuk dikongsi.'],
                    ['Bolehkah saya ubah butiran selepas bayar?', 'Boleh. Anda boleh mengemas kini butiran majlis, lagu, galeri dan video pada bila-bila masa melalui dashboard.'],
                    ['Bagaimana tetamu membuat RSVP?', 'Tetamu hanya perlu membuka pautan kad dan mengisi borang RSVP ringkas. Semua respons dipaparkan di dashboard anda dan boleh dieksport ke Excel.'],
                    ['Boleh saya hantar pautan dengan nama tetamu?', 'Boleh! Gunakan penjana pautan peribadi di dashboard — kad akan memaparkan "Kepada: Nama Tetamu".'],
                    ['Berapa lama kad aktif?', 'Bergantung kepada pakej — dari 30 hari hingga setahun selepas tarikh majlis.'],
                ] as [$q, $a])
                    <details class="group rounded-2xl border border-gold-100 bg-cream p-5 open:bg-white open:shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center justify-between font-medium text-ink">
                            {{ $q }} <x-icon name="plus" class="size-4 text-gold-600 transition group-open:rotate-45" />
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-stone-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-gold-500 py-14 text-center text-white">
        <h2 class="font-serif text-4xl font-semibold">Sedia untuk berkongsi berita gembira?</h2>
        <a href="{{ route('register') }}" class="btn mt-6 bg-white px-8 py-3 text-base text-gold-800 hover:bg-gold-50">Cipta Kad Jemputan</a>
    </section>
@endsection
