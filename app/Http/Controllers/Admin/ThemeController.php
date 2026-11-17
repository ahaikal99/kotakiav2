<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        return view('admin.themes', [
            'themes' => Theme::withCount('invitations')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Theme $theme)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'bg' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $theme->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'palette' => array_merge($theme->palette, collect($data)->only(['primary', 'accent', 'bg'])->all()),
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Tema "'.$theme->name.'" dikemas kini.');
    }
}
