<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Services\ActivityFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminActivityController extends Controller
{
    public function index(Request $request) {
        $q = Activity::with('images');
        if ($search = $request->query('search')) $q->where(function ($q) use ($search) { $q->where('title','like','%'.$search.'%')->orWhere('excerpt','like','%'.$search.'%'); });
        if ($category = $request->query('category')) $q->where('category',$category);
        if ($status = $request->query('status')) $q->where('status',$status);
        return view('admin.activities.index',['activities'=>$q->orderByDesc('activity_date')->orderByDesc('id')->paginate(15)->withQueryString()]);
    }
    public function create() { return view('admin.activities.create',['activity'=>new Activity(['status'=>'draft','activity_date'=>today()])]); }
    public function store(StoreActivityRequest $request) {
        $activity = $this->save($request,new Activity);
        return redirect()->route('admin.activities.edit',$activity)->with('success','Kegiatan berhasil ditambahkan.');
    }
    public function edit(Activity $activity) { $activity->load('images'); return view('admin.activities.edit',compact('activity')); }
    public function update(UpdateActivityRequest $request, Activity $activity) {
        $this->save($request,$activity);
        return redirect()->route('admin.activities.edit',$activity)->with('success','Kegiatan berhasil diperbarui.');
    }
    private function save(StoreActivityRequest $request, Activity $activity): Activity {
        $data = $request->validated(); unset($data['thumbnail']);
        $old = $activity->thumbnail; $uploaded = null;
        try {
            if ($request->hasFile('thumbnail')) $data['thumbnail'] = $uploaded = ActivityFiles::store($request->file('thumbnail'),'activities');
            DB::transaction(function () use ($activity,$data) { $activity->fill($data)->save(); });
        } catch (\Throwable $e) { ActivityFiles::deleteUnused($uploaded); throw $e; }
        if ($uploaded) ActivityFiles::deleteUnused($old);
        return $activity;
    }
    public function destroy(Activity $activity) {
        $paths = DB::transaction(function () use ($activity) {
            $locked = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $paths = $locked->images()->pluck('image')->push($locked->thumbnail)->all();
            $locked->delete(); return $paths;
        });
        foreach ($paths as $path) ActivityFiles::deleteUnused($path);
        return redirect()->route('admin.activities.index')->with('success','Kegiatan dan galeri berhasil dihapus.');
    }
}