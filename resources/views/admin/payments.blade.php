@extends('layouts.dashboard', ['title' => 'Bayaran'])

@section('content')
    <p class="mb-4 text-sm text-stone-500">Jumlah kutipan berjaya: <strong class="text-ink">RM{{ number_format($total, 2) }}</strong></p>
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-stone-100 p-4">
            <input name="q" value="{{ request('q') }}" class="input min-w-48 flex-1" placeholder="Cari rujukan, bil, nama atau e-mel">
            <select name="status" class="input w-auto">
                <option value="">Semua status</option>
                @foreach (\App\Models\Payment::STATUS_LABELS as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn-dark btn-sm">Tapis</button>
        </form>
        <div class="overflow-x-auto">@include('admin._payments-table')</div>
        @if ($payments->hasPages())<div class="border-t border-stone-100 p-4">{{ $payments->links() }}</div>@endif
    </div>
@endsection
