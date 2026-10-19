<?php
namespace App\Http\Requests;
use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() && in_array($this->user()->role,['admin','super_admin'],true); }
    protected function prepareForValidation() {
        $this->merge(['slug'=>Str::slug($this->input('slug') ?: $this->input('title',''))]);
    }
    public function rules(): array {
        $activity = $this->route('activity');
        return [
            'title'=>['required','string','max:255'],
            'slug'=>['required','string','max:255','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique('activities','slug')->ignore($activity instanceof Activity ? $activity->id : null)],
            'category'=>['required',Rule::in(Activity::CATEGORIES)],
            'activity_date'=>['required','date'],
            'excerpt'=>['nullable','string','max:5000'], 'content'=>['required','string','max:100000'],
            'thumbnail'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'status'=>['required',Rule::in(['draft','published'])], 'published_at'=>['nullable','date'],
        ];
    }
    public function messages(): array {
        return ['*.required'=>':attribute wajib diisi.','*.string'=>':attribute harus berupa teks.',
            '*.max'=>':attribute maksimal :max karakter; gambar maksimal 2048 KB.',
            '*.date'=>':attribute harus berupa tanggal yang valid.',
            'slug.unique'=>'Slug sudah digunakan kegiatan lain.','slug.regex'=>'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'category.in'=>'Kategori tidak tersedia.','status.in'=>'Status harus draft atau published.',
            'thumbnail.image'=>'Thumbnail harus berupa gambar.','thumbnail.mimes'=>'Thumbnail harus JPG, JPEG, PNG, atau WEBP.'];
    }
    public function attributes(): array { return ['title'=>'Judul','category'=>'Kategori','activity_date'=>'Tanggal kegiatan','excerpt'=>'Ringkasan','content'=>'Isi kegiatan','published_at'=>'Tanggal publikasi']; }
}