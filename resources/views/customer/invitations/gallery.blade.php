@extends('layouts.dashboard', ['title' => 'Galeri · '.$invitation->coupleName()])

@section('content')
    @include('customer.invitations._tabs', ['active' => 'gallery'])

    @if (! $invitation->package->has_gallery)
        <div class="card flex flex-col items-center p-12 text-center">
            <x-icon name="lock" class="size-10 text-gold-500" />
            <h2 class="mt-3 font-serif text-2xl font-semibold text-ink">Galeri tidak termasuk dalam pakej {{ $invitation->package->name }}</h2>
            @if ($invitation->isDraft())
                <p class="mt-1 text-sm text-stone-500">Tukar ke pakej Premium atau Eksklusif sebelum membuat bayaran.</p>
                <a href="{{ route('invitations.edit', $invitation) }}#tema" class="btn-primary mt-5">Tukar Pakej</a>
            @endif
        </div>
    @else
        @php $used = $invitation->gallery->count(); $max = $invitation->package->max_gallery; @endphp
        <div class="card mb-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="section-title">Muat Naik Gambar</h2>
                    <p class="text-sm text-stone-500">{{ $used }} / {{ $max }} gambar digunakan · JPG/PNG/WebP, maks 4MB setiap satu</p>
                </div>
                <div class="h-2 w-40 overflow-hidden rounded-full bg-stone-100"><div class="h-full bg-gold-500" style="width: {{ min(100, $used / max($max, 1) * 100) }}%"></div></div>
            </div>
            @if ($used < $max)
                <form method="POST" action="{{ route('invitations.gallery.store', $invitation) }}" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gold-200 bg-gold-50/40 px-6 py-10 text-center hover:bg-gold-50">
                        <x-icon name="photo" class="size-10 text-gold-500" />
                        <span class="mt-2 font-medium text-stone-700">Klik untuk pilih gambar</span>
                        <span class="text-xs text-stone-500">Boleh pilih beberapa gambar serentak (baki {{ $max - $used }})</span>
                        <span id="picked" class="mt-2 text-xs text-gold-700"></span>
                        <input type="file" name="images[]" accept="image/*" multiple class="sr-only" data-preview="#picked" required>
                    </label>
                    <div class="mt-4 text-right"><button class="btn-primary">Muat Naik</button></div>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($invitation->gallery as $img)
                <div class="group relative overflow-hidden rounded-2xl bg-stone-100">
                    <img src="{{ $img->url() }}" class="aspect-square w-full object-cover" alt="" loading="lazy">
                    <form method="POST" action="{{ route('invitations.gallery.destroy', [$invitation, $img]) }}" data-confirm="Padam gambar ini?"
                          class="absolute top-2 right-2 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100">
                        @csrf @method('DELETE')
                        <button class="flex size-8 items-center justify-center rounded-full bg-white/90 text-rose-600 shadow"><x-icon name="trash" class="size-4" /></button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
