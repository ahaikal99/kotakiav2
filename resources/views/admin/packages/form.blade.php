@extends('layouts.dashboard', ['title' => $package->exists ? 'Edit Pakej '.$package->name : 'Tambah Pakej'])

@section('content')
    <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="max-w-3xl space-y-6">
        @csrf
        @if ($package->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
            <h2 class="section-title">Maklumat Pakej</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Nama</label><input name="name" value="{{ old('name', $package->name) }}" class="input" required></div>
                <div><label class="label">Slug</label><input name="slug" value="{{ old('slug', $package->slug) }}" class="input" placeholder="auto"></div>
            </div>
            <div><label class="label">Tagline</label><input name="tagline" value="{{ old('tagline', $package->tagline) }}" class="input"></div>
            <div><label class="label">Penerangan</label><textarea name="description" rows="2" class="input">{{ old('description', $package->description) }}</textarea></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="label">Harga (RM)</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price', $package->price) }}" class="input" required></div>
                <div><label class="label">Harga Asal (RM)</label><input type="number" step="0.01" min="0" name="original_price" value="{{ old('original_price', $package->original_price) }}" class="input" placeholder="untuk potongan"></div>
                <div><label class="label">Susunan</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $package->sort_order) }}" class="input"></div>
            </div>
        </div>

        <div class="card space-y-4 p-6">
            <h2 class="section-title">Ciri & Had</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'has_rsvp' => 'RSVP', 'has_wishes' => 'Buku ucapan', 'has_countdown' => 'Kira detik', 'has_music' => 'Lagu latar',
                    'has_gallery' => 'Galeri foto', 'has_video' => 'Video', 'has_gift' => 'Salam kaut', 'premium_themes' => 'Tema premium',
                ] as $field => $label)
                    <label class="flex items-center gap-2.5 rounded-xl border border-stone-200 px-4 py-3 text-sm has-[:checked]:border-gold-400 has-[:checked]:bg-gold-50">
                        <input type="checkbox" name="{{ $field }}" value="1" class="accent-gold-500" @checked(old($field, $package->$field))> {{ $label }}
                    </label>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="label">Maks Gambar Galeri</label><input type="number" min="0" name="max_gallery" value="{{ old('max_gallery', $package->max_gallery ?? 0) }}" class="input" required></div>
                <div><label class="label">Had Respons RSVP</label><input type="number" min="1" name="max_rsvp" value="{{ old('max_rsvp', $package->max_rsvp) }}" class="input" placeholder="kosong = tanpa had"></div>
                <div><label class="label">Aktif (hari selepas majlis)</label><input type="number" min="1" name="active_days" value="{{ old('active_days', $package->active_days) }}" class="input" required></div>
            </div>
        </div>

        <div class="card flex flex-wrap gap-6 p-6 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" class="accent-gold-500" @checked(old('is_active', $package->is_active))> Aktif (dipaparkan kepada pelanggan)</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_popular" value="1" class="accent-gold-500" @checked(old('is_popular', $package->is_popular))> Tanda sebagai "Paling Popular"</label>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.packages.index') }}" class="btn-ghost">Batal</a>
            <button class="btn-primary">Simpan Pakej</button>
        </div>
    </form>
@endsection
