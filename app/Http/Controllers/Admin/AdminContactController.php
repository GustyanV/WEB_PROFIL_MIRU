<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactRequest;
use App\Models\SchoolProfile;

class AdminContactController extends Controller
{
    public function edit()
    {
        $profile = SchoolProfile::orderBy('id')->first() ?? new SchoolProfile;
        return view('admin.contact.edit', compact('profile'));
    }
    public function update(UpdateContactRequest $request)
    {
        $profile = SchoolProfile::orderBy('id')->first() ?? new SchoolProfile(['name'=>'MI Raudlatul Ulum Jabung Wetan']);
        $profile->fill($request->validated())->save();
        return redirect()->route('admin.contact.edit')->with('success', 'Kontak berhasil diperbarui.');
    }
}