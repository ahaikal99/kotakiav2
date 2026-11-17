<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_PAID => 'Berjaya',
        self::STATUS_FAILED => 'Gagal',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public static function newReference(): string
    {
        return 'KTK'.now()->format('ymd').strtoupper(Str::random(6));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'emerald',
            self::STATUS_PENDING => 'amber',
            default => 'rose',
        };
    }

    /**
     * Tandakan bayaran berjaya & aktifkan kad. Idempotent — selamat dipanggil
     * berulang kali (return URL + callback server boleh tiba serentak).
     */
    public function markAsPaid(?string $transactionId = null, array $payload = []): void
    {
        DB::transaction(function () use ($transactionId, $payload) {
            $payment = static::whereKey($this->id)->lockForUpdate()->first();
            if ($payment->isPaid()) {
                return;
            }

            $payment->forceFill([
                'status' => self::STATUS_PAID,
                'paid_at' => now(),
                'gateway_transaction_id' => $transactionId ?? $payment->gateway_transaction_id,
                'gateway_payload' => array_merge($payment->gateway_payload ?? [], $payload),
            ])->save();

            $invitation = $payment->invitation;
            $invitation->package_id = $payment->package_id;
            $invitation->setRelation('package', $payment->package);
            $invitation->activate();
        });

        $this->refresh();
    }

    public function markAsFailed(array $payload = []): void
    {
        if ($this->isPaid()) {
            return;
        }
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'gateway_payload' => array_merge($this->gateway_payload ?? [], $payload),
        ])->save();
    }
}
