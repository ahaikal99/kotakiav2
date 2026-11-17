<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder idempotent — selamat dijalankan setiap kali container dimulakan.
 * Data sedia ada (harga yang diubah admin dsb.) tidak akan ditimpa.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => config('kotakia.admin.email')],
            ['name' => 'Admin Kotakia', 'password' => config('kotakia.admin.password'), 'role' => User::ROLE_ADMIN, 'email_verified_at' => now()],
        );

        foreach ($this->packages() as $i => $package) {
            Package::firstOrCreate(['slug' => $package['slug']], $package + ['sort_order' => $i + 1]);
        }

        foreach ($this->themes() as $i => $theme) {
            Theme::firstOrCreate(['slug' => $theme['slug']], $theme + ['sort_order' => $i + 1]);
        }
    }

    protected function packages(): array
    {
        return [
            [
                'name' => 'Asas', 'slug' => 'asas', 'tagline' => 'Ringkas & elegan',
                'description' => 'Sesuai untuk majlis kecil yang mahukan kad digital ringkas dengan RSVP.',
                'price' => 29.00, 'original_price' => 49.00,
                'has_music' => false, 'has_gallery' => false, 'max_gallery' => 0, 'has_video' => false,
                'has_rsvp' => true, 'max_rsvp' => 150, 'has_wishes' => true, 'has_countdown' => true,
                'has_gift' => false, 'premium_themes' => false, 'active_days' => 30,
            ],
            [
                'name' => 'Premium', 'slug' => 'premium', 'tagline' => 'Pilihan ramai pasangan',
                'description' => 'Lengkap dengan lagu latar, galeri foto dan salam kaut.',
                'price' => 59.00, 'original_price' => 89.00,
                'has_music' => true, 'has_gallery' => true, 'max_gallery' => 12, 'has_video' => false,
                'has_rsvp' => true, 'max_rsvp' => null, 'has_wishes' => true, 'has_countdown' => true,
                'has_gift' => true, 'premium_themes' => true, 'active_days' => 90, 'is_popular' => true,
            ],
            [
                'name' => 'Eksklusif', 'slug' => 'eksklusif', 'tagline' => 'Semua ciri, tanpa had',
                'description' => 'Untuk pengalaman terbaik — video pra-perkahwinan, galeri besar & tempoh aktif setahun.',
                'price' => 99.00, 'original_price' => 149.00,
                'has_music' => true, 'has_gallery' => true, 'max_gallery' => 40, 'has_video' => true,
                'has_rsvp' => true, 'max_rsvp' => null, 'has_wishes' => true, 'has_countdown' => true,
                'has_gift' => true, 'premium_themes' => true, 'active_days' => 365,
            ],
        ];
    }

    protected function themes(): array
    {
        return [
            [
                'name' => 'Emas Diraja', 'slug' => 'emas-diraja', 'description' => 'Krim lembut dengan sentuhan emas klasik.',
                'palette' => ['bg' => '#f8f3ea', 'surface' => '#fffdf8', 'primary' => '#b08a3e', 'accent' => '#7a5a1f', 'text' => '#3b2f1e', 'muted' => '#8a7a60',
                    'font_script' => 'Great Vibes', 'font_heading' => 'Cormorant Garamond', 'font_body' => 'Jost'],
            ],
            [
                'name' => 'Bunga Pastel', 'slug' => 'bunga-pastel', 'description' => 'Merah jambu lembut, romantik & ceria.',
                'palette' => ['bg' => '#fbf0f1', 'surface' => '#fffafa', 'primary' => '#c27886', 'accent' => '#8c4757', 'text' => '#4a3338', 'muted' => '#9a7c82',
                    'font_script' => 'Parisienne', 'font_heading' => 'Playfair Display', 'font_body' => 'Poppins'],
            ],
            [
                'name' => 'Hijau Sage', 'slug' => 'hijau-sage', 'description' => 'Tona alam semula jadi yang tenang.',
                'palette' => ['bg' => '#eff2ea', 'surface' => '#fbfcf8', 'primary' => '#71845d', 'accent' => '#46553a', 'text' => '#2f3628', 'muted' => '#7b8670',
                    'font_script' => 'Allura', 'font_heading' => 'Cormorant Garamond', 'font_body' => 'Nunito Sans'],
            ],
            [
                'name' => 'Minimal Putih', 'slug' => 'minimal-putih', 'description' => 'Bersih, moden & minimalis.',
                'palette' => ['bg' => '#f5f5f4', 'surface' => '#ffffff', 'primary' => '#44403c', 'accent' => '#1c1917', 'text' => '#292524', 'muted' => '#78716c',
                    'font_script' => 'Pinyon Script', 'font_heading' => 'Cinzel', 'font_body' => 'Montserrat'],
            ],
            [
                'name' => 'Malam Songket', 'slug' => 'malam-songket', 'description' => 'Latar gelap mewah dengan benang emas.', 'is_premium' => true,
                'palette' => ['bg' => '#13151d', 'surface' => '#1c1f2b', 'primary' => '#d4af61', 'accent' => '#f0d9a2', 'text' => '#f2ead8', 'muted' => '#a59c87', 'dark' => true,
                    'font_script' => 'Great Vibes', 'font_heading' => 'Cinzel', 'font_body' => 'Jost'],
            ],
            [
                'name' => 'Firus Laut', 'slug' => 'firus-laut', 'description' => 'Biru firus yang segar dan tenang.', 'is_premium' => true,
                'palette' => ['bg' => '#ebf4f3', 'surface' => '#fbfefe', 'primary' => '#2f7f86', 'accent' => '#1b5257', 'text' => '#1f3437', 'muted' => '#6b8a8d',
                    'font_script' => 'Alex Brush', 'font_heading' => 'Playfair Display', 'font_body' => 'Montserrat'],
            ],
        ];
    }
}
