<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    /** Cipta bil & pulangkan URL untuk redirect pelanggan ke halaman bayaran. */
    public function createBill(Payment $payment): string;

    /**
     * Sahkan status bayaran dari request (return / callback).
     * Pulangkan 'paid', 'failed' atau 'pending'.
     */
    public function verify(Payment $payment, Request $request): string;
}
