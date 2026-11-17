@extends('layouts.dashboard', ['title' => 'RSVP · '.$invitation->coupleName()])

@section('actions')
    <a href="{{ route('invitations.rsvp.export', $invitation) }}" class="btn-outline btn-sm"><x-icon name="download" class="size-4" /> <span class="hidden sm:inline">Eksport CSV</span></a>
@endsection

@section('content')
    @include('customer.invitations._tabs', ['active' => 'rsvp'])

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Jumlah Respons', $stats['responses'], 'stone'],
            ['Hadir', $stats['attending'], 'emerald'],
            ['Tidak Hadir', $stats['not_attending'], 'rose'],
            ['Jumlah Pax Hadir', $stats['pax'], 'amber'],
        ] as [$label, $val, $color])
            <div class="card border-l-4 border-l-{{ $color }}-500 p-4">
                <p class="text-xs text-stone-500">{{ $label }}</p>
                <p class="font-serif text-3xl font-semibold text-ink">{{ number_format($val) }}</p>
            </div>
        @endforeach
    </div>

    @if ($invitation->package->max_rsvp)
        <p class="mb-4 text-sm text-stone-500">Had pakej: {{ $stats['responses'] }} / {{ $invitation->package->max_rsvp }} respons.</p>
    @endif

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-stone-100 p-4">
            <div class="relative min-w-48 flex-1">
                <x-icon name="search" class="absolute top-3 left-3 size-4 text-stone-400" />
                <input name="q" value="{{ request('q') }}" class="input pl-9" placeholder="Cari nama / telefon">
            </div>
            <select name="status" class="input w-auto" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                <option value="tidak" @selected(request('status') === 'tidak')>Tidak Hadir</option>
            </select>
            <button class="btn-dark btn-sm">Tapis</button>
        </form>
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Nama</th><th>Telefon</th><th>Kehadiran</th><th>Pax</th><th>Ucapan</th><th>Tarikh</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($rsvps as $r)
                        <tr>
                            <td class="font-medium text-ink">{{ $r->name }}</td>
                            <td>
                                @if ($r->phone)
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '60', preg_replace('/\D/', '', $r->phone)) }}" target="_blank" class="text-gold-700 hover:underline">{{ $r->phone }}</a>
                                @else
                                    <span class="text-stone-300">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $r->attending() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $r->attending() ? 'Hadir' : 'Tidak Hadir' }}</span>
                            </td>
                            <td>{{ $r->attending() ? $r->pax : '-' }}</td>
                            <td class="max-w-xs">
                                @if ($r->wish)
                                    <p class="line-clamp-2 {{ $r->wish_visible ? '' : 'text-stone-400 line-through' }}" title="{{ $r->wish }}">{{ $r->wish }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-xs text-stone-500">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap text-right">
                                @if ($r->wish)
                                    <form method="POST" action="{{ route('invitations.rsvp.toggle', [$invitation, $r]) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="btn-ghost btn-sm px-2" title="{{ $r->wish_visible ? 'Sembunyikan ucapan' : 'Paparkan ucapan' }}">
                                            <x-icon :name="$r->wish_visible ? 'eye-slash' : 'eye'" class="size-4" />
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('invitations.rsvp.destroy', [$invitation, $r]) }}" class="inline" data-confirm="Padam respons daripada {{ $r->name }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn-ghost btn-sm px-2 text-rose-600" title="Padam"><x-icon name="trash" class="size-4" /></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-stone-400">Tiada respons RSVP lagi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rsvps->hasPages())
            <div class="border-t border-stone-100 p-4">{{ $rsvps->links() }}</div>
        @endif
    </div>
@endsection
