<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class AdminUserRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->role === 'super_admin'; }
    public function rules(): array {
        return ['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users')->ignore($this->route('user')?->id)],
            'phone'=>['nullable','string','max:30'],'is_active'=>['required','boolean'],'role'=>['prohibited'],
            'password'=>[$this->isMethod('post')?'required':'prohibited','confirmed','string','min:8']];
    }
    public function messages(): array { return [
        '*.required'=>':attribute wajib diisi.','*.string'=>':attribute harus berupa teks.','*.max'=>':attribute maksimal :max karakter.',
        'email.email'=>'Email tidak valid.','email.unique'=>'Email sudah digunakan.',
        'password.min'=>'Password minimal 8 karakter.','password.confirmed'=>'Konfirmasi password tidak cocok.',
        'password.prohibited'=>'Gunakan aksi Reset Password untuk mengganti password.',
        'role.prohibited'=>'Role tidak boleh dikirim atau diubah.','is_active.boolean'=>'Status aktif tidak valid.',
    ]; }
    public function attributes(): array { return ['name'=>'Nama','phone'=>'WhatsApp','email'=>'Email','password'=>'Password','is_active'=>'Status aktif']; }
}