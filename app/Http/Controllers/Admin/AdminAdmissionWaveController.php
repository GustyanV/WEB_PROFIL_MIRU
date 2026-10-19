<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdmissionWaveRequest;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionWave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class AdminAdmissionWaveController extends Controller {
    public function index(Request $request) {
        $request->validate(['period'=>'nullable|integer|exists:admission_periods,id']);
        return view('admin.admissions.waves.index',['waves'=>AdmissionWave::with('period')->withCount('applications')->when($request->period,fn($q,$id)=>$q->where('admission_period_id',$id))->latest()->paginate(15)->withQueryString(),'periods'=>AdmissionPeriod::orderByDesc('start_date')->get()]);
    }
    public function create() { return $this->form(new AdmissionWave(['is_active'=>false])); }
    public function edit(AdmissionWave $wave) { return $this->form($wave); }
    private function form(AdmissionWave $wave) { return view('admin.admissions.waves.form',['wave'=>$wave,'periods'=>AdmissionPeriod::orderByDesc('start_date')->get()]); }
    public function store(AdmissionWaveRequest $request) { $this->save($request,new AdmissionWave); return redirect()->route('admin.waves.index')->with('success','Gelombang berhasil ditambahkan.'); }
    public function update(AdmissionWaveRequest $request,AdmissionWave $wave) { $this->save($request,$wave); return redirect()->route('admin.waves.index')->with('success','Gelombang berhasil diperbarui.'); }
    private function save(AdmissionWaveRequest $request, AdmissionWave $wave): void {
        DB::transaction(function () use ($request,$wave) {
            $period=AdmissionPeriod::whereKey($request->admission_period_id)->lockForUpdate()->firstOrFail();
            $data=$request->validated();
            if (strtotime($data['start_date'])<$period->start_date->startOfDay()->timestamp || strtotime($data['end_date'])>$period->end_date->endOfDay()->timestamp)
                throw ValidationException::withMessages(['start_date'=>'Tanggal gelombang harus berada dalam rentang periode.']);
            if ($wave->exists) {
                $wave=AdmissionWave::whereKey($wave->id)->lockForUpdate()->firstOrFail();
                if ((int)$wave->admission_period_id !== (int)$period->id && $wave->applications()->exists())
                    throw ValidationException::withMessages(['admission_period_id'=>'Gelombang yang memiliki pendaftar tidak dapat dipindahkan.']);
            }
            $wave->fill($data)->save();
        });
    }
    public function destroy(AdmissionWave $wave) {
        DB::transaction(function () use ($wave) {
            $wave=AdmissionWave::whereKey($wave->id)->lockForUpdate()->firstOrFail();
            if ($wave->applications()->exists()) throw ValidationException::withMessages(['delete'=>'Gelombang sudah digunakan pendaftar. Nonaktifkan gelombang, jangan hapus.']);
            $wave->delete();
        });
        return back()->with('success','Gelombang berhasil dihapus.');
    }
}