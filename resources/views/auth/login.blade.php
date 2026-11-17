@extends('layouts.public', ['title' => 'Log Masuk'])

@section('content')
    <div class="pattern-geo flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-12">
        <div class="card w-full max-w-md p-8">
            <div class="text-center">
                <img src="{{ asset('images/logo.png') }}" class="mx-auto size-14" alt="">
                <h1 class="mt-3 font-serif text-3xl font-semibold text-ink">Selamat Kembali</h1>
                <p class="text-sm text-stone-500">Log masuk untuk mengurus kad jemputan anda</p>
            </div>
            <div class="mt-6">@include('partials.flash')</div>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="email">E-mel</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="email">
                </div>
                <div>
                    <label class="label" for="password">Kata Laluan</label>
                    <input id="password" name="password" type="password" class="input" required autocomplete="current-password">
                </div>
                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="remember" value="1" class="rounded accent-gold-500"> Ingat saya
                </label>
                <button class="btn-primary w-full">Log Masuk</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-500">Belum ada akaun? <a href="{{ route('register') }}" class="font-medium text-gold-700 hover:underline">Daftar sekarang</a></p>
        </div>
    </div>
@endsection
