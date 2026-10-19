<?php
namespace App\Http\Requests;

use App\Rules\GoogleMapsEmbedUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255', 'regex:/^\\+?[0-9() .-]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_hours' => ['nullable', 'string', 'max:2000'],
            'google_maps_embed_url' => ['nullable', 'url', 'max:10000', new GoogleMapsEmbedUrl],
        ];
    }
    public function messages(): array
    {
        return [
            '*.string' => ':attribute harus berupa teks.',
            '*.max' => ':attribute maksimal :max karakter.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'whatsapp.regex' => 'WhatsApp harus berupa nomor telepon yang valid.',
            'google_maps_embed_url.url' => 'URL Google Maps harus berupa URL yang valid.',
        ];
    }
    public function attributes(): array
    {
        return ['address'=>'Alamat', 'phone'=>'Telepon', 'whatsapp'=>'WhatsApp', 'email'=>'Email', 'service_hours'=>'Jam layanan', 'google_maps_embed_url'=>'URL embed Google Maps'];
    }
}