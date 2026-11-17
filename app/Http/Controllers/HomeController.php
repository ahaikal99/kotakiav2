<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\Invitation;
use App\Models\Package;
use App\Models\Rsvp;
use App\Models\Theme;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'packages' => Package::active()->get(),
            'themes' => Theme::active()->get(),
        ]);
    }

    /** Pratonton tema menggunakan data contoh (tidak disimpan ke DB). */
    public function demo(Theme $theme)
    {
        abort_unless($theme->is_active, 404);

        $package = Package::active()->reorder()->orderByDesc('price')->first() ?? new Package([
            'has_music' => false, 'has_gallery' => false, 'has_video' => false, 'has_rsvp' => true,
            'has_wishes' => true, 'has_countdown' => true, 'has_gift' => true, 'active_days' => 30,
        ]);

        $invitation = new Invitation(array_merge(Invitation::defaultContent(), [
            'event_title' => 'Walimatulurus',
            'groom_nickname' => 'Adam',
            'groom_fullname' => 'Muhammad Adam bin Ismail',
            'bride_nickname' => 'Hawa',
            'bride_fullname' => 'Nur Hawa binti Abdullah',
            'hosts' => 'Encik Abdullah bin Hassan & Puan Siti Aminah binti Yusof',
            'event_date' => now()->addMonths(2)->next('Saturday'),
            'start_time' => '11:00',
            'end_time' => '16:00',
            'venue_name' => 'Dewan Seri Kenanga',
            'venue_address' => 'Jalan Bunga Raya 3, Taman Melati, 53100 Kuala Lumpur',
            'maps_url' => 'https://maps.google.com/?q=Kuala+Lumpur',
            'waze_url' => 'https://waze.com/ul?q=Kuala%20Lumpur',
            'contacts' => [
                ['name' => 'Abdullah', 'relation' => 'Bapa', 'phone' => '0123456789'],
                ['name' => 'Aminah', 'relation' => 'Ibu', 'phone' => '0198765432'],
            ],
            'gift' => ['bank_name' => 'Maybank', 'account_name' => 'Nur Hawa binti Abdullah', 'account_no' => '1234 5678 9012'],
            'rsvp_enabled' => true,
            'max_pax_per_rsvp' => 5,
        ]));
        $invitation->slug = 'demo';
        $invitation->status = Invitation::STATUS_ACTIVE;
        $invitation->setRelation('theme', $theme);
        $invitation->setRelation('package', $package);
        $invitation->setRelation('gallery', collect());

        $wishes = collect([
            new Rsvp(['name' => 'Kak Long', 'attendance' => 'hadir', 'wish' => 'Selamat pengantin baru! Semoga berkekalan hingga ke Jannah.']),
            new Rsvp(['name' => 'Farid & Family', 'attendance' => 'hadir', 'wish' => 'Barakallahu lakuma wa baraka alaikuma. Tahniah!']),
        ])->each(fn ($r) => $r->created_at = now()->subHours(rand(1, 48)));

        return view('invitation.show', [
            'invitation' => $invitation,
            'theme' => $theme,
            'guest' => request('to', 'Tetamu Yang Dihormati'),
            'wishes' => $wishes,
            'mode' => 'demo',
        ]);
    }
}
