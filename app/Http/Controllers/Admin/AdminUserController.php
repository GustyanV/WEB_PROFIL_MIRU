<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ResetAdminPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class AdminUserController extends Controller {
    public function index(Request $request) {
        $request->validate(['search'=>'nullable|string|max:255']);
        $q=User::where('role','admin');
        if ($search=$request->query('search')) $q->where(fn($q)=>$q->where('name','like','%'.$search.'%')->orWhere('email','like','%'.$search.'%'));
        return view('admin.users.index',['users'=>$q->latest()->paginate(15)->withQueryString()]);
    }
    public function create() { return view('admin.users.form',['user'=>new User(['is_active'=>true])]); }
    public function store(AdminUserRequest $request) {
        $data=$request->validated(); $data['role']='admin'; $data['password']=Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('success','Akun admin berhasil dibuat.');
    }
    private function managed(User $user): void { abort_unless($user->role==='admin',403,'Hanya akun dengan role admin yang dapat dikelola.'); }
    public function edit(User $user) { $this->managed($user); return view('admin.users.form',compact('user')); }
    public function update(AdminUserRequest $request,User $user) {
        $this->managed($user); $data=$request->validated(); unset($data['password']);
        $user->update($data);
        return redirect()->route('admin.users.edit',$user)->with('success','Akun admin berhasil diperbarui.');
    }
    public function resetPassword(ResetAdminPasswordRequest $request,User $user) {
        $this->managed($user);
        $user->update(['password'=>Hash::make($request->validated('password')),'remember_token'=>Str::random(60)]);
        return back()->with('success','Password admin berhasil direset.');
    }
}