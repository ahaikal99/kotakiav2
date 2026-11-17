@extends('layouts.dashboard', ['title' => $invitation->coupleName()])

@section('actions')
    <a href="{{ $invitation->isActive() ? $invitation->url() : route('invitations.preview', $invitation) }}" target="_blank" class="btn-outline btn-sm">
        <x-icon name="eye" class="size-4" /> <span class="hidden sm:inline">{{ $invitation->isActive() ? 'Buka Kad' : 'Pratonton' }}</span>
    </a>
@endsection

@section('content')
    @include('customer.invitations._tabs', ['active' => 'show'])

    @if (session('paid'))
        <div class="relative mb-6 overflow-hidden rounded-3xl bg-ink p-8 text-center text-white">
            <img src="{{ asset('images/logo.png') }}" class="absolute -top-10 -left-10 size-40 opacity-10" alt="">
            <img src="{{ asset('images/logo.png') }}" class="absolute -right-10 -bottom-10 size-40 opacity-10" alt="">
            <p class="text-sm tracking-[0.3em] text-gold-400 uppercase">Pembayaran Berjaya</p>
            <h2 class="mt-2 font-script text-5xl text-gold-200">Tahniah, {{ $invitation->coupleName() }}!</h2>
            <p class="mx-auto mt-3 max-w-lg text-stone-300">Kad jemputan anda kini <strong class="text-gold-200">AKTIF</strong>. Salin pautan & ayat jemputan di bawah dan kongsikan dengan keluarga serta sahabat handai.</p>
        </div>
    @endif

    @if ($invitation->isActive())
        <div class="grid gap-6 xl:grid-cols-5">
            {{-- Pautan & ayat --}}
            <div class="card p-6 xl:col-span-3">
                <h2 class="section-title flex items-center gap-2"><x-icon name="share" class="size-5 text-gold-600" /> Kongsi Jemputan</h2>

                <label class="label mt-5">Pautan Kad</label>
                <div class="flex gap-2">
                    <input id="share-link" value="{{ $invitation->url() }}" class="input bg-gold-50 font-medium text-gold-900" readonly>
                    <button class="btn-dark btn-sm shrink-0" data-copy="#share-link"><x-icon name="clipboard" class="size-4" /> Salin</button>
                </div>

                <div class="mt-5 rounded-2xl border border-dashed border-gold-300 bg-gold-50/50 p-4">
                    <label class="label" for="guest-name">Pautan peribadi untuk tetamu <span class="font-normal text-stone-400">(pilihan)</span></label>
                    <input id="guest-name" data-guest-name class="input" placeholder="Taip nama tetamu, cth. Pak Long Rahim & Keluarga">
                    <p class="hint">Kad akan memaparkan “Kepada: nama tetamu” dan ayat di bawah dikemas kini secara automatik.</p>
                </div>

                <label class="label mt-5" for="share-message">Ayat Jemputan</label>
                <textarea id="share-message" rows="14" class="input font-mono text-[13px] leading-relaxed" readonly>{{ $invitation->shareMessage() }}</textarea>
                <textarea id="share-template" class="hidden">{{ $invitation->shareMessage('__GUEST__') }}</textarea>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button class="btn-primary" data-copy="#share-message"><x-icon name="clipboard" class="size-4" /> Salin Ayat</button>
                    <a href="{{ $invitation->whatsappShareUrl() }}" target="_blank" data-wa-share class="btn bg-[#25D366] text-white hover:bg-[#1ebe5a]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2z"/></svg>
                        Kongsi ke WhatsApp
                    </a>
                    <a href="https://t.me/share/url?url={{ rawurlencode($invitation->url()) }}&text={{ rawurlencode($invitation->event_title.' '.$invitation->coupleName()) }}" target="_blank" class="btn bg-[#229ED9] text-white hover:bg-[#1b8cc2]">Telegram</a>
                </div>
            </div>

            {{-- Statistik --}}
            <div class="space-y-6 xl:col-span-2">
                <div class="grid grid-cols-2 gap-4">
                    @foreach ([
                        ['Paparan', $invitation->views, 'eye'],
                        ['Respons', $stats['responses'], 'check-circle'],
                        ['Hadir (pax)', $stats['pax'], 'users'],
                        ['Ucapan', $stats['wishes'], 'chat'],
                    ] as [$label, $val, $icon])
                        <div class="card p-4">
                            <p class="flex items-center gap-1.5 text-xs text-stone-500"><x-icon :name="$icon" class="size-4 text-gold-600" /> {{ $label }}</p>
                            <p class="mt-1 font-serif text-3xl font-semibold text-ink">{{ number_format($val) }}</p>
                        </div>
                    @endforeach
                </div>

                @php $total = max($stats['responses'], 1); @endphp
                <div class="card p-5">
                    <p class="mb-3 text-sm font-medium text-stone-700">Kehadiran</p>
                    <div class="flex h-3 overflow-hidden rounded-full bg-stone-100">
                        <div class="bg-emerald-500" style="width: {{ $stats['attending'] / $total * 100 }}%"></div>
                        <div class="bg-rose-400" style="width: {{ $stats['not_attending'] / $total * 100 }}%"></div>
                    </div>
                    <div class="mt-3 flex justify-between text-xs text-stone-500">
                        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-emerald-500"></span> Hadir: {{ $stats['attending'] }}</span>
                        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-rose-400"></span> Tidak hadir: {{ $stats['not_attending'] }}</span>
                    </div>
                </div>

                <div class="card">
                    <div class="flex items-center justify-between border-b border-stone-100 px-5 py-3">
                        <p class="text-sm font-medium text-stone-700">RSVP Terkini</p>
                        <a href="{{ route('invitations.rsvp.index', $invitation) }}" class="text-xs text-gold-700 hover:underline">Lihat semua →</a>
                    </div>
                    @forelse ($recent as $r)
                        <div class="flex items-center gap-3 px-5 py-3 text-sm">
                            <span class="size-2 shrink-0 rounded-full {{ $r->attending() ? 'bg-emerald-500' : 'bg-rose-400' }}"></span>
                            <span class="flex-1 truncate">{{ $r->name }}</span>
                            <span class="text-xs text-stone-400">{{ $r->attending() ? $r->pax.' pax' : 'Tidak' }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-stone-400">Belum ada respons. Kongsi pautan anda!</p>
                    @endforelse
                </div>

                <div class="card p-5 text-sm text-stone-600">
                    <p>Pakej <strong>{{ $invitation->package->name }}</strong> · Tema <strong>{{ $invitation->theme->name }}</strong></p>
                    <p class="mt-1">Aktif sehingga <strong>{{ $invitation->expires_at?->locale('ms')->translatedFormat('j F Y') }}</strong></p>
                </div>
            </div>
        </div>
    @else
        {{-- Draf / tidak aktif --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card p-6 lg:col-span-2">
                <span class="badge bg-{{ $invitation->statusColor() }}-100 text-{{ $invitation->statusColor() }}-700">{{ $invitation->statusLabel() }}</span>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-ink">{{ $invitation->event_title }} {{ $invitation->coupleName() }}</h2>
                <p class="text-stone-500">{{ $invitation->dateLabel() }} · {{ $invitation->timeLabel() }} · {{ $invitation->venue_name }}</p>

                @if ($invitation->isDraft())
                    <ol class="mt-6 space-y-3 text-sm">
                        <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs text-white">✓</span> Draf kad dicipta</li>
                        <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gold-500 text-xs text-white">2</span> <span>Lengkapkan butiran di <a href="{{ route('invitations.edit', $invitation) }}" class="text-gold-700 underline">Edit Butiran</a> & <a href="{{ route('invitations.gallery.index', $invitation) }}" class="text-gold-700 underline">Galeri</a></span></li>
                        <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gold-500 text-xs text-white">3</span> <span>Semak <a href="{{ route('invitations.preview', $invitation) }}" target="_blank" class="text-gold-700 underline">pratonton</a> kad</span></li>
                        <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-stone-300 text-xs text-white">4</span> Bayar — pautan & ayat jemputan dijana serta-merta</li>
                    </ol>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('invitations.preview', $invitation) }}" target="_blank" class="btn-outline"><x-icon name="eye" class="size-4" /> Pratonton</a>
                        <form method="POST" action="{{ route('invitations.destroy', $invitation) }}" data-confirm="Padam draf kad ini? Tindakan ini tidak boleh diundur.">
                            @csrf @method('DELETE')
                            <button class="btn-ghost text-rose-600"><x-icon name="trash" class="size-4" /> Padam Draf</button>
                        </form>
                    </div>
                @else
                    <p class="mt-6 rounded-xl bg-rose-50 p-4 text-sm text-rose-700">Kad ini tidak lagi boleh diakses oleh tetamu. Sila hubungi kami jika anda perlukan bantuan.</p>
                @endif
            </div>

            @if ($invitation->isDraft())
                <div class="card overflow-hidden">
                    <div class="bg-ink p-6 text-center text-white">
                        <p class="text-xs tracking-[0.25em] text-gold-400 uppercase">Pakej {{ $invitation->package->name }}</p>
                        <p class="mt-2 font-serif text-5xl font-semibold text-gold-100">RM{{ number_format($invitation->package->price, 2) }}</p>
                        <p class="text-xs text-stone-400">Bayaran sekali</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('invitations.checkout', $invitation) }}">
                            @csrf
                            <button class="btn-primary w-full py-3"><x-icon name="cash" class="size-4" /> Bayar & Aktifkan</button>
                        </form>
                        <p class="mt-3 text-center text-xs text-stone-500">FPX & kad melalui ToyyibPay.{{ config('kotakia.payment.gateway') === 'simulasi' ? ' (Mod simulasi aktif)' : '' }}</p>
                        <a href="{{ route('invitations.edit', $invitation) }}#tema" class="mt-2 block text-center text-xs text-gold-700 hover:underline">Tukar pakej</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($invitation->payments->isNotEmpty())
        <div class="card mt-6 overflow-x-auto">
            <p class="border-b border-stone-100 px-5 py-3 text-sm font-medium text-stone-700">Rekod Bayaran</p>
            <table class="table-base">
                <thead><tr><th>Rujukan</th><th>Jumlah</th><th>Status</th><th>Tarikh</th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($invitation->payments as $p)
                        <tr>
                            <td class="font-mono text-xs">{{ $p->reference }}</td>
                            <td>RM{{ number_format($p->amount, 2) }}</td>
                            <td><span class="badge bg-{{ $p->statusColor() }}-100 text-{{ $p->statusColor() }}-700">{{ $p->statusLabel() }}</span></td>
                            <td class="text-stone-500">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
