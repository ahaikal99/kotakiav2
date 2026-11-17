@extends('layouts.dashboard', ['title' => 'Kad Jemputan'])

@section('content')
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-stone-100 p-4">
            <input name="q" value="{{ request('q') }}" class="input min-w-48 flex-1" placeholder="Cari nama pengantin, pautan, atau pelanggan">
            <select name="status" class="input w-auto">
                <option value="">Semua status</option>
                @foreach (\App\Models\Invitation::STATUS_LABELS as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn-dark btn-sm">Tapis</button>
        </form>
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Pengantin</th><th>Pelanggan</th><th>Tarikh Majlis</th><th>Pakej / Tema</th><th>Paparan</th><th>RSVP</th><th>Status</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($invitations as $inv)
                        <tr>
                            <td>
                                <p class="font-medium text-ink">{{ $inv->coupleName() }}</p>
                                <a href="{{ $inv->url() }}" target="_blank" class="text-xs text-gold-700 hover:underline">/k/{{ $inv->slug }}</a>
                            </td>
                            <td>{{ $inv->user->name }}<br><span class="text-xs text-stone-400">{{ $inv->user->email }}</span></td>
                            <td class="whitespace-nowrap">{{ $inv->event_date->format('d/m/Y') }}</td>
                            <td>{{ $inv->package->name }}<br><span class="text-xs text-stone-400">{{ $inv->theme->name }}</span></td>
                            <td>{{ number_format($inv->views) }}</td>
                            <td>{{ $inv->rsvps_count }}</td>
                            <td><span class="badge bg-{{ $inv->statusColor() }}-100 text-{{ $inv->statusColor() }}-700">{{ $inv->statusLabel() }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('invitations.show', $inv) }}" class="btn-ghost btn-sm">Urus</a>
                                @if ($inv->status === 'active')
                                    <form method="POST" action="{{ route('admin.invitations.status', $inv) }}" class="inline" data-confirm="Gantung kad ini? Tetamu tidak akan dapat membukanya.">
                                        @csrf @method('PATCH') <input type="hidden" name="action" value="suspend">
                                        <button class="btn-ghost btn-sm text-rose-600">Gantung</button>
                                    </form>
                                @elseif ($inv->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.invitations.status', $inv) }}" class="inline">
                                        @csrf @method('PATCH') <input type="hidden" name="action" value="activate">
                                        <button class="btn-ghost btn-sm text-emerald-700">Aktifkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-10 text-center text-stone-400">Tiada kad.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invitations->hasPages())<div class="border-t border-stone-100 p-4">{{ $invitations->links() }}</div>@endif
    </div>
@endsection
