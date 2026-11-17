@php
    $tabs = [
        'show' => ['invitations.show', 'Ringkasan', 'chart'],
        'edit' => ['invitations.edit', 'Edit Butiran', 'pencil'],
        'gallery' => ['invitations.gallery.index', 'Galeri', 'photo'],
        'rsvp' => ['invitations.rsvp.index', 'RSVP & Ucapan', 'check-circle'],
    ];
@endphp
<div class="mb-6 flex gap-1 overflow-x-auto rounded-2xl border border-stone-200 bg-white p-1.5">
    @foreach ($tabs as $key => [$route, $label, $icon])
        <a href="{{ route($route, $invitation) }}"
           class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-sm transition {{ $active === $key ? 'bg-ink text-gold-100' : 'text-stone-600 hover:bg-stone-100' }}">
            <x-icon :name="$icon" class="size-4" /> {{ $label }}
        </a>
    @endforeach
</div>
