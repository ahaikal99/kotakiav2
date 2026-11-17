<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'has_music' => 'boolean',
            'has_gallery' => 'boolean',
            'has_video' => 'boolean',
            'has_rsvp' => 'boolean',
            'has_wishes' => 'boolean',
            'has_countdown' => 'boolean',
            'has_gift' => 'boolean',
            'premium_themes' => 'boolean',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('price');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /** Senarai ciri untuk paparan kad harga. [label, ada?] */
    public function featureList(): array
    {
        return [
            ['Kad jemputan digital + pautan unik', true],
            ['Pautan nama tetamu (Kepada: ...)', true],
            ['Kira detik ke hari majlis', $this->has_countdown],
            ['Google Maps & Waze', true],
            [$this->has_rsvp ? 'RSVP '.($this->max_rsvp ? '(had '.$this->max_rsvp.' respons)' : '(tanpa had)') : 'RSVP', $this->has_rsvp],
            ['Buku ucapan tetamu', $this->has_wishes],
            ['Lagu latar belakang', $this->has_music],
            [$this->has_gallery ? 'Galeri foto (sehingga '.$this->max_gallery.' gambar)' : 'Galeri foto', $this->has_gallery],
            ['Video pra-perkahwinan', $this->has_video],
            ['Salam kaut (maklumat akaun / QR)', $this->has_gift],
            ['Tema premium', $this->premium_themes],
            ['Aktif '.$this->active_days.' hari selepas majlis', true],
        ];
    }
}
