@extends('layouts.dashboard', ['title' => 'Ringkasan Admin'])

@section('content')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Jumlah Hasil', 'RM'.number_format($stats['revenue'], 2), 'cash', $stats['orders'].' pesanan berjaya'],
            ['Hasil Bulan Ini', 'RM'.number_format($stats['revenue_month'], 2), 'chart', now()->locale('ms')->translatedFormat('F Y')],
            ['Kad Aktif', number_format($stats['active']), 'card', $stats['drafts'].' draf belum bayar'],
            ['Pelanggan', number_format($stats['customers']), 'users', number_format($stats['rsvps']).' RSVP direkod'],
        ] as [$label, $value, $icon, $sub])
            <div class="card p-5">
                <div class="flex items-center justify-between text-stone-500">
                    <span class="text-sm">{{ $label }}</span>
                    <span class="flex size-9 items-center justify-center rounded-full bg-gold-100 text-gold-700"><x-icon :name="$icon" class="size-4.5" /></span>
                </div>
                <p class="mt-2 font-serif text-3xl font-semibold text-ink">{{ $value }}</p>
                <p class="text-xs text-stone-400">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <h2 class="section-title">Hasil 6 Bulan Terakhir</h2>
            @php $peak = max($monthly->max('total'), 1); @endphp
            <div class="mt-6 flex h-56 items-end gap-3">
                @foreach ($monthly as $m)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-medium text-stone-600">{{ $m['total'] > 0 ? 'RM'.number_format($m['total']) : '' }}</span>
                        <div class="w-full rounded-t-lg bg-gradient-to-t from-gold-600 to-gold-300 transition-all" style="height: {{ max(2, $m['total'] / $peak * 170) }}px"></div>
                        <span class="text-xs text-stone-500">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <p class="border-b border-stone-100 px-5 py-4 font-serif text-xl font-semibold text-ink">Majlis Akan Datang</p>
            @forelse ($upcoming as $inv)
                <a href="{{ $inv->url() }}" target="_blank" class="flex items-center gap-3 px-5 py-3 hover:bg-gold-50/50">
                    <div class="w-12 shrink-0 rounded-xl bg-gold-100 py-1 text-center text-gold-800">
                        <p class="text-lg leading-tight font-semibold">{{ $inv->event_date->format('d') }}</p>
                        <p class="text-[10px] uppercase">{{ $inv->event_date->locale('ms')->translatedFormat('M') }}</p>
                    </div>
                    <div class="min-w-0 text-sm">
                        <p class="truncate font-medium text-ink">{{ $inv->coupleName() }}</p>
                        <p class="truncate text-xs text-stone-500">{{ $inv->venue_name }}</p>
                    </div>
                </a>
            @empty
                <p class="p-6 text-center text-sm text-stone-400">Tiada majlis akan datang.</p>
            @endforelse
        </div>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
            <p class="font-serif text-xl font-semibold text-ink">Bayaran Terkini</p>
            <a href="{{ route('admin.payments.index') }}" class="text-sm text-gold-700 hover:underline">Lihat semua →</a>
        </div>
        @include('admin._payments-table', ['payments' => $recentPayments])
    </div>
@endsection
