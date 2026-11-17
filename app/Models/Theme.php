<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'palette' => 'array',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function invitations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function color(string $key, string $default = '#000000'): string
    {
        return $this->palette[$key] ?? $default;
    }

    public function isDark(): bool
    {
        return (bool) ($this->palette['dark'] ?? false);
    }

    /** CSS custom properties untuk kad jemputan. */
    public function cssVariables(): string
    {
        $colors = ['bg' => '--c-bg', 'surface' => '--c-surface', 'primary' => '--c-primary',
            'accent' => '--c-accent', 'text' => '--c-text', 'muted' => '--c-muted'];

        $css = '';
        foreach ($colors as $key => $var) {
            if (! empty($this->palette[$key])) {
                $css .= $var.':'.$this->palette[$key].';';
            }
        }

        $fonts = ['script' => ['--f-script', 'cursive'], 'heading' => ['--f-heading', 'serif'], 'body' => ['--f-body', 'sans-serif']];
        foreach ($fonts as $key => [$var, $fallback]) {
            if (! empty($this->palette['font_'.$key])) {
                $css .= $var.":'".$this->palette['font_'.$key]."',".$fallback.';';
            }
        }

        return $css;
    }

    public function googleFontsUrl(): string
    {
        $families = collect(['font_script', 'font_heading', 'font_body'])
            ->map(fn ($k) => $this->palette[$k] ?? null)
            ->filter()->unique()
            ->map(function ($family) {
                $name = str_replace(' ', '+', $family);
                $script = $family === ($this->palette['font_script'] ?? null);

                return 'family='.$name.($script ? '' : ':wght@400;500;600;700');
            });

        return 'https://fonts.googleapis.com/css2?'.$families->implode('&').'&display=swap';
    }
}
