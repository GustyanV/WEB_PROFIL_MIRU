<?php
namespace App\Http\Requests;

use App\Models\SocialMedia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSocialMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(array_keys(SocialMedia::PLATFORMS))],
            'url' => ['nullable', 'url', 'regex:~^https?://~i', 'max:2048'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
    public function messages(): array
    {
        return [
            'platform.required'=>'Platform wajib dipilih.', 'platform.in'=>'Platform tidak didukung.',
            'url.url'=>'URL media sosial harus valid.', 'url.regex'=>'URL harus menggunakan http atau https.',
            'url.max'=>'URL maksimal 2048 karakter.',
            'is_active.required'=>'Status aktif wajib diisi.', 'is_active.boolean'=>'Status aktif tidak valid.',
            'sort_order.required'=>'Urutan wajib diisi.', 'sort_order.integer'=>'Urutan harus berupa bilangan bulat.',
            'sort_order.min'=>'Urutan minimal 0.', 'sort_order.max'=>'Urutan terlalu besar.',
        ];
    }
}