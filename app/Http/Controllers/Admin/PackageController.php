<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index()
    {
        return view('admin.packages.index', [
            'packages' => Package::withCount('invitations')->orderBy('sort_order')->orderBy('price')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.packages.form', ['package' => new Package([
            'has_rsvp' => true, 'has_wishes' => true, 'has_countdown' => true, 'active_days' => 90, 'is_active' => true,
        ])]);
    }

    public function store(Request $request)
    {
        Package::create($this->validated($request));

        return redirect()->route('admin.packages.index')->with('success', 'Pakej berjaya ditambah.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', ['package' => $package]);
    }

    public function update(Request $request, Package $package)
    {
        $package->update($this->validated($request, $package));

        return redirect()->route('admin.packages.index')->with('success', 'Pakej berjaya dikemas kini.');
    }

    public function destroy(Package $package)
    {
        if ($package->invitations()->exists()) {
            $package->update(['is_active' => false]);

            return back()->with('info', 'Pakej telah digunakan oleh pelanggan — ia dinyahaktifkan, bukan dipadam.');
        }

        $package->delete();

        return back()->with('success', 'Pakej dipadam.');
    }

    protected function validated(Request $request, ?Package $package = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['nullable', 'alpha_dash:ascii', 'max:60', Rule::unique('packages', 'slug')->ignore($package?->id)],
            'tagline' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'max_gallery' => ['required', 'integer', 'min:0', 'max:200'],
            'max_rsvp' => ['nullable', 'integer', 'min:1'],
            'active_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach (['has_music', 'has_gallery', 'has_video', 'has_rsvp', 'has_wishes', 'has_countdown', 'has_gift', 'premium_themes', 'is_popular', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
