<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Achievement,Announcement,Extracurricular,GalleryAlbum,GalleryImage,News,Program,Teacher}; use Illuminate\Http\Request; use Illuminate\Support\Facades\Storage; use Illuminate\Support\Str;
class ContentController extends Controller {
 private function config(string $type): array { return [
 'guru'=>[Teacher::class,'Guru',['name'=>'Nama','nip'=>'NIP','nuptk'=>'NUPTK','position'=>'Jabatan','subject'=>'Mata Pelajaran','photo'=>'Foto:image','bio'=>'Bio:textarea','sort_order'=>'Urutan:number','is_active'=>'Aktif:boolean']],
 'program'=>[Program::class,'Program Unggulan',['title'=>'Judul','description'=>'Deskripsi:textarea','icon'=>'Icon','image'=>'Gambar:image','is_active'=>'Aktif:boolean']],
 'prestasi'=>[Achievement::class,'Prestasi',['title'=>'Judul','student_name'=>'Nama Siswa','level'=>'Tingkat:select(Sekolah|Kecamatan|Kabupaten|Provinsi|Nasional)','year'=>'Tahun','image'=>'Gambar:image','description'=>'Deskripsi:textarea']],
 'ekstrakurikuler'=>[Extracurricular::class,'Ekstrakurikuler',['name'=>'Nama','coach_name'=>'Pembina','schedule'=>'Jadwal','description'=>'Deskripsi:textarea','image'=>'Gambar:image','is_active'=>'Aktif:boolean']],
 'berita'=>[News::class,'Berita',['title'=>'Judul','slug'=>'Slug','excerpt'=>'Ringkasan:textarea','content'=>'Isi:textarea','thumbnail'=>'Thumbnail:image','status'=>'Status:select(draft|published)','published_at'=>'Tanggal Publikasi:datetime']],
 'pengumuman'=>[Announcement::class,'Pengumuman',['title'=>'Judul','content'=>'Isi:textarea','status'=>'Status:select(draft|published)','published_at'=>'Tanggal Publikasi:datetime']],
 'galeri'=>[GalleryAlbum::class,'Album Galeri',['title'=>'Judul','slug'=>'Slug','description'=>'Deskripsi:textarea','cover'=>'Cover:image']],
 ][$type] ?? abort(404); }
 public function index(Request $r,string $type){[$model,$title,$fields]=$this->config($type);$items=$model::query()->when($r->search,fn($q,$s)=>$q->where(array_key_first($fields),\App\Support\DatabaseSearch::operator(),"%$s%"))->latest()->paginate(10)->withQueryString();return view('admin.content.index',compact('type','title','fields','items'));}
 public function create(string $type){[$model,$title,$fields]=$this->config($type);return view('admin.content.form',compact('type','title','fields'));}
 public function store(Request $r,string $type){[$model,$title,$fields]=$this->config($type);$data=$this->validated($r,$type,$fields);$model::create($data);return redirect()->route('admin.content.index',$type)->with('success',"$title berhasil ditambahkan.");}
 public function edit(string $type,int $id){[$model,$title,$fields]=$this->config($type);$item=$model::findOrFail($id);return view('admin.content.form',compact('type','title','fields','item'));}
 public function update(Request $r,string $type,int $id){[$model,$title,$fields]=$this->config($type);$item=$model::findOrFail($id);$data=$this->validated($r,$type,$fields,$item);$item->update($data);return redirect()->route('admin.content.index',$type)->with('success',"$title berhasil diperbarui.");}
 public function destroy(string $type,int $id){[$model,$title,$fields]=$this->config($type);$item=$model::findOrFail($id);foreach($fields as $key=>$label)if(str_contains($label,':image')&&$item->$key)Storage::disk('public')->delete($item->$key);$item->delete();return back()->with('success',"$title berhasil dihapus.");}
 public function uploadImages(Request $r,int $id){$album=GalleryAlbum::findOrFail($id);$r->validate(['images'=>'required|array','images.*'=>'image|mimes:jpg,jpeg,png,webp|max:2048','caption'=>'nullable|string|max:255']);foreach($r->file('images') as $image)$album->images()->create(['image'=>$image->store('gallery','public'),'caption'=>$r->caption]);return back()->with('success','Foto galeri berhasil diunggah.');}
 public function destroyImage(int $id){$image=GalleryImage::findOrFail($id);Storage::disk('public')->delete($image->image);$image->delete();return back()->with('success','Foto berhasil dihapus.');}
 private function validated(Request $r,string $type,array $fields,$item=null): array {
    if (in_array($type,['berita','galeri'],true) && !$r->input('slug') && is_string($r->input('title'))) $r->merge(['slug'=>Str::slug($r->input('title'))]);
    $rules=[];
    foreach($fields as $key=>$label) {
        $kind=str_contains($label,':')?explode(':',$label,2)[1]:'';
        if($kind==='image') $rules[$key]='nullable|image|mimes:jpg,jpeg,png,webp|max:2048';
        elseif($kind==='boolean') $rules[$key]='nullable|boolean';
        elseif($key==='slug') $rules[$key]=['required','string','max:255','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',\Illuminate\Validation\Rule::unique($type==='galeri'?'gallery_albums':'news','slug')->ignore($item?->id)];
        elseif(str_starts_with($kind,'select')) $rules[$key]=['required',\Illuminate\Validation\Rule::in(explode('|',str_replace(['select(',')'],'',$kind)))];
        elseif($kind==='datetime') $rules[$key]='nullable|date';
        elseif($kind==='number') $rules[$key]='nullable|integer|min:0';
        elseif(in_array($key,['content','description'],true)) $rules[$key]='required|string|max:50000';
        elseif($kind==='textarea') $rules[$key]='nullable|string|max:50000';
        else $rules[$key]=(in_array($key,['name','title'],true)?'required':'nullable').'|string|max:255';
    }
    $data=$r->validate($rules,['*.required'=>':attribute wajib diisi.','*.unique'=>'Slug sudah digunakan.','*.date'=>'Tanggal tidak valid.','*.max'=>':attribute melebihi batas yang diizinkan.','*.mimes'=>'Gambar harus JPG, JPEG, PNG, atau WEBP.']);
    foreach($fields as $key=>$label) {
        if(str_contains($label,':image')) {
            unset($data[$key]);
            if($r->hasFile($key)) {
                $data[$key]=$r->file($key)->store(match($type){'guru'=>'teachers','program'=>'programs','prestasi'=>'achievements','ekstrakurikuler'=>'extracurriculars','berita'=>'news','galeri'=>'gallery'},'public');
                if(!$data[$key]) throw \Illuminate\Validation\ValidationException::withMessages([$key=>'Gambar gagal disimpan.']);
            }
        }
        if(str_contains($label,':boolean')) $data[$key]=$r->boolean($key);
    }
    return $data;
 }
}
