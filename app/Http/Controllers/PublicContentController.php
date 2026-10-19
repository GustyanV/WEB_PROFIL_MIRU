<?php
namespace App\Http\Controllers;
use App\Models\{Achievement,AdmissionPeriod,AdmissionWave,Announcement,Extracurricular,GalleryAlbum,News,Program,SchoolProfile,Teacher};
use App\Support\SchoolContent;
use Illuminate\Support\Facades\Schema;

class PublicContentController extends Controller
{
    private function school() { return SchoolContent::profile(SchoolProfile::orderBy('id')->first()); }
    private function newsQuery() {
        return SchoolContent::visible(News::where('status','published'))->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()));
    }
    public function home() {
        $period=AdmissionPeriod::where('is_active',true)->first();
        $wave=$period ? AdmissionWave::where('admission_period_id',$period->id)->where('is_active',true)->whereDate('start_date','<=',today())->whereDate('end_date','>=',today())->orderBy('start_date')->first() : null;
        return view('public.home',[
            'latestActivities'=>Schema::hasTable('activities') ? \App\Models\Activity::published()->with('images')->orderByDesc('activity_date')->take(3)->get() : collect(),
            'hero'=>Schema::hasTable('hero_banners') ? \App\Models\HeroBanner::orderBy('id')->first() : null,
            'school'=>$this->school(),'admissionPeriod'=>$period,'admissionWave'=>$wave,
            'programs'=>Program::where('is_active',true)->take(4)->get(),'achievements'=>Achievement::latest()->take(4)->get(),
            'news'=>$this->newsQuery()->latest('published_at')->take(3)->get(),'albums'=>GalleryAlbum::with('images')->latest()->take(3)->get(),
        ]);
    }
    public function profile($page='about') { return view('public.profile',['school'=>$this->school(),'page'=>$page]); }
    public function teachers() { return view('public.collection',['title'=>'Guru & Tenaga Kependidikan','items'=>Teacher::where('is_active',true)->orderBy('sort_order')->paginate(12),'type'=>'teacher']); }
    public function programs() { return view('public.collection',['title'=>'Program Unggulan','items'=>Program::where('is_active',true)->paginate(12),'type'=>'program']); }
    public function achievements() { return view('public.collection',['title'=>'Prestasi','items'=>Achievement::latest()->paginate(12),'type'=>'achievement']); }
    public function extracurriculars() { return view('public.collection',['title'=>'Ekstrakurikuler','items'=>Extracurricular::where('is_active',true)->paginate(12),'type'=>'extracurricular']); }
    public function news() { return view('public.collection',['title'=>'Berita','items'=>$this->newsQuery()->latest('published_at')->paginate(10),'type'=>'news']); }
    public function newsDetail($slug) { return view('public.news-detail',['item'=>$this->newsQuery()->where('slug',$slug)->firstOrFail()]); }
    public function announcements() { return view('public.collection',['title'=>'Pengumuman','items'=>SchoolContent::visible(Announcement::where('status','published'))->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->latest('published_at')->paginate(10),'type'=>'announcement']); }
    public function gallery() { return view('public.collection',['title'=>'Galeri','items'=>GalleryAlbum::with('images')->latest()->paginate(12),'type'=>'album']); }
    public function album($slug) { return view('public.album',['album'=>GalleryAlbum::where('slug',$slug)->with('images')->firstOrFail()]); }
}
