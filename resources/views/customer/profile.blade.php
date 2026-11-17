@extends('layouts.dashboard', ['title' => 'Profil'])

@section('content')
    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="section-title">Maklumat Akaun</h2>
            <div><label class="label">Nama</label><input name="name" value="{{ old('name', $user->name) }}" class="input" required></div>
            <div><label class="label">E-mel</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required></div>
            <div><label class="label">No. Telefon</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="input"></div>
            <button class="btn-primary">Simpan</button>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="section-title">Tukar Kata Laluan</h2>
            <div><label class="label">Kata Laluan Semasa</label><input type="password" name="current_password" class="input" required></div>
            <div><label class="label">Kata Laluan Baharu</label><input type="password" name="password" class="input" required></div>
            <div><label class="label">Sahkan Kata Laluan Baharu</label><input type="password" name="password_confirmation" class="input" required></div>
            <button class="btn-dark">Tukar Kata Laluan</button>
        </form>
    </div>
@endsection
