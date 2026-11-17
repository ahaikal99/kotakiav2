<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Integrasi ToyyibPay (FPX / kad).
 * Dokumentasi: https://toyyibpay.com/apireference/
 */
class ToyyibPayGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'toyyibpay';
    }

    protected function baseUrl(): string
    {
        return config('kotakia.toyyibpay.sandbox') ? 'https://dev.toyyibpay.com' : 'https://toyyibpay.com';
    }

    public function createBill(Payment $payment): string
    {
        $secret = config('kotakia.toyyibpay.secret_key');
        $category = config('kotakia.toyyibpay.category_code');

        if (! $secret || ! $category) {
            throw new RuntimeException('TOYYIBPAY_SECRET_KEY / TOYYIBPAY_CATEGORY_CODE belum ditetapkan dalam .env');
        }

        $payment->loadMissing(['user', 'package', 'invitation']);
        $user = $payment->user;

        $response = Http::asForm()->timeout(30)->post($this->baseUrl().'/index.php/api/createBill', [
            'userSecretKey' => $secret,
            'categoryCode' => $category,
            'billName' => Str::limit('Kotakia '.$payment->package->name, 30, ''),
            'billDescription' => Str::limit('Kad jemputan digital '.$payment->invitation->coupleName(), 100, ''),
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => (int) round($payment->amount * 100), // sen
            'billReturnUrl' => route('payment.return'),
            'billCallbackUrl' => route('payment.callback'),
            'billExternalReferenceNo' => $payment->reference,
            'billTo' => $user->name,
            'billEmail' => $user->email,
            'billPhone' => $user->phone ?: '0123456789',
            'billPaymentChannel' => 2, // 0=FPX, 1=Kad, 2=Kedua-dua
            'billChargeToCustomer' => '',
            'billExpiryDays' => 3,
        ]);

        $billCode = $response->json('0.BillCode');

        if (! $response->successful() || ! $billCode) {
            Log::error('ToyyibPay createBill gagal', ['body' => $response->body()]);
            throw new RuntimeException('Gagal mencipta bil ToyyibPay. Sila cuba lagi.');
        }

        $payment->update(['gateway_bill_code' => $billCode]);

        return $this->baseUrl().'/'.$billCode;
    }

    /**
     * Jangan percaya parameter return/callback bulat-bulat — semak semula
     * status sebenar melalui API getBillTransactions.
     */
    public function verify(Payment $payment, Request $request): string
    {
        $response = Http::asForm()->timeout(30)->post($this->baseUrl().'/index.php/api/getBillTransactions', [
            'billCode' => $payment->gateway_bill_code,
        ]);

        $transactions = collect($response->json() ?: []);

        $paid = $transactions->first(fn ($t) => ($t['billpaymentStatus'] ?? null) == '1'
            && (int) round(((float) ($t['billpaymentAmount'] ?? 0)) * 100) === (int) round($payment->amount * 100));

        if ($paid) {
            $payment->gateway_transaction_id = $paid['billpaymentInvoiceNo'] ?? null;

            return 'paid';
        }

        $statusId = (string) $request->input('status_id', $request->input('status', ''));

        return $statusId === '3' ? 'failed' : 'pending';
    }
}
