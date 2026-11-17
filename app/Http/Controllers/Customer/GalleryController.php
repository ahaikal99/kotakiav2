<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $invitation->load(['package', 'gallery']);

        return view('customer.invitations.gallery', ['invitation' => $invitation]);
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorizeOwner($invitation);
        $package = $invitation->package;

        if (! $package->has_gallery) {
            return back()->with('error', 'Pakej '.$package->name.' tidak termasuk galeri foto. Naik taraf pakej untuk menggunakan ciri ini.');
        }

        $remaining = $package->max_gallery - $invitation->gallery()->count();

        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:'.max($remaining, 1)],
            'images.*' => ['image', 'max:'.config('kotakia.uploads.image_max_kb')],
        ], [
            'images.max' => 'Baki slot galeri anda hanya '.max($remaining, 0).' gambar.',
        ], ['images' => 'gambar', 'images.*' => 'gambar']);

        if ($remaining <= 0) {
            return back()->with('error', 'Had galeri ('.$package->max_gallery.' gambar) telah dicapai.');
        }

        $order = (int) $invitation->gallery()->max('sort_order');
        foreach ($request->file('images') as $file) {
            $invitation->gallery()->create([
                'path' => $file->store('invitations/'.$invitation->id.'/gallery', 'public'),
                'sort_order' => ++$order,
            ]);
        }

        return back()->with('success', count($request->file('images')).' gambar berjaya dimuat naik.');
    }

    public function destroy(Invitation $invitation, GalleryImage $image)
    {
        $this->authorizeOwner($invitation);
        abort_unless((int) $image->invitation_id === (int) $invitation->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Gambar telah dipadam.');
    }
}
