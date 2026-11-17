<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Package;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function create(Request $request)
    {
        $packages = Package::active()->get();
        $themes = Theme::active()->get();

        return view('customer.invitations.create', [
            'packages' => $packages,
            'themes' => $themes,
            'selectedPackage' => $packages->firstWhere('slug', $request->query('pakej'))?->id ?? $packages->firstWhere('is_popular', true)?->id ?? $packages->first()?->id,
            'selectedTheme' => $themes->firstWhere('slug', $request->query('tema'))?->id ?? $themes->first()?->id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => ['required', Rule::exists('packages', 'id')->where('is_active', true)],
            'theme_id' => ['required', Rule::exists('themes', 'id')->where('is_active', true)],
            'groom_nickname' => ['required', 'string', 'max:40'],
            'bride_nickname' => ['required', 'string', 'max:40'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'venue_name' => ['required', 'string', 'max:150'],
        ], [], $this->attributeNames());

        $this->ensureThemeAllowed($data['theme_id'], $data['package_id']);

        $invitation = new Invitation(array_merge(Invitation::defaultContent(), $data));
        $invitation->user_id = $request->user()->id;
        $invitation->slug = Invitation::generateSlug($data['groom_nickname'], $data['bride_nickname']);
        $invitation->status = Invitation::STATUS_DRAFT;
        $invitation->save();

        return redirect()->route('invitations.edit', $invitation)
            ->with('success', 'Draf kad berjaya dicipta! Lengkapkan butiran di bawah, kemudian buat pembayaran untuk mengaktifkan kad.');
    }

    public function show(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $invitation->load(['package', 'theme', 'payments']);

        return view('customer.invitations.show', [
            'invitation' => $invitation,
            'stats' => $invitation->rsvpStats(),
            'recent' => $invitation->rsvps()->limit(5)->get(),
        ]);
    }

    public function edit(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $invitation->load(['package', 'theme']);

        return view('customer.invitations.edit', [
            'invitation' => $invitation,
            'packages' => Package::active()->get(),
            'themes' => Theme::active()->get(),
        ]);
    }

    public function update(Request $request, Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $limits = config('kotakia.uploads');

        $rules = [
            'theme_id' => ['required', Rule::exists('themes', 'id')->where('is_active', true)],
            'event_title' => ['required', 'string', 'max:60'],
            'groom_nickname' => ['required', 'string', 'max:40'],
            'groom_fullname' => ['nullable', 'string', 'max:120'],
            'bride_nickname' => ['required', 'string', 'max:40'],
            'bride_fullname' => ['nullable', 'string', 'max:120'],
            'hosts' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue_name' => ['required', 'string', 'max:150'],
            'venue_address' => ['nullable', 'string', 'max:500'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'waze_url' => ['nullable', 'url', 'max:500'],
            'greeting' => ['nullable', 'string', 'max:300'],
            'message' => ['nullable', 'string', 'max:1500'],
            'doa' => ['nullable', 'string', 'max:1500'],
            'schedule' => ['nullable', 'array', 'max:20'],
            'schedule.*.time' => ['nullable', 'string', 'max:20'],
            'schedule.*.title' => ['nullable', 'string', 'max:100'],
            'contacts' => ['nullable', 'array', 'max:6'],
            'contacts.*.name' => ['nullable', 'string', 'max:60'],
            'contacts.*.relation' => ['nullable', 'string', 'max:40'],
            'contacts.*.phone' => ['nullable', 'string', 'max:20'],
            'rsvp_enabled' => ['boolean'],
            'rsvp_deadline' => ['nullable', 'date'],
            'max_pax_per_rsvp' => ['required', 'integer', 'min:1', 'max:20'],
            'cover_image' => ['nullable', 'image', 'max:'.$limits['image_max_kb']],
            'remove_cover' => ['boolean'],
            'music_file' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/mp3,audio/mp4,audio/x-m4a,audio/aac,audio/ogg', 'max:'.$limits['music_max_kb']],
            'music_title' => ['nullable', 'string', 'max:100'],
            'remove_music' => ['boolean'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:'.$limits['video_max_kb']],
            'remove_video' => ['boolean'],
            'gift.bank_name' => ['nullable', 'string', 'max:60'],
            'gift.account_name' => ['nullable', 'string', 'max:100'],
            'gift.account_no' => ['nullable', 'string', 'max:40'],
            'gift_qr' => ['nullable', 'image', 'max:'.$limits['image_max_kb']],
            'remove_gift_qr' => ['boolean'],
        ];

        if ($invitation->isDraft()) {
            $rules['package_id'] = ['required', Rule::exists('packages', 'id')->where('is_active', true)];
            $rules['slug'] = ['required', 'alpha_dash:ascii', 'min:3', 'max:60', Rule::unique('invitations', 'slug')->ignore($invitation->id)];
        }

        $data = $request->validate($rules, [], $this->attributeNames());

        $packageId = $data['package_id'] ?? $invitation->package_id;
        $this->ensureThemeAllowed($data['theme_id'], $packageId);
        $package = Package::findOrFail($packageId);

        $invitation->fill(collect($data)->only([
            'theme_id', 'event_title', 'groom_nickname', 'groom_fullname', 'bride_nickname', 'bride_fullname',
            'hosts', 'event_date', 'start_time', 'end_time', 'venue_name', 'venue_address', 'maps_url', 'waze_url',
            'greeting', 'message', 'doa', 'rsvp_deadline', 'max_pax_per_rsvp', 'music_title', 'video_url',
        ])->all());

        if ($invitation->isDraft()) {
            $invitation->package_id = $packageId;
            $invitation->slug = strtolower($data['slug']);
        }

        $invitation->rsvp_enabled = $request->boolean('rsvp_enabled');
        $invitation->schedule = collect($data['schedule'] ?? [])
            ->filter(fn ($r) => filled($r['title'] ?? null))->values()->all();
        $invitation->contacts = collect($data['contacts'] ?? [])
            ->filter(fn ($r) => filled($r['name'] ?? null) && filled($r['phone'] ?? null))->values()->all();

        $dir = 'invitations/'.$invitation->id;

        // Gambar muka depan
        if ($request->boolean('remove_cover')) {
            $this->deleteFile($invitation->cover_image);
            $invitation->cover_image = null;
        }
        if ($request->hasFile('cover_image')) {
            $this->deleteFile($invitation->cover_image);
            $invitation->cover_image = $request->file('cover_image')->store($dir, 'public');
        }

        // Lagu latar
        if ($request->boolean('remove_music')) {
            $this->deleteFile($invitation->music_path);
            $invitation->music_path = null;
        }
        if ($request->hasFile('music_file') && $package->has_music) {
            $this->deleteFile($invitation->music_path);
            $invitation->music_path = $request->file('music_file')->store($dir.'/music', 'public');
        }

        // Video
        if ($request->boolean('remove_video')) {
            $this->deleteFile($invitation->video_path);
            $invitation->video_path = null;
        }
        if ($request->hasFile('video_file') && $package->has_video) {
            $this->deleteFile($invitation->video_path);
            $invitation->video_path = $request->file('video_file')->store($dir.'/video', 'public');
        }

        // Salam kaut
        $gift = array_merge($invitation->gift ?? [], array_map(fn ($v) => $v ?: null, $data['gift'] ?? []));
        if ($request->boolean('remove_gift_qr')) {
            $this->deleteFile($gift['qr'] ?? null);
            $gift['qr'] = null;
        }
        if ($request->hasFile('gift_qr') && $package->has_gift) {
            $this->deleteFile($gift['qr'] ?? null);
            $gift['qr'] = $request->file('gift_qr')->store($dir, 'public');
        }
        $invitation->gift = $gift;

        $invitation->save();

        // Kemas kini tarikh tamat jika tarikh majlis berubah selepas aktif
        if ($invitation->status === Invitation::STATUS_ACTIVE && $invitation->wasChanged('event_date')) {
            $invitation->load('package')->activate();
        }

        return redirect()->route('invitations.edit', $invitation)->with('success', 'Butiran kad berjaya disimpan.');
    }

    public function destroy(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);

        if (! $invitation->isDraft() || $invitation->payments()->where('status', 'paid')->exists()) {
            return back()->with('error', 'Hanya kad draf yang belum dibayar boleh dipadam.');
        }

        Storage::disk('public')->deleteDirectory('invitations/'.$invitation->id);
        $invitation->delete();

        return redirect()->route('dashboard')->with('success', 'Draf kad telah dipadam.');
    }

    public function preview(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $invitation->load(['theme', 'package', 'gallery']);

        return view('invitation.show', [
            'invitation' => $invitation,
            'theme' => $invitation->theme,
            'guest' => request('to', 'Tetamu Yang Dihormati'),
            'wishes' => $invitation->rsvps()->where('wish_visible', true)->whereNotNull('wish')->where('wish', '!=', '')->limit(100)->get(),
            'mode' => 'preview',
        ]);
    }

    protected function ensureThemeAllowed(int|string $themeId, int|string $packageId): void
    {
        $theme = Theme::find($themeId);
        $package = Package::find($packageId);

        if ($theme?->is_premium && ! $package?->premium_themes) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'theme_id' => 'Tema "'.$theme->name.'" hanya untuk pakej yang menyokong tema premium.',
            ]);
        }
    }

    protected function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function attributeNames(): array
    {
        return [
            'package_id' => 'pakej', 'theme_id' => 'tema', 'event_title' => 'tajuk majlis',
            'groom_nickname' => 'nama panggilan pengantin lelaki', 'bride_nickname' => 'nama panggilan pengantin perempuan',
            'groom_fullname' => 'nama penuh pengantin lelaki', 'bride_fullname' => 'nama penuh pengantin perempuan',
            'hosts' => 'tuan rumah', 'event_date' => 'tarikh majlis', 'start_time' => 'masa mula', 'end_time' => 'masa tamat',
            'venue_name' => 'nama lokasi', 'venue_address' => 'alamat', 'maps_url' => 'pautan Google Maps', 'waze_url' => 'pautan Waze',
            'slug' => 'pautan kad', 'cover_image' => 'gambar muka depan', 'music_file' => 'fail lagu', 'video_file' => 'fail video',
            'video_url' => 'pautan video', 'gift_qr' => 'kod QR', 'max_pax_per_rsvp' => 'had tetamu setiap RSVP',
        ];
    }
}
