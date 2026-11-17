@extends('layouts.dashboard', ['title' => 'Pakej'])

@section('actions')
    <a href="{{ route('admin.packages.create') }}" class="btn-primary btn-sm"><x-icon name="plus" class="size-4" /> Tambah Pakej</a>
@endsection

@section('content')
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($packages as $p)
            <div class="card p-6 {{ $p->is_active ? '' : 'opacity-60' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-serif text-2xl font-semibold text-ink">{{ $p->name }}</h3>
                        <p class="text-sm text-stone-500">{{ $p->tagline }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if ($p->is_popular)<span class="badge bg-gold-100 text-gold-700">Popular</span>@endif
                        @unless ($p->is_active)<span class="badge bg-stone-100 text-stone-500">Tidak aktif</span>@endunless
                    </div>
                </div>
                <p class="mt-3 font-serif text-4xl font-semibold text-gold-700">RM{{ number_format($p->price, 2) }}</p>
                <ul class="mt-4 space-y-1 text-sm">
                    @foreach ($p->featureList() as [$label, $has])
                        <li class="flex gap-2 {{ $has ? 'text-stone-700' : 'text-stone-300 line-through' }}"><x-icon :name="$has ? 'check' : 'x'" class="size-4 shrink-0" /> {{ $label }}</li>
                    @endforeach
                </ul>
                <div class="mt-5 flex items-center justify-between border-t border-stone-100 pt-4">
                    <span class="text-xs text-stone-500">{{ $p->invitations_count }} kad menggunakan pakej ini</span>
                    <div class="flex gap-1">
                        <a href="{{ route('admin.packages.edit', $p) }}" class="btn-outline btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.packages.destroy', $p) }}" data-confirm="Padam pakej {{ $p->name }}?">
                            @csrf @method('DELETE')
                            <button class="btn-ghost btn-sm text-rose-600"><x-icon name="trash" class="size-4" /></button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
