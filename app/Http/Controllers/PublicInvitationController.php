<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;

class PublicInvitationController extends Controller
{
    protected function findPublished(string $slug): Invitation
    {
        $invitation = Invitation::with(['theme', 'package', 'gallery'])->where('slug', $slug)->firstOrFail();

        if (! $invitation->isActive()) {
            $owner = auth()->check() && ((int) auth()->id() === (int) $invitation->user_id || auth()->user()->isAdmin());
            abort_unless($owner, 404);
        }

        return $invitation;
    }

    public function show(Request $request, string $slug)
    {
        $invitation = $this->findPublished($slug);

        // Kira paparan unik setiap sesi
        $key = 'viewed_'.$invitation->id;
        if ($invitation->isActive() && ! $request->session()->has($key)) {
            $invitation->increment('views');
            $request->session()->put($key, true);
        }

        return view('invitation.show', [
            'invitation' => $invitation,
            'theme' => $invitation->theme,
            'guest' => $request->query('to'),
            'wishes' => $invitation->feature('wishes')
                ? $invitation->rsvps()->where('wish_visible', true)->whereNotNull('wish')->where('wish', '!=', '')->limit(100)->get()
                : collect(),
            'mode' => $invitation->isActive() ? 'live' : 'preview',
        ]);
    }

    public function rsvp(Request $request, string $slug)
    {
        $invitation = $this->findPublished($slug);

        if (! $invitation->isActive() || ! $invitation->rsvpOpen()) {
            return back()->with('rsvp_error', 'Maaf, RSVP untuk majlis ini telah ditutup.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'attendance' => ['required', 'in:hadir,tidak'],
            'pax' => ['required_if:attendance,hadir', 'nullable', 'integer', 'min:1', 'max:'.$invitation->max_pax_per_rsvp],
            'wish' => ['nullable', 'string', 'max:500'],
        ], [], ['name' => 'nama', 'attendance' => 'kehadiran', 'pax' => 'bilangan tetamu', 'wish' => 'ucapan']);

        if (! $invitation->package->has_wishes) {
            $data['wish'] = null;
        }
        $data['pax'] = $data['attendance'] === 'hadir' ? (int) $data['pax'] : 0;

        $invitation->rsvps()->create($data + ['ip_address' => $request->ip()]);

        return redirect()->to(url()->previous().'#rsvp')
            ->with('rsvp_success', $data['attendance'] === 'hadir'
                ? 'Terima kasih! Kehadiran anda telah direkodkan. Jumpa di majlis nanti 🤍'
                : 'Terima kasih atas maklum balas dan doa anda 🤍');
    }

    public function calendar(string $slug)
    {
        $invitation = $this->findPublished($slug);
        $fmt = fn ($dt) => $dt->copy()->utc()->format('Ymd\THis\Z');
        $esc = fn ($s) => addcslashes((string) $s, ",;\\");

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//eCard Kotakia//MS', 'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:kotakia-'.$invitation->id.'@'.request()->getHost(),
            'DTSTAMP:'.$fmt(now()),
            'DTSTART:'.$fmt($invitation->eventStart()),
            'DTEND:'.$fmt($invitation->eventEnd()),
            'SUMMARY:'.$esc($invitation->event_title.' '.$invitation->coupleName()),
            'LOCATION:'.$esc(trim($invitation->venue_name.', '.$invitation->venue_address, ', ')),
            'DESCRIPTION:'.$esc('Kad jemputan: '.$invitation->url()),
            'END:VEVENT', 'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="majlis-'.$invitation->slug.'.ics"',
        ]);
    }
}
