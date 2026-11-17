<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'package', 'invitation'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('reference', 'like', "%{$term}%")
                ->orWhere('gateway_bill_code', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"))))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.payments', [
            'payments' => $payments,
            'total' => (float) Payment::where('status', Payment::STATUS_PAID)->sum('amount'),
        ]);
    }

    /** Pengesahan manual (cth. bayaran melalui pindahan bank terus). */
    public function markPaid(Request $request, Payment $payment)
    {
        $payment->markAsPaid('MANUAL-'.$request->user()->id, ['manual_by' => $request->user()->email, 'manual_at' => now()->toDateTimeString()]);

        return back()->with('success', 'Bayaran '.$payment->reference.' disahkan & kad diaktifkan.');
    }
}
