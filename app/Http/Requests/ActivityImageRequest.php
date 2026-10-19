<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ActivityImageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() && in_array($this->user()->role,['admin','super_admin'],true); }
    public function rules(): array {
        if ($this->isMethod('post')) return ['images'=>['required','array','min:1','max:20'],'images.*'=>['required','image','mimes:jpg,jpeg,png,webp','max:2048']];
        return ['caption'=>['nullable','string','max:255'],'sort_order'=>['required','integer','min:0','max:2147483647'],'is_cover'=>['required','boolean']];
    }
    public function messages(): array {
        return ['images.required'=>'Pilih foto yang akan diunggah.','images.array'=>'Foto harus berupa daftar file.',
            'images.max'=>'Maksimal 20 foto per unggahan.','images.*.required'=>'File gambar wajib diisi.',
            'images.*.image'=>'File harus berupa gambar.','images.*.mimes'=>'Foto harus JPG, JPEG, PNG, atau WEBP.',
            'images.*.max'=>'Setiap foto maksimal 2 MB.','caption.string'=>'Caption harus berupa teks.','caption.max'=>'Caption maksimal 255 karakter.',
            'sort_order.required'=>'Urutan wajib diisi.','sort_order.integer'=>'Urutan harus bilangan bulat.',
            'sort_order.min'=>'Urutan minimal 0.','sort_order.max'=>'Urutan terlalu besar.',
            'is_cover.required'=>'Status cover wajib diisi.','is_cover.boolean'=>'Status cover tidak valid.'];
    }
}