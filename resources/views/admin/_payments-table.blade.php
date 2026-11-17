<table class="table-base">
    <thead><tr><th>Rujukan</th><th>Pelanggan</th><th>Kad</th><th>Pakej</th><th>Jumlah</th><th>Gateway</th><th>Status</th><th>Tarikh</th><th></th></tr></thead>
    <tbody class="divide-y divide-stone-100">
        @forelse ($payments as $p)
            <tr>
                <td class="font-mono text-xs">{{ $p->reference }}<br><span class="text-stone-400">{{ $p->gateway_bill_code }}</span></td>
                <td>{{ $p->user->name }}<br><span class="text-xs text-stone-400">{{ $p->user->email }}</span></td>
                <td>{{ $p->invitation?->coupleName() }}</td>
                <td>{{ $p->package->name }}</td>
                <td class="font-medium">RM{{ number_format($p->amount, 2) }}</td>
                <td class="text-xs uppercase">{{ $p->gateway }}</td>
                <td><span class="badge bg-{{ $p->statusColor() }}-100 text-{{ $p->statusColor() }}-700">{{ $p->statusLabel() }}</span></td>
                <td class="text-xs whitespace-nowrap text-stone-500">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-right">
                    @unless ($p->isPaid())
                        <form method="POST" action="{{ route('admin.payments.mark-paid', $p) }}" data-confirm="Sahkan bayaran {{ $p->reference }} secara manual dan aktifkan kad?">
                            @csrf @method('PATCH')
                            <button class="btn-outline btn-sm whitespace-nowrap">Sahkan</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="py-10 text-center text-stone-400">Tiada rekod.</td></tr>
        @endforelse
    </tbody>
</table>
