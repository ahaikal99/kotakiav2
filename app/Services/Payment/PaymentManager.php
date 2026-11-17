<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    public function gateway(?string $name = null): PaymentGateway
    {
        return match ($name ?? config('kotakia.payment.gateway')) {
            'toyyibpay' => new ToyyibPayGateway,
            'simulasi' => new SimulatedGateway,
            default => throw new InvalidArgumentException('Payment gateway tidak dikenali.'),
        };
    }
}
