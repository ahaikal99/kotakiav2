@extends('layouts.dashboard', ['title' => 'Cipta Kad Baru'])

@section('content')
    <form method="POST" action="{{ route('invitations.store') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        <div class="card space-y-6 p-6">
            <div>
                <h2 class="section-title">1. Pilih Pakej & Tema</h2>
                <p class="text-sm text-stone-500">Anda boleh menukar pakej selagi kad belum dibayar.</p>
            </div>
            @include('customer.invitations._pickers', ['showPackages' => true])
        </div>

        <div class="card space-y-5 p-6">
            <div>
                <h2 class="section-title">2. Maklumat Asas</h2>
                <p class="text-sm text-stone-500">Butiran lain (aturcara, lagu, galeri dll.) boleh dilengkapkan selepas ini.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="groom_nickname">Nama Panggilan Pengantin Lelaki</label>
                    <input id="groom_nickname" name="groom_nickname" value="{{ old('groom_nickname') }}" class="input" placeholder="cth. Adam" required>
                </div>
                <div>
                    <label class="label" for="bride_nickname">Nama Panggilan Pengantin Perempuan</label>
                    <input id="bride_nickname" name="bride_nickname" value="{{ old('bride_nickname') }}" class="input" placeholder="cth. Hawa" required>
                </div>
                <div>
                    <label class="label" for="event_date">Tarikh Majlis</label>
                    <input id="event_date" type="date" name="event_date" value="{{ old('event_date') }}" min="{{ today()->toDateString() }}" class="input" required>
                </div>
                <div>
                    <label class="label" for="start_time">Masa Mula</label>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time', '11:00') }}" class="input" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="venue_name">Nama Lokasi / Dewan</label>
                    <input id="venue_name" name="venue_name" value="{{ old('venue_name') }}" class="input" placeholder="cth. Dewan Seri Kenanga" required>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="btn-ghost">Batal</a>
            <button class="btn-primary">Cipta Draf Kad <x-icon name="arrow-right" class="size-4" /></button>
        </div>
    </form>
@endsection
