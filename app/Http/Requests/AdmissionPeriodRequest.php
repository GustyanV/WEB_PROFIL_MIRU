<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AdmissionPeriodRequest extends FormRequest {
    public function authorize(): bool { return $this->user() && in_array($this->user()->role,['admin','super_admin'],true); }
    public function rules(): array { return [
        'name'=>['required','string','max:255'],'academic_year'=>['required','string','max:20','regex:~^\\d{4}/\\d{4}$~'],
        'start_date'=>['required','date'],'end_date'=>['required','date','after_or_equal:start_date'],
        'quota'=>['nullable','integer','min:1','max:2147483647'],'is_active'=>['required','boolean'],
    ]; }
    public function withValidator($validator) { $validator->after(function ($validator) {
        if ($validator->errors()->any()) return;
        $period=$this->route('period');
        if ($period && $period->waves()->where(function ($q) { $q->whereDate('start_date','<',$this->start_date)->orWhereDate('end_date','>',$this->end_date); })->exists())
            $validator->errors()->add('start_date','Rentang periode harus mencakup seluruh gelombang yang sudah ada.');
    }); }
    public function messages(): array { return [
        '*.required'=>':attribute wajib diisi.','*.string'=>':attribute harus berupa teks.','*.max'=>':attribute melebihi batas :max.',
        '*.date'=>':attribute harus tanggal yang valid.','end_date.after_or_equal'=>'Tanggal selesai harus sama atau setelah tanggal mulai.',
        'academic_year.regex'=>'Gunakan format tahun pelajaran 2027/2028.','quota.integer'=>'Kuota harus bilangan bulat.','quota.min'=>'Kuota minimal 1.',
        'is_active.boolean'=>'Status aktif tidak valid.',
    ]; }
    public function attributes(): array { return ['name'=>'Nama periode','academic_year'=>'Tahun pelajaran','start_date'=>'Tanggal mulai','end_date'=>'Tanggal selesai','is_active'=>'Status aktif']; }
}