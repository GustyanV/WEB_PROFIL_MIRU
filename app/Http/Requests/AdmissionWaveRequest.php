<?php
namespace App\Http\Requests;
use App\Models\AdmissionPeriod;
use Illuminate\Foundation\Http\FormRequest;
class AdmissionWaveRequest extends FormRequest {
    public function authorize(): bool { return $this->user() && in_array($this->user()->role,['admin','super_admin'],true); }
    public function rules(): array { return [
        'admission_period_id'=>['required','integer','exists:admission_periods,id'],'name'=>['required','string','max:255'],
        'start_date'=>['required','date'],'end_date'=>['required','date','after_or_equal:start_date'],
        'quota'=>['nullable','integer','min:0','max:2147483647'],'is_active'=>['required','boolean'],
    ]; }
    public function withValidator($validator) { $validator->after(function ($validator) {
        if ($validator->errors()->any()) return;
        $period=AdmissionPeriod::find($this->admission_period_id);
        if ($period && (strtotime($this->start_date)<$period->start_date->startOfDay()->timestamp || strtotime($this->end_date)>$period->end_date->endOfDay()->timestamp))
            $validator->errors()->add('start_date','Tanggal gelombang harus berada dalam rentang periode.');
        $wave=$this->route('wave');
        if ($wave && (int)$wave->admission_period_id !== (int)$this->admission_period_id && $wave->applications()->exists())
            $validator->errors()->add('admission_period_id','Gelombang yang sudah memiliki pendaftar tidak dapat dipindahkan ke periode lain.');
    }); }
    public function messages(): array { return [
        '*.required'=>':attribute wajib diisi.','*.integer'=>':attribute harus bilangan bulat.','*.string'=>':attribute harus berupa teks.',
        '*.max'=>':attribute melebihi batas :max.','*.date'=>':attribute harus tanggal yang valid.',
        'admission_period_id.exists'=>'Periode tidak ditemukan.','end_date.after_or_equal'=>'Tanggal selesai harus sama atau setelah tanggal mulai.',
        'quota.min'=>'Kuota tidak boleh negatif.','is_active.boolean'=>'Status aktif tidak valid.',
    ]; }
    public function attributes(): array { return ['admission_period_id'=>'Periode','name'=>'Nama gelombang','start_date'=>'Tanggal mulai','end_date'=>'Tanggal selesai','is_active'=>'Status aktif']; }
}