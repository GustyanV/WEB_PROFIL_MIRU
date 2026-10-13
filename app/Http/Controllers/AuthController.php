<?php
namespace App\Http\Controllers;
use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Facades\Password; use Illuminate\Validation\Rules\Password as PasswordRule;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');} public function showRegister(){return view('auth.register');}
 public function register(Request $r){$data=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','phone'=>'required|string|max:30','password'=>['required','confirmed',PasswordRule::min(8)]]);$user=User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone'],'password'=>Hash::make($data['password']),'role'=>'pendaftar']);Auth::login($user);$r->session()->regenerate();return redirect()->route('dashboard')->with('success','Akun pendaftar berhasil dibuat.');}
 public function login(Request $r){$credentials=$r->validate(['email'=>'required|email','password'=>'required|string']);if(!Auth::attempt($credentials,$r->boolean('remember'))) return back()->withErrors(['email'=>'Email atau password tidak sesuai.'])->onlyInput('email');$r->session()->regenerate();return $this->redirectForRole($r->user());}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home')->with('success','Anda telah logout.');}
 public function showForgot(){return view('auth.forgot-password');} public function sendReset(Request $r){$r->validate(['email'=>'required|email']);$status=Password::sendResetLink($r->only('email'));return $status===Password::RESET_LINK_SENT?back()->with('status',__($status)):back()->withErrors(['email'=>__($status)]);}
 public static function redirectForRole(User $user){return in_array($user->role,['super_admin','admin'],true)?redirect()->route('admin.dashboard'):redirect()->route('dashboard');}
}
