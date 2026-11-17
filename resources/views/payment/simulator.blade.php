@extends('layouts.public', ['title' => 'Simulasi Bayaran'])

@section('content')
    <div class="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-stone-100 px-4 py-12">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-xl">
            <div class="bg-gradient-to-r from-sky-600 to-indigo-600 p-6 text-white">
                <p class="text-xs tracking-widest uppercase opacity-80">Gerbang Pembayaran · Mod Simulasi</p>
                <p class="mt-1 text-2xl font-semibold">FPX Online Banking</p>
            </div>
            <div class="space-y-3 p-6 text-sm">
                <div class="flex justify-between"><span class="text-stone-500">Penjual</span><span class="font-medium">eCard Kotakia</span></div>
                <div class="flex justify-between"><span class="text-stone-500">Rujukan</span><span class="font-mono">{{ $payment->reference }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500">Item</span><span>Pakej {{ $payment->package->name }} — {{ $payment->invitation->coupleName() }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500">Pembayar</span><span>{{ $payment->user->name }}</span></div>
                <div class="flex justify-between border-t pt-3 text-lg"><span>Jumlah</span><span class="font-semibold">RM{{ number_format($payment->amount, 2) }}</span></div>
                <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">
                    Ini halaman simulasi untuk development. Tukar <code>PAYMENT_GATEWAY=toyyibpay</code> dalam <code>.env</code> untuk bayaran sebenar.
                </p>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ $failUrl }}" class="btn border border-stone-300 text-stone-700 hover:bg-stone-50">Gagalkan</a>
                    <a href="{{ $successUrl }}" class="btn bg-emerald-600 text-white hover:bg-emerald-700">Bayar Berjaya</a>
                </div>
            </div>
        </div>
    </div>
@endsection
