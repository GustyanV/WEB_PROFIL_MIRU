<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ResetAdminPasswordRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->role === 'super_admin'; }
    public function rules(): array { return ['password'=>['required','string','min:8','confirmed'],'role'=>['prohibited']]; }
    public function messages(): array { return ['password.required'=>'Password baru wajib diisi.','password.string'=>'Password harus berupa teks.','password.min'=>'Password minimal 8 karakter.','password.confirmed'=>'Konfirmasi password tidak cocok.','role.prohibited'=>'Role tidak boleh diubah.']; }
}