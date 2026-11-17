<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function index(Request $request, Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $invitation->load('package');

        $rsvps = $invitation->rsvps()
            ->when($request->query('status'), fn ($q, $s) => $q->where('attendance', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")))
            ->paginate(25)->withQueryString();

        return view('customer.invitations.rsvp', [
            'invitation' => $invitation,
            'rsvps' => $rsvps,
            'stats' => $invitation->rsvpStats(),
        ]);
    }

    public function export(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);

        $filename = 'rsvp-'.$invitation->slug.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($invitation) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel baca UTF-8
            fputcsv($out, ['Bil', 'Nama', 'No. Telefon', 'Kehadiran', 'Bilangan Pax', 'Ucapan', 'Tarikh'], escape: '');
            $i = 0;
            $invitation->rsvps()->reorder('id')->chunk(500, function ($rows) use ($out, &$i) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        ++$i, $r->name, $r->phone, $r->attending() ? 'Hadir' : 'Tidak Hadir',
                        $r->pax, $r->wish, $r->created_at->format('d/m/Y H:i'),
                    ], escape: '');
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function toggleWish(Invitation $invitation, Rsvp $rsvp)
    {
        $this->authorizeOwner($invitation);
        abort_unless((int) $rsvp->invitation_id === (int) $invitation->id, 404);

        $rsvp->update(['wish_visible' => ! $rsvp->wish_visible]);

        return back()->with('success', $rsvp->wish_visible ? 'Ucapan dipaparkan semula.' : 'Ucapan disembunyikan dari kad.');
    }

    public function destroy(Invitation $invitation, Rsvp $rsvp)
    {
        $this->authorizeOwner($invitation);
        abort_unless((int) $rsvp->invitation_id === (int) $invitation->id, 404);

        $rsvp->delete();

        return back()->with('success', 'Respons RSVP telah dipadam.');
    }
}
