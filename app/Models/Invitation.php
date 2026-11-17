<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Invitation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draf (belum bayar)',
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_EXPIRED => 'Tamat tempoh',
        self::STATUS_SUSPENDED => 'Digantung',
    ];

    protected $guarded = ['id', 'user_id', 'status', 'views', 'published_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'rsvp_deadline' => 'date',
            'schedule' => 'array',
            'contacts' => 'array',
            'gift' => 'array',
            'rsvp_enabled' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ---------- Relationships ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    // ---------- Helpers ----------

    public static function generateSlug(string $groom, string $bride): string
    {
        $base = Str::slug(Str::limit($groom, 20, '').'-'.Str::limit($bride, 20, '')) ?: 'majlis';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function coupleName(string $separator = ' & '): string
    {
        return $this->groom_nickname.$separator.$this->bride_nickname;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->expires_at?->isPast()) {
            return self::STATUS_LABELS[self::STATUS_EXPIRED];
        }

        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match (true) {
            $this->isActive() => 'emerald',
            $this->status === self::STATUS_DRAFT => 'amber',
            default => 'rose',
        };
    }

    /** Aktifkan kad selepas pembayaran berjaya. */
    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->published_at ??= now();
        $this->expires_at = $this->event_date->copy()->endOfDay()->addDays($this->package->active_days);
        $this->save();
    }

    public function url(?string $guest = null): string
    {
        $url = route('invitation.show', $this->slug);

        return $guest ? $url.'?to='.rawurlencode($guest) : $url;
    }

    public function eventStart(): Carbon
    {
        return Carbon::parse($this->event_date->format('Y-m-d').' '.$this->start_time);
    }

    public function eventEnd(): Carbon
    {
        return $this->end_time
            ? Carbon::parse($this->event_date->format('Y-m-d').' '.$this->end_time)
            : $this->eventStart()->addHours(4);
    }

    public function dateLabel(): string
    {
        return $this->event_date->locale('ms')->translatedFormat('l, j F Y');
    }

    public function timeLabel(): string
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');

        return $this->end_time ? $start.' - '.Carbon::parse($this->end_time)->format('g:i A') : $start;
    }

    public function feature(string $name): bool
    {
        $p = $this->package;

        return match ($name) {
            'music' => $p->has_music && $this->music_path,
            'gallery' => $p->has_gallery && $this->relationLoaded('gallery') ? $this->gallery->isNotEmpty() : $p->has_gallery,
            'video' => $p->has_video && ($this->video_url || $this->video_path),
            'rsvp' => $p->has_rsvp && $this->rsvp_enabled,
            'wishes' => $p->has_wishes,
            'countdown' => $p->has_countdown,
            'gift' => $p->has_gift && ! empty($this->gift['bank_name'] ?? null),
            default => false,
        };
    }

    public function rsvpOpen(): bool
    {
        if (! $this->feature('rsvp')) {
            return false;
        }
        if ($this->rsvp_deadline && $this->rsvp_deadline->endOfDay()->isPast()) {
            return false;
        }
        if ($this->package->max_rsvp && $this->rsvps()->count() >= $this->package->max_rsvp) {
            return false;
        }

        return true;
    }

    public function youtubeId(): ?string
    {
        if (! $this->video_url) {
            return null;
        }
        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $this->video_url, $m);

        return $m[1] ?? null;
    }

    public function mediaUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function googleCalendarUrl(): string
    {
        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->event_title.' '.$this->coupleName(),
            'dates' => $this->eventStart()->utc()->format('Ymd\THis\Z').'/'.$this->eventEnd()->utc()->format('Ymd\THis\Z'),
            'details' => 'Kad jemputan: '.$this->url(),
            'location' => trim($this->venue_name.', '.$this->venue_address, ', '),
        ]);
    }

    /** Ayat jemputan sedia untuk dikongsi di WhatsApp / Telegram. */
    public function shareMessage(?string $guest = null): string
    {
        $lines = [
            'Assalamualaikum & Salam Sejahtera'.($guest ? ' '.$guest : '').' 🌸',
            '',
            'Dengan penuh rasa kesyukuran, kami menjemput Tuan/Puan/Saudara/Saudari ke Majlis '.$this->event_title.':',
            '',
            '💍 *'.$this->coupleName().'*',
            '',
            '📅 '.$this->dateLabel(),
            '🕐 '.$this->timeLabel(),
            '📍 '.$this->venue_name,
            '',
            'Sila buka kad jemputan digital kami di pautan berikut'.($this->package->has_rsvp && $this->rsvp_enabled ? ' dan sahkan kehadiran (RSVP)' : '').':',
            $this->url($guest),
            '',
            'Kehadiran dan doa restu anda amatlah kami hargai. Terima kasih 🤍',
        ];

        return implode("\n", $lines);
    }

    public function whatsappShareUrl(?string $guest = null): string
    {
        return 'https://wa.me/?text='.rawurlencode($this->shareMessage($guest));
    }

    public function rsvpStats(): array
    {
        $rows = $this->rsvps()->reorder()
            ->selectRaw('attendance, COUNT(*) as total, SUM(pax) as pax')
            ->groupBy('attendance')
            ->get()
            ->keyBy('attendance');

        return [
            'responses' => (int) $rows->sum('total'),
            'attending' => (int) ($rows['hadir']->total ?? 0),
            'not_attending' => (int) ($rows['tidak']->total ?? 0),
            'pax' => (int) ($rows['hadir']->pax ?? 0),
            'wishes' => $this->rsvps()->whereNotNull('wish')->where('wish', '!=', '')->count(),
        ];
    }

    public static function defaultContent(): array
    {
        return [
            'greeting' => 'Assalamualaikum Warahmatullahi Wabarakatuh & Salam Sejahtera',
            'message' => "Dengan segala hormatnya, kami mempersilakan Dato' / Datin / Tuan / Puan / Encik / Cik seisi keluarga hadir ke majlis perkahwinan anakanda kami.",
            'doa' => 'Ya Allah, berkatilah majlis ini, kurniakanlah kebahagiaan dan keredhaan kepada kedua mempelai, limpahkanlah zuriat yang soleh dan solehah, kekalkanlah jodoh mereka hingga ke Jannah. Amin.',
            'schedule' => [
                ['time' => '11:00 AM', 'title' => 'Ketibaan tetamu'],
                ['time' => '12:30 PM', 'title' => 'Ketibaan pengantin'],
                ['time' => '1:00 PM', 'title' => 'Jamuan makan'],
                ['time' => '4:00 PM', 'title' => 'Majlis bersurai'],
            ],
        ];
    }
}
