<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{StudentApplication,News,Activity,Teacher,GalleryAlbum};
class AdminDashboardController extends Controller {
    public function index() {
        $stats=[
            'Total Pendaftar'=>StudentApplication::count(),
            'Menunggu Verifikasi'=>StudentApplication::whereIn('status',['submitted','under_review'])->count(),
            'Diterima'=>StudentApplication::where('status','accepted')->count(),
            'Total Berita'=>News::count(),'Total Kegiatan'=>Activity::count(),'Total Guru'=>Teacher::count(),'Total Galeri'=>GalleryAlbum::count(),
        ];
        return view('admin.index',['stats'=>$stats,'recentApplications'=>StudentApplication::with(['user','studentProfile'])->latest()->take(5)->get(),
            'recentNews'=>News::latest()->take(5)->get(),'recentActivities'=>Activity::latest()->take(5)->get()]);
    }
}