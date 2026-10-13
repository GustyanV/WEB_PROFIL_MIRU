<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicContentController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApplicationPrintController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PublicContentController::class,'home'])->name('home');
Route::get('/profil', [PublicContentController::class,'profile'])->name('profile');
Route::get('/profil/sejarah', fn(PublicContentController $c)=>$c->profile('history'))->name('profile.history');
Route::get('/profil/visi-misi', fn(PublicContentController $c)=>$c->profile('vision'))->name('profile.vision');
Route::get('/profil/sambutan', fn(PublicContentController $c)=>$c->profile('headmaster'))->name('profile.headmaster');
Route::get('/profil/struktur-organisasi', fn(PublicContentController $c)=>$c->profile('structure'))->name('profile.structure');
Route::get('/guru',[PublicContentController::class,'teachers'])->name('teachers'); Route::get('/program-unggulan',[PublicContentController::class,'programs'])->name('programs'); Route::get('/prestasi',[PublicContentController::class,'achievements'])->name('achievements'); Route::get('/ekstrakurikuler',[PublicContentController::class,'extracurriculars'])->name('extracurriculars'); Route::get('/berita',[PublicContentController::class,'news'])->name('news'); Route::get('/berita/{slug}',[PublicContentController::class,'newsDetail'])->name('news.show'); Route::get('/pengumuman',[PublicContentController::class,'announcements'])->name('announcements'); Route::get('/galeri',[PublicContentController::class,'gallery'])->name('gallery'); Route::get('/galeri/{slug}',[PublicContentController::class,'album'])->name('gallery.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/verifikasi-spmb/{token}',[ApplicationPrintController::class,'verify'])->name('spmb.verify');
Route::middleware(['auth','role:pendaftar'])->prefix('dashboard')->name('applicant.')->group(function(){Route::get('/',[ApplicantController::class,'dashboard'])->name('dashboard');Route::get('/biodata',[ApplicantController::class,'biodata'])->name('biodata');Route::post('/biodata',[ApplicantController::class,'saveBiodata'])->name('biodata.save');Route::get('/orang-tua',[ApplicantController::class,'parents'])->name('parents');Route::post('/orang-tua',[ApplicantController::class,'saveParents'])->name('parents.save');Route::get('/dokumen',[ApplicantController::class,'documents'])->name('documents');Route::post('/dokumen/{type}',[ApplicantController::class,'upload'])->name('documents.upload');Route::delete('/dokumen/{id}',[ApplicantController::class,'removeDocument'])->name('documents.delete');Route::get('/review',[ApplicantController::class,'review'])->name('review');Route::post('/submit',[ApplicantController::class,'submit'])->name('submit');Route::get('/status',[ApplicantController::class,'status'])->name('status');Route::get('/cetak-bukti/{application}',[ApplicationPrintController::class,'preview'])->name('proof');Route::get('/cetak-bukti/{application}/pdf',[ApplicationPrintController::class,'pdf'])->name('proof.pdf');});
Route::get('/dashboard-home', fn()=>redirect()->route('applicant.dashboard'))->middleware(['auth','role:pendaftar'])->name('dashboard');
Route::prefix('admin')->name('admin.')->middleware(['auth','role:super_admin,admin'])->group(function () { Route::get('/', fn() => view('admin.index'))->name('dashboard'); Route::get('/pendaftar',[ApplicationController::class,'index'])->name('applications.index');Route::get('/pendaftar/{application}',[ApplicationController::class,'show'])->name('applications.show');Route::post('/pendaftar/{application}/status',[ApplicationController::class,'transition'])->name('applications.transition');Route::get('/soon/{module}',fn($module)=>view('admin.soon',compact('module')))->name('soon');Route::get('/profil',[SchoolProfileController::class,'edit'])->name('profile.edit');Route::put('/profil',[SchoolProfileController::class,'update'])->name('profile.update');Route::get('/{type}',[ContentController::class,'index'])->whereIn('type',['guru','program','prestasi','ekstrakurikuler','berita','pengumuman','galeri'])->name('content.index');Route::get('/{type}/create',[ContentController::class,'create'])->name('content.create');Route::post('/{type}',[ContentController::class,'store'])->name('content.store');Route::get('/{type}/{id}/edit',[ContentController::class,'edit'])->name('content.edit');Route::put('/{type}/{id}',[ContentController::class,'update'])->name('content.update');Route::delete('/{type}/{id}',[ContentController::class,'destroy'])->name('content.destroy');Route::post('/galeri/{id}/images',[ContentController::class,'uploadImages'])->name('gallery.images.store');Route::delete('/galeri/images/{id}',[ContentController::class,'destroyImage'])->name('gallery.images.destroy'); });
