<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Rsvp;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $paid = Payment::where('status', Payment::STATUS_PAID);

        // Hasil 6 bulan terakhir (dikira dalam PHP supaya serasi semua DB)
        $monthly = collect(range(5, 0))->map(function ($i) {
            $month = now()->startOfMonth()->subMonths($i);

            return [
                'label' => $month->locale('ms')->translatedFormat('M Y'),
                'total' => (float) Payment::where('status', Payment::STATUS_PAID)
                    ->whereBetween('paid_at', [$month, $month->copy()->endOfMonth()])->sum('amount'),
            ];
        });

        return view('admin.dashboard', [
            'stats' => [
                'revenue' => (float) (clone $paid)->sum('amount'),
                'revenue_month' => (float) (clone $paid)->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
                'orders' => (clone $paid)->count(),
                'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'active' => Invitation::where('status', Invitation::STATUS_ACTIVE)->where('expires_at', '>', now())->count(),
                'drafts' => Invitation::where('status', Invitation::STATUS_DRAFT)->count(),
                'rsvps' => Rsvp::count(),
            ],
            'monthly' => $monthly,
            'recentPayments' => Payment::with(['user', 'package', 'invitation'])->latest()->limit(8)->get(),
            'upcoming' => Invitation::with('user')->where('status', Invitation::STATUS_ACTIVE)
                ->whereDate('event_date', '>=', today())->orderBy('event_date')->limit(6)->get(),
        ]);
    }
}
