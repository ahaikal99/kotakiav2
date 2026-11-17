@extends('layouts.dashboard', ['title' => 'Dashboard'])

@section('actions')
    <a href="{{ route('invitations.create') }}" class="btn-primary btn-sm"><x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">Cipta Kad</span></a>
@endsection

@section('content')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Kad Jemputan', $stats['total'], 'card', $stats['active'].' aktif'],
            ['Jumlah Paparan', number_format($stats['views']), 'eye', 'pembukaan kad'],
            ['Respons RSVP', number_format($stats['responses']), 'check-circle', 'semua kad'],
            ['Tetamu Hadir', number_format($stats['pax']), 'users', 'jumlah pax'],
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

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <h2 class="section-title mb-4">Kad Jemputan Saya</h2>
            @forelse ($invitations as $inv)
                <div class="card mb-4 flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                    <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl font-script text-2xl"
                         style="background: {{ $inv->theme->color('bg') }}; color: {{ $inv->theme->color('primary') }}">
                        {{ mb_substr($inv->groom_nickname, 0, 1) }}&{{ mb_substr($inv->bride_nickname, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-serif text-xl font-semibold text-ink">{{ $inv->coupleName() }}</h3>
                            <span class="badge bg-{{ $inv->statusColor() }}-100 text-{{ $inv->statusColor() }}-700">{{ $inv->statusLabel() }}</span>
                        </div>
                        <p class="text-sm text-stone-500">{{ $inv->dateLabel() }} · {{ $inv->venue_name }}</p>
                        <p class="mt-1 text-xs text-stone-400">Pakej {{ $inv->package->name }} · Tema {{ $inv->theme->name }} · {{ $inv->views }} paparan · {{ $inv->rsvps_count }} RSVP</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('invitations.show', $inv) }}" class="btn-primary btn-sm">Urus</a>
                        <a href="{{ route('invitations.edit', $inv) }}" class="btn-outline btn-sm">Edit</a>
                    </div>
                </div>
            @empty
                <div class="card flex flex-col items-center p-12 text-center">
                    <img src="{{ asset('images/logo.png') }}" class="size-16 opacity-60" alt="">
                    <h3 class="mt-4 font-serif text-2xl font-semibold text-ink">Belum ada kad jemputan</h3>
                    <p class="mt-1 text-sm text-stone-500">Mula cipta kad jemputan digital pertama anda.</p>
                    <a href="{{ route('invitations.create') }}" class="btn-primary mt-5"><x-icon name="plus" class="size-4" /> Cipta Kad</a>
                </div>
            @endforelse
        </div>

        <div>
            <h2 class="section-title mb-4">RSVP Terkini</h2>
            <div class="card divide-y divide-stone-100">
                @forelse ($latestRsvps as $r)
                    <div class="flex items-start gap-3 p-4">
                        <span class="mt-1 size-2.5 shrink-0 rounded-full {{ $r->attending() ? 'bg-emerald-500' : 'bg-rose-400' }}"></span>
                        <div class="min-w-0 text-sm">
                            <p class="font-medium text-ink">{{ $r->name }} <span class="font-normal text-stone-400">· {{ $r->attending() ? $r->pax.' pax' : 'tidak hadir' }}</span></p>
                            @if ($r->wish)<p class="line-clamp-2 text-stone-500">“{{ $r->wish }}”</p>@endif
                            <p class="text-xs text-stone-400">{{ $r->invitation->coupleName() }} · {{ $r->created_at->locale('ms')->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-stone-400">Belum ada respons.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
