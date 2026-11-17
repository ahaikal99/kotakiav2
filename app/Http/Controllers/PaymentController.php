<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(protected PaymentManager $payments) {}

    public function checkout(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);

        if ($invitation->isActive()) {
            return redirect()->route('invitations.show', $invitation)->with('info', 'Kad ini sudah aktif.');
        }

        $package = $invitation->package;
        $gateway = $this->payments->gateway();

        $payment = Payment::create([
            'reference' => Payment::newReference(),
            'user_id' => $invitation->user_id,
            'invitation_id' => $invitation->id,
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => Payment::STATUS_PENDING,
            'gateway' => $gateway->name(),
        ]);

        if ((float) $package->price <= 0) {
            $payment->markAsPaid('PERCUMA');

            return redirect()->route('invitations.show', $invitation)->with('paid', true);
        }

        try {
            return redirect()->away($gateway->createBill($payment));
        } catch (Throwable $e) {
            Log::error('Checkout gagal', ['payment' => $payment->reference, 'error' => $e->getMessage()]);
            $payment->markAsFailed(['error' => $e->getMessage()]);

            return back()->with('error', $e->getMessage());
        }
    }

    /** Halaman "bank" palsu untuk mod development. */
    public function simulator(Payment $payment)
    {
        abort_unless($payment->gateway === 'simulasi', 404);

        $payment->load(['invitation', 'package', 'user']);
        $link = fn (string $result) => URL::temporarySignedRoute('payment.return', now()->addHour(), [
            'ref' => $payment->reference, 'result' => $result,
        ]);

        return view('payment.simulator', [
            'payment' => $payment,
            'successUrl' => $link('success'),
            'failUrl' => $link('fail'),
        ]);
    }

    /** Pelanggan kembali dari gateway (browser redirect). */
    public function return(Request $request)
    {
        $reference = $request->query('ref', $request->query('order_id'));
        $payment = Payment::with('invitation.package')->where('reference', $reference)->firstOrFail();

        if (! $payment->isPaid()) {
            $this->process($payment, $request);
        }

        $route = auth()->check()
            ? route('invitations.show', $payment->invitation_id)
            : route('login');

        return match ($payment->fresh()->status) {
            Payment::STATUS_PAID => redirect($route)->with('paid', true),
            Payment::STATUS_FAILED => redirect($route)->with('error', 'Pembayaran tidak berjaya. Sila cuba lagi.'),
            default => redirect($route)->with('info', 'Pembayaran sedang diproses. Status akan dikemas kini secara automatik.'),
        };
    }

    /** Callback server-ke-server (ToyyibPay). */
    public function callback(Request $request)
    {
        $payment = Payment::where('reference', $request->input('order_id'))
            ->where('gateway_bill_code', $request->input('billcode'))
            ->first();

        if ($payment && ! $payment->isPaid()) {
            $this->process($payment, $request);
        }

        return response('OK');
    }

    protected function process(Payment $payment, Request $request): void
    {
        try {
            $status = $this->payments->gateway($payment->gateway)->verify($payment, $request);
        } catch (Throwable $e) {
            Log::error('Pengesahan bayaran gagal', ['payment' => $payment->reference, 'error' => $e->getMessage()]);

            return;
        }

        $payload = $request->except(['signature', 'expires']);

        if ($status === 'paid') {
            $payment->markAsPaid($payment->gateway_transaction_id, $payload);
        } elseif ($status === 'failed') {
            $payment->markAsFailed($payload);
        }
    }
}
