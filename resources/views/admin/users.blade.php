@extends('layouts.dashboard', ['title' => 'Pelanggan'])

@section('content')
    <div class="card">
        <form method="GET" class="flex gap-2 border-b border-stone-100 p-4">
            <input name="q" value="{{ request('q') }}" class="input flex-1" placeholder="Cari nama, e-mel atau telefon">
            <button class="btn-dark btn-sm">Cari</button>
        </form>
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Nama</th><th>E-mel</th><th>Telefon</th><th>Peranan</th><th>Kad</th><th>Jumlah Bayaran</th><th>Daftar</th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($users as $u)
                        <tr>
                            <td class="font-medium text-ink">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone ?: '—' }}</td>
                            <td><span class="badge {{ $u->isAdmin() ? 'bg-ink text-gold-200' : 'bg-stone-100 text-stone-600' }}">{{ $u->isAdmin() ? 'Admin' : 'Pelanggan' }}</span></td>
                            <td>{{ $u->invitations_count }}</td>
                            <td>RM{{ number_format($u->total_paid ?? 0, 2) }}</td>
                            <td class="text-xs text-stone-500">{{ $u->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-stone-400">Tiada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())<div class="border-t border-stone-100 p-4">{{ $users->links() }}</div>@endif
    </div>
@endsection
