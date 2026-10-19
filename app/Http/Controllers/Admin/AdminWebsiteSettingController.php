<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteSettingRequest;
use App\Models\SchoolProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class AdminWebsiteSettingController extends Controller {
    public function edit() { return view('admin.settings.edit',['profile'=>SchoolProfile::orderBy('id')->first() ?? new SchoolProfile]); }
    public function update(WebsiteSettingRequest $request) {
        $profile=SchoolProfile::orderBy('id')->first() ?? new SchoolProfile;
        $data=$request->validated(); unset($data['logo'],$data['favicon']);
        $uploaded=[]; $old=[];
        try {
            foreach (['logo','favicon'] as $field) if ($request->hasFile($field)) {
                $path=$request->file($field)->store('site','public');
                if (!$path) throw ValidationException::withMessages([$field=>'Upload gagal. Silakan coba kembali.']);
                $uploaded[]=$path; $old[]=$profile->$field; $data[$field]=$path;
            }
            $profile->fill($data)->save();
        } catch (\Throwable $e) { Storage::disk('public')->delete($uploaded); throw $e; }
        foreach ($old as $path) if ($path && str_starts_with($path,'site/') && !str_contains($path,'..') && !SchoolProfile::where('logo',$path)->orWhere('favicon',$path)->exists()) Storage::disk('public')->delete($path);
        return back()->with('success','Pengaturan website berhasil diperbarui.');
    }
}