@extends('layouts.dashboard', ['title' => 'Tema'])

@section('content')
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($themes as $t)
            <form method="POST" action="{{ route('admin.themes.update', $t) }}" class="card overflow-hidden {{ $t->is_active ? '' : 'opacity-70' }}">
                @csrf @method('PATCH')
                <div class="flex h-32 flex-col items-center justify-center" style="background: {{ $t->color('bg') }}">
                    <span class="text-[10px] tracking-[0.3em] uppercase" style="color: {{ $t->color('muted') }}">Walimatulurus</span>
                    <span class="font-script text-3xl" style="color: {{ $t->color('primary') }}">Adam & Hawa</span>
                </div>
                <div class="space-y-3 p-5">
                    <div><label class="label">Nama</label><input name="name" value="{{ $t->name }}" class="input" required></div>
                    <div><label class="label">Penerangan</label><input name="description" value="{{ $t->description }}" class="input"></div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['primary' => 'Utama', 'accent' => 'Aksen', 'bg' => 'Latar'] as $key => $label)
                            <label class="text-xs text-stone-500">{{ $label }}
                                <input type="color" name="{{ $key }}" value="{{ $t->color($key) }}" class="mt-1 h-9 w-full cursor-pointer rounded-lg border border-stone-200">
                            </label>
                        @endforeach
                    </div>
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" class="accent-gold-500" @checked($t->is_active)> Aktif</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_premium" value="1" class="accent-gold-500" @checked($t->is_premium)> Premium</label>
                    </div>
                    <div class="flex items-center justify-between border-t border-stone-100 pt-3">
                        <a href="{{ route('theme.demo', $t) }}" target="_blank" class="text-xs text-gold-700 hover:underline">Lihat demo · {{ $t->invitations_count }} kad</a>
                        <button class="btn-primary btn-sm">Simpan</button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
@endsection
