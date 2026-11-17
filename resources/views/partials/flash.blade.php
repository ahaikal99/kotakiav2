@php
    $flashes = [
        'success' => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'check-circle'],
        'error' => ['bg-rose-50 border-rose-200 text-rose-800', 'x'],
        'info' => ['bg-sky-50 border-sky-200 text-sky-800', 'sparkles'],
    ];
@endphp
@foreach ($flashes as $key => [$classes, $icon])
    @if (session($key))
        <div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $classes }}">
            <x-icon :name="$icon" class="mt-0.5 size-5 shrink-0" />
            <span>{{ session($key) }}</span>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-medium">Sila semak semula borang:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
