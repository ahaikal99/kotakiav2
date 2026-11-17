@php
    /** @var \App\Models\Invitation $invitation */
    $isLive = $mode === 'live';
    $skipCover = request()->boolean('buka') || session('rsvp_success') || session('rsvp_error') || $errors->any();
    $cover = $invitation->mediaUrl($invitation->cover_image);
    $music = $invitation->feature('music') ? $invitation->mediaUrl($invitation->music_path) : null;
    $gallery = $invitation->package->has_gallery ? $invitation->gallery : collect();
    $contacts = collect($invitation->contacts ?? []);
    $schedule = collect($invitation->schedule ?? []);
    $gift = $invitation->gift ?? [];
    $waPhone = fn ($p) => preg_replace('/^0/', '60', preg_replace('/\D/', '', (string) $p));
    $rsvpAction = $isLive ? route('invitation.rsvp', $invitation->slug) : '#';
@endphp
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $invitation->event_title }} {{ $invitation->coupleName() }}</title>
    <meta name="description" content="{{ $invitation->dateLabel() }} · {{ $invitation->venue_name }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $invitation->event_title }} {{ $invitation->coupleName() }}">
    <meta property="og:description" content="{{ $invitation->dateLabel() }} · {{ $invitation->venue_name }}. Klik untuk membuka kad jemputan.">
    <meta property="og:image" content="{{ $cover ? url($cover) : asset('images/logo-512.png') }}">
    @if ($isLive)<meta property="og:url" content="{{ $invitation->url() }}">@else<meta name="robots" content="noindex">@endif
    <meta name="theme-color" content="{{ $theme->color('bg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $theme->googleFontsUrl() }}" rel="stylesheet">
    <style>
        :root { {!! $theme->cssVariables() !!} --radius: 18px; }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: var(--f-body); color: var(--c-text); background: color-mix(in srgb, var(--c-bg) 82%, var(--c-primary)); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        body.locked { overflow: hidden; }
        img { max-width: 100%; display: block; }
        a { color: inherit; }
        .stage { position: relative; max-width: 480px; margin: 0 auto; min-height: 100vh; background: var(--c-bg); box-shadow: 0 0 60px rgba(0,0,0,.12); overflow: hidden; padding-bottom: 90px; }
        .pattern { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Cg fill='none' stroke='%23999' stroke-opacity='.10' stroke-width='1.2'%3E%3Cpath d='M32 4 60 32 32 60 4 32Z'/%3E%3Cpath d='M32 18 46 32 32 46 18 32Z'/%3E%3C/g%3E%3C/svg%3E"); }
        .script { font-family: var(--f-script); font-weight: 400; }
        .heading { font-family: var(--f-heading); }
        .eyebrow { font-size: 11px; letter-spacing: .35em; text-transform: uppercase; color: var(--c-muted); }
        .primary { color: var(--c-primary); }
        .muted { color: var(--c-muted); }
        .center { text-align: center; }
        .ornament { color: var(--c-primary); }
        .ornament svg { display: block; }

        /* Ribbon pratonton */
        .ribbon { position: sticky; top: 0; z-index: 60; background: #1f1a14; color: #f5ebd3; font: 500 12px/1.4 system-ui, sans-serif; text-align: center; padding: 8px 12px; }
        .ribbon a { color: #ddbb73; }

        /* Muka depan / sampul */
        .cover { position: fixed; top: 0; bottom: 0; left: 50%; width: min(480px, 100%); transform: translateX(-50%); z-index: 50; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 32px; background-color: var(--c-bg); transition: transform 1s cubic-bezier(.7,0,.3,1), opacity 1s; overflow: hidden; }
        .cover.opened { transform: translate(-50%, -105%); opacity: .6; pointer-events: none; }
        .cover .frame { position: absolute; inset: 18px; border: 1px solid color-mix(in srgb, var(--c-primary) 45%, transparent); border-radius: 220px 220px 24px 24px; pointer-events: none; }
        .cover .frame::after { content: ''; position: absolute; inset: 8px; border: 1px solid color-mix(in srgb, var(--c-primary) 25%, transparent); border-radius: 210px 210px 18px 18px; }
        .cover .names { font-size: 64px; line-height: 1.05; color: var(--c-primary); margin: 14px 0 6px; }
        .cover .amp { font-family: var(--f-heading); font-size: 26px; color: var(--c-accent); display: block; margin: 2px 0; }
        .cover .guest { margin-top: 28px; font-size: 13px; color: var(--c-muted); }
        .cover .guest strong { display: block; margin-top: 4px; font-family: var(--f-heading); font-size: 22px; color: var(--c-text); font-weight: 600; }
        .corner { position: absolute; width: 120px; height: 120px; opacity: .55; }
        .corner.tl { top: -34px; left: -34px; } .corner.tr { top: -34px; right: -34px; } .corner.bl { bottom: -34px; left: -34px; } .corner.br { bottom: -34px; right: -34px; }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; cursor: pointer; border-radius: 999px; padding: 12px 26px; font: 500 14px var(--f-body); text-decoration: none; background: var(--c-primary); color: #fff; box-shadow: 0 8px 20px -8px var(--c-primary); transition: transform .2s, filter .2s; }
        .btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
        .btn.outline { background: transparent; color: var(--c-primary); border: 1px solid var(--c-primary); box-shadow: none; }
        .btn.sm { padding: 8px 16px; font-size: 13px; }
        .btn svg { width: 16px; height: 16px; }

        /* Hero */
        .hero { position: relative; min-height: min(92vh, 780px); display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; padding: 40px 28px 56px; color: #fff; background: var(--c-accent); }
        .hero.has-img::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.05) 30%, rgba(0,0,0,.65)); z-index: 1; }
        .hero img.bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .hero.no-img { justify-content: center; color: var(--c-text); background-color: var(--c-surface); }
        .hero > * { position: relative; z-index: 2; }
        .hero .names { font-size: 58px; line-height: 1.1; margin: 10px 0; }
        .hero.no-img .names { color: var(--c-primary); }
        .hero .date { font-family: var(--f-heading); font-size: 20px; letter-spacing: .08em; }
        .hero.has-img .eyebrow { color: rgba(255,255,255,.85); }

        section.block { padding: 56px 28px; text-align: center; position: relative; }
        section.alt { background: var(--c-surface); }
        .title { font-family: var(--f-heading); font-size: 30px; font-weight: 600; color: var(--c-primary); margin: 6px 0 18px; line-height: 1.2; }
        .lead { font-size: 15px; color: var(--c-text); }
        .fullname { font-family: var(--f-heading); font-size: 24px; font-weight: 600; color: var(--c-text); margin: 0; line-height: 1.3; }
        .divider { display: flex; align-items: center; justify-content: center; gap: 12px; margin: 22px auto; color: var(--c-primary); }
        .divider::before, .divider::after { content: ''; height: 1px; width: 70px; background: linear-gradient(90deg, transparent, var(--c-primary)); }
        .divider::after { transform: scaleX(-1); }

        .panel { background: var(--c-surface); border: 1px solid color-mix(in srgb, var(--c-primary) 20%, transparent); border-radius: var(--radius); padding: 22px; box-shadow: 0 10px 30px -18px rgba(0,0,0,.25); }
        section.alt .panel { background: var(--c-bg); }
        .info-row { display: flex; gap: 14px; align-items: flex-start; text-align: left; padding: 10px 0; }
        .info-row + .info-row { border-top: 1px dashed color-mix(in srgb, var(--c-primary) 25%, transparent); }
        .info-row .ico { flex: none; width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; background: color-mix(in srgb, var(--c-primary) 14%, transparent); color: var(--c-primary); }
        .info-row .ico svg { width: 18px; height: 18px; }
        .info-row small { display: block; font-size: 11px; letter-spacing: .15em; text-transform: uppercase; color: var(--c-muted); }
        .info-row strong { font-weight: 600; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 18px; }

        .countdown { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 8px; }
        .countdown div { background: var(--c-surface); border-radius: 14px; padding: 14px 4px; border: 1px solid color-mix(in srgb, var(--c-primary) 20%, transparent); }
        .countdown b { display: block; font-family: var(--f-heading); font-size: 30px; color: var(--c-primary); line-height: 1; }
        .countdown span { font-size: 11px; color: var(--c-muted); letter-spacing: .1em; text-transform: uppercase; }

        .timeline { text-align: left; position: relative; margin: 0 auto; max-width: 340px; }
        .timeline::before { content: ''; position: absolute; left: 88px; top: 6px; bottom: 6px; width: 1px; background: color-mix(in srgb, var(--c-primary) 40%, transparent); }
        .timeline .item { display: grid; grid-template-columns: 76px 1fr; gap: 24px; padding: 9px 0; position: relative; }
        .timeline .item::before { content: ''; position: absolute; left: 84px; top: 17px; width: 9px; height: 9px; border-radius: 50%; background: var(--c-primary); box-shadow: 0 0 0 4px var(--c-bg); }
        section.alt .timeline .item::before { box-shadow: 0 0 0 4px var(--c-surface); }
        .timeline .time { text-align: right; font-weight: 600; color: var(--c-primary); font-size: 14px; padding-top: 2px; }

        .video { position: relative; padding-top: 56.25%; border-radius: var(--radius); overflow: hidden; background: #000; }
        .video iframe, .video video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

        .gallery { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .gallery button { padding: 0; border: 0; cursor: zoom-in; border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: var(--c-surface); }
        .gallery button:nth-child(5n+1) { grid-column: span 2; aspect-ratio: 16/10; }
        .gallery img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
        .gallery button:hover img { transform: scale(1.05); }
        .lightbox { position: fixed; inset: 0; z-index: 90; background: rgba(0,0,0,.92); display: none; align-items: center; justify-content: center; padding: 20px; }
        .lightbox.show { display: flex; }
        .lightbox img { max-height: 88vh; border-radius: 8px; }
        .lightbox .lb-btn { position: absolute; background: rgba(255,255,255,.12); color: #fff; border: 0; width: 44px; height: 44px; border-radius: 50%; font-size: 22px; cursor: pointer; }

        form.rsvp { text-align: left; display: grid; gap: 14px; }
        form.rsvp label.l { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .field { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid color-mix(in srgb, var(--c-primary) 30%, transparent); background: var(--c-bg); color: var(--c-text); font: 15px var(--f-body); }
        .field:focus { outline: 2px solid color-mix(in srgb, var(--c-primary) 40%, transparent); outline-offset: 1px; }
        .choices { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .choices label { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 12px; border: 1px solid color-mix(in srgb, var(--c-primary) 30%, transparent); cursor: pointer; font-size: 14px; }
        .choices input { accent-color: var(--c-primary); }
        .choices label:has(input:checked) { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
        .choices label:has(input:checked) input { accent-color: #fff; }
        .alert { padding: 12px 14px; border-radius: 12px; font-size: 14px; margin-bottom: 14px; text-align: left; }
        .alert.ok { background: #e9f7ef; color: #1e6b3f; } .alert.err { background: #fdecee; color: #9b1c2e; }

        .wishes { max-height: 420px; overflow-y: auto; display: grid; gap: 10px; text-align: left; margin-top: 18px; padding-right: 4px; }
        .wish { background: var(--c-surface); border-radius: 14px; padding: 14px 16px; border-left: 3px solid var(--c-primary); }
        section.alt .wish { background: var(--c-bg); }
        .wish p { margin: 4px 0 0; font-size: 14px; }
        .wish .who { font-weight: 600; font-size: 14px; }
        .wish .when { font-size: 11px; color: var(--c-muted); }

        .contact { display: flex; align-items: center; gap: 12px; text-align: left; padding: 12px 0; }
        .contact + .contact { border-top: 1px dashed color-mix(in srgb, var(--c-primary) 25%, transparent); }
        .contact .who { flex: 1; } .contact .who small { display: block; color: var(--c-muted); font-size: 12px; }
        .icon-btn { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; background: color-mix(in srgb, var(--c-primary) 14%, transparent); color: var(--c-primary); text-decoration: none; }
        .icon-btn svg { width: 18px; height: 18px; }

        .gift-card { background: linear-gradient(135deg, var(--c-primary), var(--c-accent)); color: #fff; border-radius: var(--radius); padding: 22px; text-align: left; position: relative; overflow: hidden; }
        .gift-card .no { font-family: var(--f-heading); font-size: 24px; letter-spacing: .08em; margin: 8px 0 2px; }
        .gift-card .copy { background: rgba(255,255,255,.2); color: #fff; border: 0; border-radius: 999px; padding: 6px 14px; cursor: pointer; font-size: 12px; margin-top: 10px; }
        .qr { margin: 16px auto 0; width: 200px; border-radius: 12px; background: #fff; padding: 10px; }

        footer.foot { padding: 34px 20px 20px; text-align: center; font-size: 12px; color: var(--c-muted); }
        footer.foot img { width: 34px; margin: 0 auto 8px; opacity: .85; }
        footer.foot a { text-decoration: none; color: var(--c-primary); font-weight: 500; }

        /* Navigasi bawah */
        .dock { position: fixed; bottom: 14px; left: 50%; transform: translateX(-50%); z-index: 40; width: min(440px, calc(100% - 24px)); display: flex; justify-content: space-around; background: color-mix(in srgb, var(--c-surface) 88%, transparent); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid color-mix(in srgb, var(--c-primary) 25%, transparent); border-radius: 22px; padding: 8px 6px; box-shadow: 0 10px 30px -10px rgba(0,0,0,.3); }
        .dock a, .dock button { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; font: 500 11px var(--f-body); color: var(--c-text); text-decoration: none; background: none; border: 0; cursor: pointer; padding: 4px; }
        .dock svg { width: 21px; height: 21px; color: var(--c-primary); }
        .dock .spin svg { animation: spin 4s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .modal { position: fixed; inset: 0; z-index: 80; background: rgba(0,0,0,.45); display: none; align-items: flex-end; justify-content: center; }
        .modal.show { display: flex; }
        .sheet { background: var(--c-surface); width: min(480px, 100%); border-radius: 24px 24px 0 0; padding: 24px 24px 34px; max-height: 80vh; overflow-y: auto; animation: up .3s ease; }
        @keyframes up { from { transform: translateY(40px); opacity: 0; } }

        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .9s ease, transform .9s ease; }
        .reveal.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; } .cover { transition: none; } }
    </style>
</head>
<body class="{{ $skipCover ? '' : 'locked' }}">
<svg width="0" height="0" style="position:absolute">
    <defs>
        <symbol id="geo" viewBox="0 0 100 100">
            <g fill="none" stroke="currentColor" stroke-width="3">
                <path d="M50 4 96 50 50 96 4 50Z"/><path d="M50 18 82 50 50 82 18 50Z"/><path d="M50 32 68 50 50 68 32 50Z"/>
                <path d="M50 4v14M96 50H82M50 96V82M4 50h14"/>
            </g>
        </symbol>
        <symbol id="i-cal" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></symbol>
        <symbol id="i-clock" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol>
        <symbol id="i-pin" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></g></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></symbol>
        <symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.02c-.24.68-1.43 1.31-1.97 1.36-.5.05-.97.24-3.27-.68-2.77-1.09-4.52-3.92-4.66-4.1-.13-.18-1.11-1.48-1.11-2.82 0-1.34.7-2 .95-2.28.25-.27.54-.34.72-.34h.52c.17 0 .39-.06.61.47.23.54.77 1.88.84 2.02.07.13.11.29.02.47-.09.18-.13.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.55.16.27.7 1.15 1.5 1.87 1.03.92 1.9 1.2 2.17 1.34.27.13.43.11.59-.07.16-.18.68-.79.86-1.07.18-.27.36-.23.61-.14.25.09 1.58.75 1.85.88.27.14.45.2.52.32.07.11.07.66-.17 1.33z"/></symbol>
        <symbol id="i-music" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol>
        <symbol id="i-gift" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></symbol>
    </defs>
</svg>

<div class="stage pattern">
    @if ($mode === 'preview')
        <div class="ribbon">PRATONTON — kad ini belum aktif. Tetamu belum boleh melihatnya.
            @auth <a href="{{ route('invitations.show', $invitation) }}">Kembali ke dashboard</a>@endauth
        </div>
    @elseif ($mode === 'demo')
        <div class="ribbon">CONTOH TEMA "{{ $theme->name }}" · <a href="{{ route('invitations.create', ['tema' => $theme->slug]) }}">Guna tema ini →</a></div>
    @endif

    {{-- ========== SAMPUL ========== --}}
    @unless ($skipCover)
        <div class="cover pattern" id="cover">
            <div class="frame"></div>
            <svg class="corner tl ornament"><use href="#geo"/></svg>
            <svg class="corner tr ornament"><use href="#geo"/></svg>
            <svg class="corner bl ornament"><use href="#geo"/></svg>
            <svg class="corner br ornament"><use href="#geo"/></svg>
            <div class="eyebrow">{{ $invitation->event_title }}</div>
            <div class="names script">
                {{ $invitation->groom_nickname }}<span class="amp">&amp;</span>{{ $invitation->bride_nickname }}
            </div>
            <div class="heading" style="font-size:18px; letter-spacing:.06em">{{ $invitation->event_date->format('d . m . Y') }}</div>
            @if ($guest)
                <div class="guest">Kepada<strong>{{ $guest }}</strong></div>
            @endif
            <button class="btn" style="margin-top:28px" id="open-btn">
                <svg viewBox="0 0 24 24"><use href="#i-gift"/></svg> Buka Jemputan
            </button>
        </div>
    @endunless

    {{-- ========== HERO ========== --}}
    <header class="hero {{ $cover ? 'has-img' : 'no-img' }}">
        @if ($cover)
            <img class="bg" src="{{ $cover }}" alt="{{ $invitation->coupleName() }}">
        @else
            <svg class="ornament" style="width:90px;height:90px;margin-bottom:10px"><use href="#geo"/></svg>
        @endif
        <div class="eyebrow">{{ $invitation->event_title }}</div>
        <div class="names script">{{ $invitation->coupleName() }}</div>
        <div class="date">{{ $invitation->dateLabel() }}</div>
    </header>

    {{-- ========== JEMPUTAN ========== --}}
    <section class="block">
        <div class="reveal">
            <svg class="ornament" style="width:46px;height:46px;margin:0 auto 14px"><use href="#geo"/></svg>
            @if ($invitation->greeting)
                <p class="heading" style="font-size:19px; font-style:italic; margin:0 0 18px">{{ $invitation->greeting }}</p>
            @endif
            @if ($invitation->hosts)
                <p class="fullname" style="font-size:19px">{{ $invitation->hosts }}</p>
                <div class="divider">✦</div>
            @endif
            @if ($invitation->message)
                <p class="lead">{!! nl2br(e($invitation->message)) !!}</p>
            @endif
            <div style="margin:26px 0 10px">
                <p class="fullname">{{ $invitation->groom_fullname ?: $invitation->groom_nickname }}</p>
                <p class="script primary" style="font-size:40px; margin:4px 0; line-height:1">&amp;</p>
                <p class="fullname">{{ $invitation->bride_fullname ?: $invitation->bride_nickname }}</p>
            </div>
        </div>
    </section>

    {{-- ========== BUTIRAN MAJLIS ========== --}}
    <section class="block alt" id="lokasi">
        <div class="reveal">
            <div class="eyebrow">Butiran Majlis</div>
            <h2 class="title">Tarikh & Lokasi</h2>
            <div class="panel">
                <div class="info-row"><span class="ico"><svg><use href="#i-cal"/></svg></span><div><small>Tarikh</small><strong>{{ $invitation->dateLabel() }}</strong></div></div>
                <div class="info-row"><span class="ico"><svg><use href="#i-clock"/></svg></span><div><small>Masa</small><strong>{{ $invitation->timeLabel() }}</strong></div></div>
                <div class="info-row"><span class="ico"><svg><use href="#i-pin"/></svg></span><div><small>Lokasi</small><strong>{{ $invitation->venue_name }}</strong>@if ($invitation->venue_address)<br><span class="muted" style="font-size:14px">{!! nl2br(e($invitation->venue_address)) !!}</span>@endif</div></div>
            </div>
            <div class="btn-row">
                @if ($invitation->maps_url)<a class="btn sm" href="{{ $invitation->maps_url }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><use href="#i-pin"/></svg> Google Maps</a>@endif
                @if ($invitation->waze_url)<a class="btn sm" href="{{ $invitation->waze_url }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><use href="#i-pin"/></svg> Waze</a>@endif
                <a class="btn sm outline" href="{{ $invitation->googleCalendarUrl() }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><use href="#i-cal"/></svg> Simpan Tarikh</a>
            </div>
        </div>
    </section>

    {{-- ========== KIRA DETIK ========== --}}
    @if ($invitation->feature('countdown') && $invitation->eventStart()->isFuture())
        <section class="block">
            <div class="reveal">
                <div class="eyebrow">Menghitung Hari</div>
                <h2 class="title">Menuju Hari Bahagia</h2>
                <div class="countdown" data-target="{{ $invitation->eventStart()->toIso8601String() }}">
                    <div><b data-u="d">0</b><span>Hari</span></div>
                    <div><b data-u="h">0</b><span>Jam</span></div>
                    <div><b data-u="m">0</b><span>Minit</span></div>
                    <div><b data-u="s">0</b><span>Saat</span></div>
                </div>
            </div>
        </section>
    @endif

    {{-- ========== ATURCARA ========== --}}
    @if ($schedule->isNotEmpty())
        <section class="block {{ $invitation->feature('countdown') ? 'alt' : '' }}">
            <div class="reveal">
                <div class="eyebrow">Aturcara</div>
                <h2 class="title">Atur Cara Majlis</h2>
                <div class="timeline">
                    @foreach ($schedule as $row)
                        <div class="item"><div class="time">{{ $row['time'] ?? '' }}</div><div>{{ $row['title'] }}</div></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========== VIDEO ========== --}}
    @if ($invitation->feature('video'))
        <section class="block">
            <div class="reveal">
                <div class="eyebrow">Kisah Kami</div>
                <h2 class="title">Video Pra-Perkahwinan</h2>
                <div class="video">
                    @if ($invitation->youtubeId())
                        <iframe src="https://www.youtube-nocookie.com/embed/{{ $invitation->youtubeId() }}?rel=0" title="Video" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @elseif ($invitation->video_path)
                        <video src="{{ $invitation->mediaUrl($invitation->video_path) }}" controls playsinline preload="metadata" data-video></video>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ========== GALERI ========== --}}
    @if ($gallery->isNotEmpty())
        <section class="block alt">
            <div class="reveal">
                <div class="eyebrow">Galeri</div>
                <h2 class="title">Detik Indah</h2>
                <div class="gallery">
                    @foreach ($gallery as $img)
                        <button type="button" data-lightbox="{{ $img->url() }}"><img src="{{ $img->url() }}" alt="" loading="lazy"></button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========== DOA ========== --}}
    @if ($invitation->doa)
        <section class="block">
            <div class="reveal">
                <svg class="ornament" style="width:40px;height:40px;margin:0 auto 10px"><use href="#geo"/></svg>
                <h2 class="title">Doa Untuk Pengantin</h2>
                <p class="heading" style="font-size:18px; font-style:italic">{!! nl2br(e($invitation->doa)) !!}</p>
            </div>
        </section>
    @endif

    {{-- ========== SALAM KAUT ========== --}}
    @if ($invitation->feature('gift'))
        <section class="block alt">
            <div class="reveal">
                <div class="eyebrow">Salam Kaut</div>
                <h2 class="title">Hadiah & Ingatan</h2>
                <p class="lead muted" style="margin-top:-6px">Kehadiran & doa anda sudah cukup bermakna. Bagi yang ingin memberi, boleh melalui:</p>
                <div class="gift-card">
                    <div style="font-size:13px; opacity:.85">{{ $gift['bank_name'] }}</div>
                    <div class="no" id="acc-no">{{ $gift['account_no'] ?? '' }}</div>
                    <div style="font-size:14px">{{ $gift['account_name'] ?? '' }}</div>
                    <button class="copy" data-copy-acc="{{ preg_replace('/\s+/', '', $gift['account_no'] ?? '') }}">Salin No. Akaun</button>
                </div>
                @if (! empty($gift['qr']))
                    <img class="qr" src="{{ $invitation->mediaUrl($gift['qr']) }}" alt="Kod QR">
                @endif
            </div>
        </section>
    @endif

    {{-- ========== RSVP ========== --}}
    @if ($invitation->feature('rsvp') || $invitation->feature('wishes'))
        <section class="block" id="rsvp">
            <div class="reveal in">
                <div class="eyebrow">{{ $invitation->feature('rsvp') ? 'RSVP' : 'Ucapan' }}</div>
                <h2 class="title">{{ $invitation->feature('rsvp') ? 'Sahkan Kehadiran' : 'Ucapan & Doa' }}</h2>
                @if ($invitation->feature('rsvp') && $invitation->rsvp_deadline)
                    <p class="muted" style="margin-top:-10px; font-size:13px">Sila sahkan sebelum {{ $invitation->rsvp_deadline->locale('ms')->translatedFormat('j F Y') }}</p>
                @endif

                @if ($invitation->feature('rsvp'))
                <div class="panel">
                    @if (session('rsvp_success'))
                        <div class="alert ok">{{ session('rsvp_success') }}</div>
                    @endif
                    @if (session('rsvp_error'))
                        <div class="alert err">{{ session('rsvp_error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert err">{{ $errors->first() }}</div>
                    @endif

                    @if ($mode === 'live' && ! $invitation->rsvpOpen())
                        <p class="muted">RSVP telah ditutup. Terima kasih.</p>
                    @elseif (! session('rsvp_success'))
                        <form class="rsvp" method="POST" action="{{ $rsvpAction }}" @if (! $isLive) onsubmit="event.preventDefault(); alert('Ini pratonton — RSVP hanya berfungsi selepas kad diaktifkan.');" @endif>
                            @csrf
                            <div>
                                <label class="l" for="r-name">Nama</label>
                                <input class="field" id="r-name" name="name" value="{{ old('name', $mode === 'live' ? $guest : '') }}" required maxlength="100" placeholder="Nama anda">
                            </div>
                            <div>
                                <label class="l" for="r-phone">No. Telefon <span class="muted">(pilihan)</span></label>
                                <input class="field" id="r-phone" name="phone" value="{{ old('phone') }}" maxlength="20" inputmode="tel" placeholder="01x-xxx xxxx">
                            </div>
                            <div>
                                <label class="l">Kehadiran</label>
                                <div class="choices">
                                    <label><input type="radio" name="attendance" value="hadir" {{ old('attendance', 'hadir') === 'hadir' ? 'checked' : '' }}> Hadir</label>
                                    <label><input type="radio" name="attendance" value="tidak" {{ old('attendance') === 'tidak' ? 'checked' : '' }}> Tidak Hadir</label>
                                </div>
                            </div>
                            <div id="pax-wrap">
                                <label class="l" for="r-pax">Bilangan Tetamu</label>
                                <select class="field" id="r-pax" name="pax">
                                    @for ($i = 1; $i <= $invitation->max_pax_per_rsvp; $i++)
                                        <option value="{{ $i }}" @selected(old('pax', 1) == $i)>{{ $i }} orang</option>
                                    @endfor
                                </select>
                            </div>
                            @if ($invitation->feature('wishes'))
                                <div>
                                    <label class="l" for="r-wish">Ucapan & Doa</label>
                                    <textarea class="field" id="r-wish" name="wish" rows="3" maxlength="500" placeholder="Tinggalkan ucapan untuk pengantin...">{{ old('wish') }}</textarea>
                                </div>
                            @endif
                            <button class="btn" type="submit"><svg viewBox="0 0 24 24"><use href="#i-check"/></svg> Hantar</button>
                        </form>
                    @endif
                </div>
                @endif

                @if ($invitation->feature('wishes') && $wishes->isNotEmpty())
                    <h3 class="heading primary" style="margin:34px 0 0; font-size:22px">Ucapan Tetamu ({{ $wishes->count() }})</h3>
                    <div class="wishes">
                        @foreach ($wishes as $w)
                            <div class="wish">
                                <div class="who">{{ $w->name }} <span class="when">· {{ $w->created_at?->locale('ms')->diffForHumans() }}</span></div>
                                <p>{{ $w->wish }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ========== HUBUNGI ========== --}}
    @if ($contacts->isNotEmpty())
        <section class="block alt" id="hubungi">
            <div class="reveal">
                <div class="eyebrow">Sebarang Pertanyaan</div>
                <h2 class="title">Hubungi Kami</h2>
                <div class="panel">
                    @foreach ($contacts as $c)
                        <div class="contact">
                            <div class="who"><strong>{{ $c['name'] }}</strong><small>{{ $c['relation'] ?? '' }}</small></div>
                            <a class="icon-btn" href="tel:{{ preg_replace('/[^\d+]/', '', $c['phone']) }}" aria-label="Telefon"><svg viewBox="0 0 24 24"><use href="#i-phone"/></svg></a>
                            <a class="icon-btn" href="https://wa.me/{{ $waPhone($c['phone']) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><use href="#i-wa"/></svg></a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <footer class="foot">
        <p class="script primary" style="font-size:36px; margin:0 0 6px">Terima Kasih</p>
        <img src="{{ asset('images/logo.png') }}" alt="">
        Kad digital oleh <a href="{{ route('home') }}" target="_blank">eCard Kotakia</a>
    </footer>

    {{-- ========== NAV BAWAH ========== --}}
    <nav class="dock">
        <a href="#lokasi"><svg viewBox="0 0 24 24"><use href="#i-pin"/></svg>Lokasi</a>
        @if ($contacts->isNotEmpty())<a href="#hubungi"><svg viewBox="0 0 24 24"><use href="#i-phone"/></svg>Hubungi</a>@endif
        @if ($invitation->feature('rsvp') || $invitation->feature('wishes'))<a href="#rsvp"><svg viewBox="0 0 24 24"><use href="#i-check"/></svg>RSVP</a>@endif
        <a href="{{ $invitation->googleCalendarUrl() }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><use href="#i-cal"/></svg>Kalendar</a>
        @if ($music)<button type="button" id="music-btn"><svg viewBox="0 0 24 24"><use href="#i-music"/></svg><span>Lagu</span></button>@endif
    </nav>
</div>

<div class="lightbox" id="lightbox">
    <button class="lb-btn" style="top:16px; right:16px" data-lb-close aria-label="Tutup">×</button>
    <button class="lb-btn" style="left:12px" data-lb-prev aria-label="Sebelum">‹</button>
    <img src="" alt="">
    <button class="lb-btn" style="right:12px" data-lb-next aria-label="Seterusnya">›</button>
</div>

@if ($music)
    <audio id="bg-music" src="{{ $music }}" loop preload="auto"></audio>
@endif

<script>
(() => {
    const cover = document.getElementById('cover');
    const audio = document.getElementById('bg-music');
    const musicBtn = document.getElementById('music-btn');

    const setMusicUi = () => {
        if (!musicBtn) return;
        musicBtn.classList.toggle('spin', !audio.paused);
        musicBtn.querySelector('span').textContent = audio.paused ? 'Main' : 'Jeda';
    };
    const play = () => audio && audio.play().then(setMusicUi).catch(setMusicUi);

    document.getElementById('open-btn')?.addEventListener('click', () => {
        cover.classList.add('opened');
        document.body.classList.remove('locked');
        play();
        setTimeout(() => cover.remove(), 1100);
    });

    musicBtn?.addEventListener('click', () => { audio.paused ? play() : (audio.pause(), setMusicUi()); });
    document.querySelector('[data-video]')?.addEventListener('play', () => { if (audio && !audio.paused) { audio.pause(); setMusicUi(); } });
    if (audio) setMusicUi();

    // Animasi muncul
    const io = new IntersectionObserver((entries) => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .15 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    // Kira detik
    const cd = document.querySelector('.countdown');
    if (cd) {
        const target = new Date(cd.dataset.target).getTime();
        const tick = () => {
            let d = Math.max(0, target - Date.now()) / 1000;
            const v = { d: Math.floor(d / 86400), h: Math.floor(d % 86400 / 3600), m: Math.floor(d % 3600 / 60), s: Math.floor(d % 60) };
            for (const k in v) cd.querySelector(`[data-u="${k}"]`).textContent = String(v[k]).padStart(2, '0');
        };
        tick(); setInterval(tick, 1000);
    }

    // RSVP: sembunyi bilangan tetamu jika tidak hadir
    const paxWrap = document.getElementById('pax-wrap');
    const syncPax = () => { const v = document.querySelector('input[name=attendance]:checked')?.value; if (paxWrap) paxWrap.style.display = v === 'tidak' ? 'none' : ''; };
    document.querySelectorAll('input[name=attendance]').forEach(r => r.addEventListener('change', syncPax));
    syncPax();

    // Salin no. akaun
    document.querySelector('[data-copy-acc]')?.addEventListener('click', async (e) => {
        try { await navigator.clipboard.writeText(e.target.dataset.copyAcc); e.target.textContent = '✓ Disalin'; } catch {}
        setTimeout(() => e.target.textContent = 'Salin No. Akaun', 1600);
    });

    // Lightbox galeri
    const lb = document.getElementById('lightbox');
    const imgs = [...document.querySelectorAll('[data-lightbox]')].map(b => b.dataset.lightbox);
    let idx = 0;
    const show = (i) => { idx = (i + imgs.length) % imgs.length; lb.querySelector('img').src = imgs[idx]; lb.classList.add('show'); };
    document.querySelectorAll('[data-lightbox]').forEach((b, i) => b.addEventListener('click', () => show(i)));
    lb.querySelector('[data-lb-close]').addEventListener('click', () => lb.classList.remove('show'));
    lb.querySelector('[data-lb-prev]').addEventListener('click', () => show(idx - 1));
    lb.querySelector('[data-lb-next]').addEventListener('click', () => show(idx + 1));
    lb.addEventListener('click', (e) => { if (e.target === lb) lb.classList.remove('show'); });
})();
</script>
</body>
</html>
