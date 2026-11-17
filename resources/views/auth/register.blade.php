@extends('layouts.public', ['title' => 'Daftar'])

@section('content')
    <div class="pattern-geo flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-12">
        <div class="card w-full max-w-md p-8">
            <div class="text-center">
                <img src="{{ asset('images/logo.png') }}" class="mx-auto size-14" alt="">
                <h1 class="mt-3 font-serif text-3xl font-semibold text-ink">Daftar Akaun</h1>
                <p class="text-sm text-stone-500">Mula cipta kad jemputan digital anda</p>
            </div>
            <div class="mt-6">@include('partials.flash')</div>
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="name">Nama Penuh</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="input" required autofocus>
                </div>
                <div>
                    <label class="label" for="email">E-mel</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required>
                </div>
                <div>
                    <label class="label" for="phone">No. Telefon</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="input" placeholder="012-345 6789" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="password">Kata Laluan</label>
                        <input id="password" name="password" type="password" class="input" required autocomplete="new-password">
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">Sahkan</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="input" required>
                    </div>
                </div>
                <p class="hint">Minimum 8 aksara.</p>
                <button class="btn-primary w-full">Daftar</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-500">Sudah ada akaun? <a href="{{ route('login') }}" class="font-medium text-gold-700 hover:underline">Log masuk</a></p>
        </div>
    </div>
@endsection
