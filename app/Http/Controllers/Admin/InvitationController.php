<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $invitations = Invitation::with(['user', 'package', 'theme'])->withCount('rsvps')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('groom_nickname', 'like', "%{$term}%")
                ->orWhere('bride_nickname', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"))))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.invitations', ['invitations' => $invitations]);
    }

    public function status(Request $request, Invitation $invitation)
    {
        $request->validate(['action' => ['required', 'in:suspend,activate']]);

        if ($request->input('action') === 'suspend') {
            $invitation->forceFill(['status' => Invitation::STATUS_SUSPENDED])->save();

            return back()->with('success', 'Kad '.$invitation->coupleName().' digantung.');
        }

        $invitation->load('package')->activate();

        return back()->with('success', 'Kad '.$invitation->coupleName().' diaktifkan.');
    }
}
