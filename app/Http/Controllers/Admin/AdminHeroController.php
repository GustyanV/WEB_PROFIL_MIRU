<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminHeroController extends Controller
{
    public function edit() {
        return view('admin.hero.edit',['hero'=>HeroBanner::orderBy('id')->first() ?? new HeroBanner(['is_active'=>true])]);
    }
    public function update(Request $request) {
        $safeUrl=function($attribute,$value,$fail) {
            if (!preg_match('~^(https?://[^\s]+|/(?!/)[^\s]*|\#[^\s]*)$~i',$value)) $fail('URL harus berupa http(s), path lokal (/...), atau anchor (#...).');
        };
        $data=$request->validate([
            'title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:5000'],
            'small_text'=>['nullable','string','max:255'],'academic_year'=>['nullable','string','max:255'],
            'primary_button_label'=>['nullable','string','max:255'],'secondary_button_label'=>['nullable','string','max:255'],
            'primary_button_url'=>['nullable','string','max:255',$safeUrl],'secondary_button_url'=>['nullable','string','max:255',$safeUrl],
            'banner_desktop'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'banner_mobile'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'remove_mobile'=>['nullable','boolean'],'is_active'=>['required','boolean'],
        ],[
            '*.image'=>'File banner harus berupa gambar.','*.mimes'=>'Gunakan JPG, JPEG, PNG, atau WEBP.',
            'banner_desktop.max'=>'Banner desktop maksimal 2 MB.','banner_mobile.max'=>'Banner mobile maksimal 2 MB.',
            'title.required'=>'Judul wajib diisi.','*.boolean'=>'Pilihan aktif/hapus tidak valid.',
        ]);
        $hero=HeroBanner::orderBy('id')->first() ?? new HeroBanner;
        $uploaded=[]; $replaced=[];
        unset($data['banner_desktop'],$data['banner_mobile'],$data['remove_mobile']);
        try {
            foreach(['banner_desktop','banner_mobile'] as $field) {
                if($request->hasFile($field)) {
                    $path=$request->file($field)->store('hero','public');
                    if(!$path) throw ValidationException::withMessages([$field=>'Banner gagal disimpan. Silakan coba kembali.']);
                    $uploaded[]=$path; $replaced[]=$hero->$field; $data[$field]=$path;
                }
            }
            if($request->boolean('remove_mobile') && !$request->hasFile('banner_mobile')) {
                $replaced[]=$hero->banner_mobile; $data['banner_mobile']=null;
            }
            $data['is_active']=$request->boolean('is_active');
            $hero->fill($data)->save();
        } catch(\Throwable $e) { Storage::disk('public')->delete($uploaded); throw $e; }
        foreach(array_unique($replaced) as $path) {
            if($path && str_starts_with($path,'hero/') && !str_contains($path,'..')
                && !HeroBanner::where('banner_desktop',$path)->orWhere('banner_mobile',$path)->orWhere('image',$path)->orWhere('background_image',$path)->exists()) Storage::disk('public')->delete($path);
        }
        return redirect()->route('admin.hero.edit')->with('success','Hero / Banner berhasil diperbarui.');
    }
}
