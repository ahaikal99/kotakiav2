@extends('layouts.dashboard', ['title' => 'Sejarah Bayaran'])

@section('content')
    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Rujukan</th><th>Kad</th><th>Pakej</th><th>Jumlah</th><th>Status</th><th>Tarikh</th></tr></thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($payments as $p)
                    <tr>
                        <td class="font-mono text-xs">{{ $p->reference }}</td>
                        <td><a href="{{ route('invitations.show', $p->invitation_id) }}" class="text-gold-700 hover:underline">{{ $p->invitation?->coupleName() }}</a></td>
                        <td>{{ $p->package->name }}</td>
                        <td>RM{{ number_format($p->amount, 2) }}</td>
                        <td><span class="badge bg-{{ $p->statusColor() }}-100 text-{{ $p->statusColor() }}-700">{{ $p->statusLabel() }}</span></td>
                        <td class="text-stone-500">{{ ($p->paid_at ?? $p->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-stone-400">Tiada rekod bayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($payments->hasPages())<div class="border-t border-stone-100 p-4">{{ $payments->links() }}</div>@endif
    </div>
@endsection
