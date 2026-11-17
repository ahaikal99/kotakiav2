<?php

namespace App\Http\Controllers;

use App\Models\Invitation;

abstract class Controller
{
    /** Pastikan kad milik pengguna semasa (admin dibenarkan). */
    protected function authorizeOwner(Invitation $invitation): void
    {
        $user = auth()->user();
        abort_unless($user && ((int) $invitation->user_id === (int) $user->id || $user->isAdmin()), 403);
    }
}
