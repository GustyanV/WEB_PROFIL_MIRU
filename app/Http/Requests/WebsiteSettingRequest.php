<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class WebsiteSettingRequest extends FormRequest {
    public function authorize(): bool { return $this->user() && in_array($this->user()->role,['admin','super_admin'],true); }
    public function rules(): array { return [
        'name'=>['required','string','max:255'],'tagline'=>['nullable','string','max:255'],
        'footer_text'=>['nullable','string','max:5000'],'copyright_text'=>['nullable','string','max:500'],
        'logo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        'favicon'=>['nullable','file','mimes:png,ico','max:1024',function($attribute,$value,$fail) {
            $extension=strtolower($value->getClientOriginalExtension());
            $bytes=file_get_contents($value->getRealPath());
            if (($extension==='png' && !str_starts_with($bytes,"\x89PNG\r\n\x1a\n")) || ($extension==='ico' && !str_starts_with($bytes,"\x00\x00\x01\x00")) || !in_array($extension,['png','ico'],true))
                $fail('Favicon harus berupa file PNG atau ICO yang valid.');
        }],
        'primary_color'=>['nullable','regex:/^#[0-9a-fA-F]{6}$/'],'accent_color'=>['nullable','regex:/^#[0-9a-fA-F]{6}$/'],
    ]; }
    public function messages(): array { return [
        'name.required'=>'Nama website wajib diisi.','*.string'=>':attribute harus berupa teks.','*.max'=>':attribute melebihi batas yang diizinkan (:max).',
        'logo.image'=>'Logo harus berupa gambar.','logo.mimes'=>'Logo harus JPG, JPEG, PNG, atau WEBP.',
        'favicon.file'=>'Favicon harus berupa file.','favicon.mimes'=>'Favicon harus PNG atau ICO.',
        '*.regex'=>'Warna harus berupa hex enam digit, misalnya #0F8B83.',
    ]; }
}