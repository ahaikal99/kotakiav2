<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Rsvp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $invitations = $user->invitations()->with(['package', 'theme'])->withCount('rsvps')->latest()->get();
        $ids = $invitations->pluck('id');

        return view('customer.dashboard', [
            'invitations' => $invitations,
            'stats' => [
                'total' => $invitations->count(),
                'active' => $invitations->filter->isActive()->count(),
                'views' => (int) $invitations->sum('views'),
                'responses' => Rsvp::whereIn('invitation_id', $ids)->count(),
                'pax' => (int) Rsvp::whereIn('invitation_id', $ids)->where('attendance', 'hadir')->sum('pax'),
            ],
            'latestRsvps' => Rsvp::with('invitation')->whereIn('invitation_id', $ids)->latest()->limit(6)->get(),
        ]);
    }

    public function payments(Request $request)
    {
        return view('customer.payments', [
            'payments' => $request->user()->payments()->with(['invitation', 'package'])->latest()->paginate(15),
        ]);
    }
}
