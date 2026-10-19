<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSocialMediaRequest;
use App\Models\SocialMedia;

class AdminSocialMediaController extends Controller
{
    public function index()
    {
        $items = SocialMedia::orderBy('sort_order')->orderBy('id')->paginate(20);
        return view('admin.social-media.index', compact('items'));
    }
    public function create() { return view('admin.social-media.create', ['socialMedia'=>new SocialMedia(['is_active'=>true, 'sort_order'=>0])]); }
    public function store(SaveSocialMediaRequest $request)
    {
        SocialMedia::create($request->validated());
        return redirect()->route('admin.social-media.index')->with('success', 'Media sosial berhasil ditambahkan.');
    }
    public function edit(SocialMedia $socialMedia) { return view('admin.social-media.edit', compact('socialMedia')); }
    public function update(SaveSocialMediaRequest $request, SocialMedia $socialMedia)
    {
        $socialMedia->update($request->validated());
        return redirect()->route('admin.social-media.index')->with('success', 'Media sosial berhasil diperbarui.');
    }
    public function destroy(SocialMedia $socialMedia)
    {
        $socialMedia->delete();
        return redirect()->route('admin.social-media.index')->with('success', 'Media sosial berhasil dihapus.');
    }
}