<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    protected $fillable = ['name', 'phone', 'attendance', 'pax', 'wish', 'wish_visible', 'ip_address'];

    protected function casts(): array
    {
        return ['wish_visible' => 'boolean'];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function attending(): bool
    {
        return $this->attendance === 'hadir';
    }
}
