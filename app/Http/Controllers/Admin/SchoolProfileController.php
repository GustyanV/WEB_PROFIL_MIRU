<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SchoolProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SchoolProfileController extends Controller
{
    public function edit() { return view('admin.profile',['profile'=>SchoolProfile::orderBy('id')->first() ?? new SchoolProfile]); }
    public function logoSource() {
        $bytes=app(\App\Support\PdfLogo::class)->source(SchoolProfile::orderBy('id')->first());
        return response($bytes)->header('Content-Type',(new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes))->header('Cache-Control','private, no-store')->header('X-Content-Type-Options','nosniff');
    }
    public function update(Request $r) {
        $rules=[
            'name'=>'required|string|max:255','nsm'=>'nullable|string|max:50','npsn'=>'nullable|string|max:50',
            'address'=>'nullable|string|max:5000','phone'=>'nullable|string|max:255','whatsapp'=>'nullable|string|max:255',
            'email'=>'nullable|email|max:255','website'=>'nullable|url|max:255','headmaster_name'=>'nullable|string|max:255',
            'logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'pdf_logo'=>'nullable|file|mimetypes:image/jpeg|mimes:jpg,jpeg|max:2048',
            'pdf_logo_hash'=>'nullable|string|max:64',
            'pdf_logo_approved'=>'sometimes|accepted',
            'favicon'=>'nullable|image|mimes:png|max:1024',
            'headmaster_photo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
        foreach(['about','history','vision','mission','goals','education_commitment','headmaster_message'] as $field) $rules[$field]='nullable|string|max:50000';
        $data=$r->validate($rules,['*.max'=>':attribute melebihi batas yang diizinkan.','*.image'=>'File harus berupa gambar.','*.mimes'=>'Format gambar tidak didukung.']);
        $profile=SchoolProfile::orderBy('id')->first() ?? new SchoolProfile;
        $pdfLogo=app(\App\Support\PdfLogo::class);
        [$original,$pdfBytes]=$pdfLogo->validateUpload($r,$profile);
        unset($data['pdf_logo'],$data['pdf_logo_hash'],$data['pdf_logo_approved']);
        $uploaded=[]; $old=[];
        foreach(['logo','favicon','headmaster_photo'] as $field) unset($data[$field]);
        try {
            foreach(['logo','favicon','headmaster_photo'] as $field) if($r->hasFile($field)) {
                $path=$r->file($field)->store('school','public');
                if(!$path) throw ValidationException::withMessages([$field=>'Gambar gagal disimpan.']);
                $uploaded[]=$path; if ($field!=='logo') $old[]=$profile->$field; $data[$field]=$path;
            }
            if ($pdfBytes) $pdfLogo->storeDerivative($original,$pdfBytes); // Shared hash objects must not be deleted on a concurrent request rollback.
            $profile->fill($data)->save();
        } catch(\Throwable $e) { Storage::disk('public')->delete($uploaded); throw $e; }
        foreach($old as $path) if($path && preg_match('~^(school|site)/~',$path) && !str_contains($path,'..') && !SchoolProfile::where('logo',$path)->orWhere('favicon',$path)->orWhere('headmaster_photo',$path)->exists()) Storage::disk('public')->delete($path);
        return back()->with('success','Profil madrasah berhasil diperbarui.');
    }
}
