@extends('layouts.dashboard', ['title' => 'Edit Kad · '.$invitation->coupleName()])

@php
    $pkg = $invitation->package;
    $schedule = old('schedule', $invitation->schedule ?? []);
    $contacts = old('contacts', $invitation->contacts ?? []);
    $gift = $invitation->gift ?? [];
    $locked = fn ($has) => $has ? '' : 'pointer-events-none opacity-50';
@endphp

@section('actions')
    <a href="{{ route('invitations.preview', $invitation) }}" target="_blank" class="btn-outline btn-sm"><x-icon name="eye" class="size-4" /> <span class="hidden sm:inline">Pratonton</span></a>
    <a href="{{ route('invitations.show', $invitation) }}" class="btn-dark btn-sm">Urus Kad</a>
@endsection

@section('content')
    @include('customer.invitations._tabs', ['active' => 'edit'])

    @if ($invitation->isDraft())
        <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-gold-200 bg-gold-50 p-5 sm:flex-row sm:items-center">
            <x-icon name="sparkles" class="size-6 shrink-0 text-gold-600" />
            <div class="flex-1 text-sm text-gold-900">
                <p class="font-medium">Kad ini masih draf.</p>
                <p>Lengkapkan butiran, semak pratonton, kemudian buat pembayaran <strong>RM{{ number_format($pkg->price, 2) }}</strong> untuk mendapatkan pautan jemputan.</p>
            </div>
            <form method="POST" action="{{ route('invitations.checkout', $invitation) }}">
                @csrf
                <button class="btn-primary whitespace-nowrap"><x-icon name="cash" class="size-4" /> Bayar & Aktifkan</button>
            </form>
        </div>
    @endif

    <form method="POST" action="{{ route('invitations.update', $invitation) }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1fr_220px]">
        @csrf @method('PUT')

        <div class="space-y-6">
            {{-- Pakej & tema --}}
            <div class="card space-y-6 p-6" id="tema">
                <h2 class="section-title">Pakej & Tema</h2>
                @if (! $invitation->isDraft())
                    <p class="rounded-xl bg-stone-50 px-4 py-3 text-sm text-stone-600">Pakej: <strong>{{ $pkg->name }}</strong> (telah dibayar)</p>
                @endif
                @include('customer.invitations._pickers', [
                    'showPackages' => $invitation->isDraft(),
                    'selectedPackage' => $invitation->package_id,
                    'selectedTheme' => $invitation->theme_id,
                ])
                @if ($invitation->isDraft())
                    <div>
                        <label class="label" for="slug">Pautan Kad</label>
                        <div class="flex items-center overflow-hidden rounded-xl border border-stone-300 bg-stone-50 focus-within:ring-2 focus-within:ring-gold-200">
                            <span class="pl-3.5 text-sm whitespace-nowrap text-stone-500">{{ url('/k') }}/</span>
                            <input id="slug" name="slug" value="{{ old('slug', $invitation->slug) }}" class="w-full bg-white px-2 py-2.5 text-sm focus:outline-none" required>
                        </div>
                        <p class="hint">Huruf kecil, nombor dan tanda sempang sahaja. Tidak boleh diubah selepas pembayaran.</p>
                    </div>
                @endif
            </div>

            {{-- Pengantin --}}
            <div class="card space-y-4 p-6" id="pengantin">
                <h2 class="section-title">Pengantin & Tuan Rumah</h2>
                <div>
                    <label class="label" for="event_title">Tajuk Majlis</label>
                    <input id="event_title" name="event_title" value="{{ old('event_title', $invitation->event_title) }}" class="input" list="titles" required>
                    <datalist id="titles"><option value="Walimatulurus"><option value="Majlis Perkahwinan"><option value="Majlis Bertandang"><option value="Majlis Resepsi"><option value="Majlis Pertunangan"></datalist>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label">Nama Panggilan Lelaki</label><input name="groom_nickname" value="{{ old('groom_nickname', $invitation->groom_nickname) }}" class="input" required></div>
                    <div><label class="label">Nama Panggilan Perempuan</label><input name="bride_nickname" value="{{ old('bride_nickname', $invitation->bride_nickname) }}" class="input" required></div>
                    <div><label class="label">Nama Penuh Lelaki</label><input name="groom_fullname" value="{{ old('groom_fullname', $invitation->groom_fullname) }}" class="input" placeholder="Muhammad Adam bin Ismail"></div>
                    <div><label class="label">Nama Penuh Perempuan</label><input name="bride_fullname" value="{{ old('bride_fullname', $invitation->bride_fullname) }}" class="input" placeholder="Nur Hawa binti Abdullah"></div>
                </div>
                <div>
                    <label class="label">Ibu Bapa / Tuan Rumah</label>
                    <input name="hosts" value="{{ old('hosts', $invitation->hosts) }}" class="input" placeholder="Encik Abdullah bin Hassan & Puan Siti Aminah binti Yusof">
                </div>
                <div>
                    <label class="label">Gambar Muka Depan</label>
                    @if ($invitation->cover_image)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $invitation->mediaUrl($invitation->cover_image) }}" class="h-20 w-16 rounded-lg object-cover" alt="">
                            <label class="flex items-center gap-2 text-sm text-rose-600"><input type="checkbox" name="remove_cover" value="1"> Buang gambar</label>
                        </div>
                    @endif
                    <input type="file" name="cover_image" accept="image/*" class="input file:mr-3 file:rounded-full file:border-0 file:bg-gold-100 file:px-3 file:py-1 file:text-gold-800">
                    <p class="hint">Gambar potret disyorkan (cth. 1080×1600). Maks 4MB.</p>
                </div>
            </div>

            {{-- Majlis --}}
            <div class="card space-y-4 p-6" id="majlis">
                <h2 class="section-title">Tarikh & Lokasi</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="label">Tarikh</label><input type="date" name="event_date" value="{{ old('event_date', $invitation->event_date->toDateString()) }}" class="input" required></div>
                    <div><label class="label">Masa Mula</label><input type="time" name="start_time" value="{{ old('start_time', substr($invitation->start_time, 0, 5)) }}" class="input" required></div>
                    <div><label class="label">Masa Tamat</label><input type="time" name="end_time" value="{{ old('end_time', $invitation->end_time ? substr($invitation->end_time, 0, 5) : '') }}" class="input"></div>
                </div>
                <div><label class="label">Nama Lokasi</label><input name="venue_name" value="{{ old('venue_name', $invitation->venue_name) }}" class="input" required></div>
                <div><label class="label">Alamat Penuh</label><textarea name="venue_address" rows="2" class="input">{{ old('venue_address', $invitation->venue_address) }}</textarea></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label">Pautan Google Maps</label><input type="url" name="maps_url" value="{{ old('maps_url', $invitation->maps_url) }}" class="input" placeholder="https://maps.app.goo.gl/..."></div>
                    <div><label class="label">Pautan Waze</label><input type="url" name="waze_url" value="{{ old('waze_url', $invitation->waze_url) }}" class="input" placeholder="https://waze.com/ul/..."></div>
                </div>
            </div>

            {{-- Kandungan --}}
            <div class="card space-y-4 p-6" id="kandungan">
                <h2 class="section-title">Ayat Jemputan & Doa</h2>
                <div><label class="label">Salam Pembuka</label><input name="greeting" value="{{ old('greeting', $invitation->greeting) }}" class="input"></div>
                <div><label class="label">Ayat Jemputan</label><textarea name="message" rows="4" class="input">{{ old('message', $invitation->message) }}</textarea></div>
                <div><label class="label">Doa</label><textarea name="doa" rows="3" class="input">{{ old('doa', $invitation->doa) }}</textarea></div>
            </div>

            {{-- Aturcara --}}
            <div class="card space-y-4 p-6" id="aturcara" data-repeater data-max="20">
                <h2 class="section-title">Aturcara Majlis</h2>
                <div class="space-y-2" data-rows>
                    @foreach ($schedule as $i => $row)
                        <div class="flex gap-2" data-row>
                            <input name="schedule[{{ $i }}][time]" value="{{ $row['time'] ?? '' }}" class="input w-32" placeholder="11:00 AM">
                            <input name="schedule[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" class="input" placeholder="Acara">
                            <button type="button" class="btn-ghost px-3" data-remove-row><x-icon name="trash" class="size-4" /></button>
                        </div>
                    @endforeach
                </div>
                <template>
                    <div class="flex gap-2" data-row>
                        <input name="schedule[__i__][time]" class="input w-32" placeholder="11:00 AM">
                        <input name="schedule[__i__][title]" class="input" placeholder="Acara">
                        <button type="button" class="btn-ghost px-3" data-remove-row><x-icon name="trash" class="size-4" /></button>
                    </div>
                </template>
                <button type="button" class="btn-outline btn-sm" data-add-row><x-icon name="plus" class="size-4" /> Tambah Acara</button>
            </div>

            {{-- Hubungi --}}
            <div class="card space-y-4 p-6" id="hubungi" data-repeater data-max="6">
                <div>
                    <h2 class="section-title">Untuk Dihubungi</h2>
                    <p class="text-sm text-stone-500">Tetamu boleh terus WhatsApp atau telefon. Maks 6 orang.</p>
                </div>
                <div class="space-y-2" data-rows>
                    @foreach ($contacts as $i => $c)
                        <div class="grid gap-2 sm:grid-cols-[1fr_140px_160px_auto]" data-row>
                            <input name="contacts[{{ $i }}][name]" value="{{ $c['name'] ?? '' }}" class="input" placeholder="Nama">
                            <input name="contacts[{{ $i }}][relation]" value="{{ $c['relation'] ?? '' }}" class="input" placeholder="Hubungan">
                            <input name="contacts[{{ $i }}][phone]" value="{{ $c['phone'] ?? '' }}" class="input" placeholder="012xxxxxxx">
                            <button type="button" class="btn-ghost px-3" data-remove-row><x-icon name="trash" class="size-4" /></button>
                        </div>
                    @endforeach
                </div>
                <template>
                    <div class="grid gap-2 sm:grid-cols-[1fr_140px_160px_auto]" data-row>
                        <input name="contacts[__i__][name]" class="input" placeholder="Nama">
                        <input name="contacts[__i__][relation]" class="input" placeholder="Hubungan (cth. Bapa)">
                        <input name="contacts[__i__][phone]" class="input" placeholder="012xxxxxxx">
                        <button type="button" class="btn-ghost px-3" data-remove-row><x-icon name="trash" class="size-4" /></button>
                    </div>
                </template>
                <button type="button" class="btn-outline btn-sm" data-add-row><x-icon name="plus" class="size-4" /> Tambah Orang</button>
            </div>

            {{-- RSVP --}}
            <div class="card space-y-4 p-6 {{ $locked($pkg->has_rsvp) }}" id="rsvp">
                <h2 class="section-title">RSVP</h2>
                <input type="hidden" name="rsvp_enabled" value="0">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="rsvp_enabled" value="1" class="accent-gold-500" @checked(old('rsvp_enabled', $invitation->rsvp_enabled))> Aktifkan borang RSVP</label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label">Tarikh Tutup RSVP</label><input type="date" name="rsvp_deadline" value="{{ old('rsvp_deadline', $invitation->rsvp_deadline?->toDateString()) }}" class="input"></div>
                    <div><label class="label">Had Tetamu Setiap RSVP</label><input type="number" min="1" max="20" name="max_pax_per_rsvp" value="{{ old('max_pax_per_rsvp', $invitation->max_pax_per_rsvp) }}" class="input" required></div>
                </div>
            </div>

            {{-- Media --}}
            <div class="card space-y-6 p-6" id="media">
                <h2 class="section-title">Lagu & Video</h2>

                <div class="{{ $locked($pkg->has_music) }}">
                    <p class="label flex items-center gap-2"><x-icon name="music" class="size-4 text-gold-600" /> Lagu Latar Belakang @unless ($pkg->has_music)<span class="badge bg-stone-100 text-stone-500"><x-icon name="lock" class="size-3" /> Pakej Premium</span>@endunless</p>
                    @if ($invitation->music_path)
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            <audio controls src="{{ $invitation->mediaUrl($invitation->music_path) }}" class="h-9"></audio>
                            <label class="flex items-center gap-2 text-sm text-rose-600"><input type="checkbox" name="remove_music" value="1"> Buang lagu</label>
                        </div>
                    @endif
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="file" name="music_file" accept="audio/*" class="input">
                        <input name="music_title" value="{{ old('music_title', $invitation->music_title) }}" class="input" placeholder="Tajuk lagu (pilihan)">
                    </div>
                    <p class="hint">MP3 / M4A, maks 15MB.</p>
                </div>

                <div class="{{ $locked($pkg->has_video) }}">
                    <p class="label flex items-center gap-2"><x-icon name="video" class="size-4 text-gold-600" /> Video Pra-Perkahwinan @unless ($pkg->has_video)<span class="badge bg-stone-100 text-stone-500"><x-icon name="lock" class="size-3" /> Pakej Eksklusif</span>@endunless</p>
                    <input type="url" name="video_url" value="{{ old('video_url', $invitation->video_url) }}" class="input" placeholder="Pautan YouTube — https://youtu.be/...">
                    <p class="my-2 text-center text-xs text-stone-400">— atau muat naik fail video —</p>
                    @if ($invitation->video_path)
                        <div class="mb-2 flex items-center gap-3 text-sm">
                            <a href="{{ $invitation->mediaUrl($invitation->video_path) }}" target="_blank" class="text-gold-700 underline">Lihat video semasa</a>
                            <label class="flex items-center gap-2 text-rose-600"><input type="checkbox" name="remove_video" value="1"> Buang video</label>
                        </div>
                    @endif
                    <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="input">
                    <p class="hint">MP4, maks 50MB. Pautan YouTube diutamakan jika kedua-duanya diisi.</p>
                </div>

                <div class="rounded-xl bg-stone-50 p-4 text-sm text-stone-600">
                    <x-icon name="photo" class="mr-1 inline size-4 text-gold-600" />
                    Galeri foto diurus di tab <a href="{{ route('invitations.gallery.index', $invitation) }}" class="font-medium text-gold-700 underline">Galeri</a>.
                </div>
            </div>

            {{-- Salam kaut --}}
            <div class="card space-y-4 p-6 {{ $locked($pkg->has_gift) }}" id="salam-kaut">
                <h2 class="section-title flex items-center gap-2">Salam Kaut @unless ($pkg->has_gift)<span class="badge bg-stone-100 font-sans text-xs text-stone-500"><x-icon name="lock" class="size-3" /> Pakej Premium</span>@endunless</h2>
                <p class="-mt-2 text-sm text-stone-500">Kosongkan nama bank untuk tidak memaparkan bahagian ini.</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="label">Bank</label><input name="gift[bank_name]" value="{{ old('gift.bank_name', $gift['bank_name'] ?? '') }}" class="input" placeholder="Maybank"></div>
                    <div><label class="label">Nama Pemegang</label><input name="gift[account_name]" value="{{ old('gift.account_name', $gift['account_name'] ?? '') }}" class="input"></div>
                    <div><label class="label">No. Akaun</label><input name="gift[account_no]" value="{{ old('gift.account_no', $gift['account_no'] ?? '') }}" class="input"></div>
                </div>
                <div>
                    <label class="label">Kod QR DuitNow (pilihan)</label>
                    @if (! empty($gift['qr']))
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $invitation->mediaUrl($gift['qr']) }}" class="size-20 rounded-lg border object-cover" alt="">
                            <label class="flex items-center gap-2 text-sm text-rose-600"><input type="checkbox" name="remove_gift_qr" value="1"> Buang QR</label>
                        </div>
                    @endif
                    <input type="file" name="gift_qr" accept="image/*" class="input">
                </div>
            </div>
        </div>

        {{-- Navigasi sisi --}}
        <aside class="hidden xl:block">
            <div class="sticky top-24 space-y-3">
                <nav class="card p-2 text-sm">
                    @foreach (['tema' => 'Pakej & Tema', 'pengantin' => 'Pengantin', 'majlis' => 'Tarikh & Lokasi', 'kandungan' => 'Ayat Jemputan', 'aturcara' => 'Aturcara', 'hubungi' => 'Hubungi', 'rsvp' => 'RSVP', 'media' => 'Lagu & Video', 'salam-kaut' => 'Salam Kaut'] as $id => $label)
                        <a href="#{{ $id }}" class="block rounded-lg px-3 py-2 text-stone-600 hover:bg-gold-50 hover:text-gold-800">{{ $label }}</a>
                    @endforeach
                </nav>
                <button class="btn-primary w-full">Simpan Perubahan</button>
            </div>
        </aside>

        <div class="sticky bottom-4 z-20 xl:hidden">
            <button class="btn-primary w-full shadow-lg">Simpan Perubahan</button>
        </div>
    </form>
@endsection
