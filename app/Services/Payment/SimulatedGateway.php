<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Gateway palsu untuk development — memaparkan halaman "bank" simulasi.
 * Pautan ditandatangani supaya status tidak boleh dipalsukan dari luar.
 */
class SimulatedGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulasi';
    }

    public function createBill(Payment $payment): string
    {
        $payment->update(['gateway_bill_code' => 'SIM-'.$payment->reference]);

        return URL::temporarySignedRoute('payment.simulator', now()->addHour(), ['payment' => $payment->reference]);
    }

    public function verify(Payment $payment, Request $request): string
    {
        if (! $request->hasValidSignature()) {
            return 'pending';
        }

        return $request->query('result') === 'success' ? 'paid' : 'failed';
    }
}
