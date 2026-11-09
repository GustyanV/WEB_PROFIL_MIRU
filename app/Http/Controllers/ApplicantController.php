<?php
namespace App\Http\Controllers; use App\Models\{AdmissionPeriod,AdmissionWave,ApplicationDocument,ApplicationStatusHistory,ParentProfile,StudentApplication,StudentProfile}; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Storage;
class ApplicantController extends Controller { private function app(Request $r){return StudentApplication::firstOrCreate(['user_id'=>$r->user()->id],['status'=>'draft']);} private function editable($a){abort_unless(in_array($a->status,['draft','revision']),403,'Data pendaftaran sudah terkunci.');}
 public function dashboard(Request $r){$app=$this->app($r);return view('dashboard.index',compact('app'));} public function biodata(Request $r){$app=$this->app($r);return view('dashboard.biodata',['app'=>$app,'profile'=>$app->studentProfile??new StudentProfile]);} public function saveBiodata(Request $r){$app=$this->app($r);$this->editable($app);$data=$r->validate(['full_name'=>'required','nik'=>'nullable|digits:16','nisn'=>'nullable|digits_between:8,12','birth_date'=>'nullable|date','gender'=>'required','address'=>'required','birth_place'=>'nullable','religion'=>'nullable','province'=>'nullable','regency'=>'nullable','district'=>'nullable','village'=>'nullable','postal_code'=>'nullable','previous_school'=>'nullable']);$app->studentProfile()->updateOrCreate([], $data);return redirect()->route('applicant.parents')->with('success','Data siswa tersimpan.');} public function parents(Request $r){$app=$this->app($r);return view('dashboard.parents',['app'=>$app,'parent'=>$app->parentProfile??new ParentProfile]);} public function saveParents(Request $r){$app=$this->app($r);$this->editable($app);$data=$r->validate(['father_name'=>'required','father_phone'=>'nullable','mother_name'=>'required','mother_phone'=>'nullable','guardian_name'=>'nullable','guardian_relationship'=>'nullable','guardian_occupation'=>'nullable','guardian_phone'=>'nullable']);$app->parentProfile()->updateOrCreate([], $data);return redirect()->route('applicant.documents')->with('success','Data orang tua tersimpan.');} public function documents(Request $r){$app=$this->app($r);return view('dashboard.documents',compact('app'));} public function upload(Request $r,$type) {
    abort_unless(in_array($type,\App\Services\SpmbDocuments::TYPES,true),404);
    $app=$this->app($r); $this->editable($app);
    $r->validate(['file'=>$type==='student_photo'?'required|image|mimes:jpg,jpeg,png|max:2048':'required|mimes:pdf,jpg,jpeg,png|max:2048']);
    $file=$r->file('file');
    $disk=\App\Services\SpmbDocuments::disk();
    $path=$file->store('spmb/'.$r->user()->id,'spmb');
    try {
        $oldPath=DB::transaction(function()use($app,$type,$file,$path) {
            $locked=StudentApplication::whereKey($app->id)->lockForUpdate()->firstOrFail();
            $this->editable($locked);
            $old=$locked->documents()->where('document_type',$type)->first();
            $oldPath=$old?->file_path;
            $locked->documents()->updateOrCreate(['document_type'=>$type],['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()]);
            return $oldPath;
        });
    } catch (\Throwable $e) { $disk->delete($path); throw $e; }
    if($oldPath && \App\Services\SpmbDocuments::validPath($oldPath)) {
        $disk->delete($oldPath);
        Storage::disk('public')->delete($oldPath);
    }
    return back()->with('success','Dokumen tersimpan.');
 }
 public function removeDocument(Request $r,$id) {
    $app=$this->app($r);
    DB::transaction(function()use($app,$id) {
        $locked=StudentApplication::whereKey($app->id)->lockForUpdate()->firstOrFail();
        $this->editable($locked);
        $doc=$locked->documents()->findOrFail($id);
        if(\App\Services\SpmbDocuments::validPath($doc->file_path)) {
            \App\Services\SpmbDocuments::disk()->delete($doc->file_path);
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
    });
    return back();
 }
 public function review(Request $r){$app=$this->app($r)->load('studentProfile','parentProfile','documents');return view('dashboard.review',compact('app'));} public function submit(Request $r) {
    $app=$this->app($r);
    DB::transaction(function()use($app) {
        // Periode dikunci sebelum aplikasi agar urutan lock konsisten dengan pengelolaan periode.
        $periods=AdmissionPeriod::orderBy('id')->lockForUpdate()->get();
        $locked=StudentApplication::whereKey($app->id)->lockForUpdate()->firstOrFail();
        $this->editable($locked);
        abort_unless($locked->studentProfile && $locked->parentProfile && $locked->documents()->whereIn('document_type',['student_photo','family_card','birth_certificate'])->distinct()->count('document_type')===3,422,'Lengkapi data dan dokumen wajib.');
        if($locked->status==='revision' && $locked->registration_number) {
            // Revisi tetap memakai nomor, periode, gelombang, dan waktu pengiriman awal.
            $locked->update(['status'=>'submitted']);
        } else {
            $period=$periods->first(fn($p)=>$p->is_active && $p->start_date->startOfDay()->lte(now()) && $p->end_date->endOfDay()->gte(now()));
            $wave=$period ? AdmissionWave::where('admission_period_id',$period->id)->where('is_active',true)->whereDate('start_date','<=',today())->whereDate('end_date','>=',today())->orderBy('start_date')->orderBy('id')->lockForUpdate()->first() : null;
            abort_unless($wave,422,'Pendaftaran SPMB saat ini belum dibuka.');
            $prefix='SPMB-'.substr($period->academic_year,0,4).'-';
            $last=0;
            foreach(StudentApplication::where('registration_number','like',$prefix.'%')->pluck('registration_number') as $number) {
                $suffix=substr($number,strlen($prefix));
                if(ctype_digit($suffix)) $last=max($last,(int)$suffix);
            }
            $locked->update(['admission_period_id'=>$period->id,'admission_wave_id'=>$wave->id,'registration_number'=>$prefix.str_pad((string)($last+1),4,'0',STR_PAD_LEFT),'status'=>'submitted','submitted_at'=>now()]);
        }
        $locked->statusHistories()->create(['status'=>'submitted','note'=>'Pendaftaran dikirim oleh pendaftar.']);
    });
    return redirect()->route('applicant.status');
 }
 public function status(Request $r){$app=$this->app($r)->load('studentProfile','period','wave','statusHistories');return view('dashboard.status',compact('app'));} }
