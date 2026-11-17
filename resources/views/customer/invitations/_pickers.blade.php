{{-- Pemilih pakej & tema. Pemboleh ubah: $packages, $themes, $selectedPackage, $selectedTheme, $showPackages --}}
@if ($showPackages)
    <div>
        <p class="label">Pakej</p>
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ($packages as $p)
                <label class="relative cursor-pointer rounded-2xl border border-stone-200 bg-white p-4 transition has-[:checked]:border-gold-500 has-[:checked]:ring-2 has-[:checked]:ring-gold-200">
                    <input type="radio" name="package_id" value="{{ $p->id }}" class="peer sr-only" data-premium-themes="{{ $p->premium_themes ? 1 : 0 }}" @checked(old('package_id', $selectedPackage) == $p->id)>
                    @if ($p->is_popular)<span class="badge absolute top-3 right-3 bg-gold-100 text-gold-700">Popular</span>@endif
                    <p class="font-serif text-lg font-semibold text-ink">{{ $p->name }}</p>
                    <p class="font-semibold text-gold-700">RM{{ number_format($p->price, 2) }}</p>
                    <ul class="mt-2 space-y-0.5 text-xs text-stone-500">
                        @foreach (array_slice(array_filter($p->featureList(), fn ($f) => $f[1]), 2, 5) as [$label])
                            <li class="flex gap-1.5"><x-icon name="check" class="size-3.5 text-gold-500" /> {{ $label }}</li>
                        @endforeach
                    </ul>
                </label>
            @endforeach
        </div>
    </div>
@endif

<div>
    <p class="label">Tema Kad</p>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($themes as $t)
            <label class="group relative cursor-pointer overflow-hidden rounded-2xl border border-stone-200 bg-white transition has-[:checked]:border-gold-500 has-[:checked]:ring-2 has-[:checked]:ring-gold-200" data-theme-card data-premium="{{ $t->is_premium ? 1 : 0 }}">
                <input type="radio" name="theme_id" value="{{ $t->id }}" class="sr-only" @checked(old('theme_id', $selectedTheme) == $t->id)>
                <div class="flex h-24 flex-col items-center justify-center" style="background: {{ $t->color('bg') }}">
                    <span class="font-script text-2xl" style="color: {{ $t->color('primary') }}">A & H</span>
                </div>
                <div class="flex items-center justify-between gap-2 p-2.5">
                    <span class="truncate text-sm font-medium text-stone-700">{{ $t->name }}</span>
                    @if ($t->is_premium)<span class="badge bg-ink text-gold-200">Premium</span>@endif
                </div>
                <a href="{{ route('theme.demo', $t) }}" target="_blank" class="absolute top-2 right-2 rounded-full bg-white/90 px-2 py-0.5 text-[11px] text-stone-600 opacity-0 shadow transition group-hover:opacity-100">Contoh</a>
            </label>
        @endforeach
    </div>
    <p class="hint">Tema premium hanya untuk pakej Premium & ke atas. Tema boleh ditukar pada bila-bila masa.</p>
</div>
