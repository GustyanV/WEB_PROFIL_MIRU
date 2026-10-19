<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{StudentApplication,AdmissionPeriod,AdmissionWave};
use Illuminate\Http\Request;
class AdminReportController extends Controller {
    private function query(Request $request) {
        $request->validate(['period'=>'nullable|integer|exists:admission_periods,id','wave'=>'nullable|integer|exists:admission_waves,id','status'=>'nullable|in:draft,submitted,under_review,revision,verified,accepted,rejected']);
        return StudentApplication::with(['user','studentProfile','period','wave'])
            ->when($request->period,fn($q,$id)=>$q->where('admission_period_id',$id))
            ->when($request->wave,fn($q,$id)=>$q->where('admission_wave_id',$id))
            ->when($request->status,fn($q,$s)=>$q->where('status',$s));
    }
    public function index(Request $request) {
        $query=$this->query($request);
        return view('admin.reports.index',['items'=>$query->latest()->paginate(20)->withQueryString(),'total'=>(clone $query)->count(),'periods'=>AdmissionPeriod::all(),'waves'=>AdmissionWave::all()]);
    }
    public function export(Request $request) {
        $query=$this->query($request);
        return response()->streamDownload(function () use ($query) {
            $file=fopen('php://output','w'); fwrite($file,"\xEF\xBB\xBF");
            fputcsv($file,['Nomor Pendaftaran','Nama Siswa','Nama Wali','Email','Periode','Gelombang','Status']);
            foreach ($query->orderBy('id')->lazyById(200) as $a) {
                $row=[$a->registration_number,$a->studentProfile?->full_name,$a->user?->name,$a->user?->email,$a->period?->name,$a->wave?->name,$a->status];
                fputcsv($file,array_map(fn($v)=>preg_match('/^[=+@\\-\\t\\r]/',(string)$v) ? "'".$v : $v,$row));
            }
            fclose($file);
        },'laporan-spmb-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}