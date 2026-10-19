<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityImageRequest;
use App\Models\Activity;
use App\Models\ActivityImage;
use App\Services\ActivityFiles;
use Illuminate\Support\Facades\DB;

class AdminActivityImageController extends Controller
{
    public function store(ActivityImageRequest $request, Activity $activity) {
        $uploaded = [];
        try {
            foreach ($request->file('images') as $file) $uploaded[] = ActivityFiles::store($file,'activities/gallery');
            DB::transaction(function () use ($activity,$uploaded) {
                $locked = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
                $order = (int)$locked->images()->max('sort_order');
                foreach ($uploaded as $path) $locked->images()->create(['image'=>$path,'sort_order'=>++$order]);
            });
        } catch (\Throwable $e) { foreach ($uploaded as $path) ActivityFiles::deleteUnused($path); throw $e; }
        return back()->with('success','Foto galeri berhasil diunggah.');
    }
    public function update(ActivityImageRequest $request, Activity $activity, ActivityImage $image) {
        abort_unless($image->activity_id === $activity->id,404);
        DB::transaction(function () use ($request,$activity,$image) {
            Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            if ($request->boolean('is_cover')) $activity->images()->update(['is_cover'=>false]);
            $image->refresh()->update($request->validated());
        });
        return back()->with('success','Foto galeri berhasil diperbarui.');
    }
    public function destroy(Activity $activity, ActivityImage $image) {
        abort_unless($image->activity_id === $activity->id,404);
        $path = $image->image;
        DB::transaction(function () use ($activity,$image) {
            Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $image->delete();
        });
        ActivityFiles::deleteUnused($path);
        return back()->with('success','Foto berhasil dihapus.');
    }
}