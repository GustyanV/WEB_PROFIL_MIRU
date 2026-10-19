<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdmissionPeriodRequest;
use App\Models\AdmissionPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class AdminAdmissionPeriodController extends Controller {
    public function index() { return view('admin.admissions.periods.index',['periods'=>AdmissionPeriod::withCount(['waves','applications'])->latest()->paginate(15)]); }
    public function create() { return view('admin.admissions.periods.form',['period'=>new AdmissionPeriod(['is_active'=>false])]); }
    public function edit(AdmissionPeriod $period) { return view('admin.admissions.periods.form',compact('period')); }
    public function store(AdmissionPeriodRequest $request) { $this->save($request,new AdmissionPeriod); return redirect()->route('admin.periods.index')->with('success','Periode berhasil ditambahkan.'); }
    public function update(AdmissionPeriodRequest $request,AdmissionPeriod $period) { $this->save($request,$period); return redirect()->route('admin.periods.index')->with('success','Periode berhasil diperbarui.'); }
    private function save(AdmissionPeriodRequest $request,AdmissionPeriod $period): void {
        DB::transaction(function () use ($request,$period) {
            AdmissionPeriod::orderBy('id')->lockForUpdate()->get();
            if ($request->boolean('is_active')) AdmissionPeriod::where('is_active',true)->update(['is_active'=>false]);
            if ($period->exists) {
                $period->refresh();
                if ($period->waves()->where(function ($q) use ($request) {
                    $q->whereDate('start_date','<',$request->start_date)->orWhereDate('end_date','>',$request->end_date);
                })->exists()) throw ValidationException::withMessages(['start_date'=>'Rentang periode harus mencakup seluruh gelombang yang sudah ada.']);
            }
            $period->fill($request->validated())->save();
        });
    }
    public function destroy(AdmissionPeriod $period) {
        DB::transaction(function () use ($period) {
            $period=AdmissionPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();
            if ($period->applications()->exists() || $period->waves()->whereHas('applications')->exists())
                throw ValidationException::withMessages(['delete'=>'Periode sudah digunakan pendaftar. Nonaktifkan periode, jangan hapus.']);
            $period->delete();
        });
        return back()->with('success','Periode berhasil dihapus.');
    }
}