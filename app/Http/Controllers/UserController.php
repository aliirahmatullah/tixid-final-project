<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; //Import model terlebih dahulu
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserExport;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{

    public function register(Request $request){
        //Request $request : mengambil value dari inputan form
        //dd() : dump and die untuk debgging
        // dd($request->all());

        //validasi data
        $request->validate([
            //name_input => 'validasi'
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            //email: dns memastikan email valid
            'email' => 'required|email:dns',
            'password' => 'required'
        ], [
            //custom pesan
            //format : 'name_input.validasi '=> 'pesan error'
            'first_name.required' => 'Nama depan wajib diisi',
            'first_name.min' => 'Nama depan diisi minimal 3 karakter',
            'last_name.required' => 'Nama belakang wajib diisi',
            'last_name.min' => 'Nama belakang diisi minimal 3 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Email diisi dengan data valid',
            'password.required' => 'Password wajib diisi'
        ]);

        //eloquent (fungsi model) tambah data baru : create([])
        $createData = User::create([
            //'column => $request->name_input
            'name' => $request->first_name . " " . $request->last_name,
            'email' => $request->email,
            //enkripsi data " merubah menjadi karakter acak, tidak ada bisa yang tau isi datanya : Hash::make()
            'password' => Hash::make($request->password),
            //Role diisi langsung sebagai user agar tidak bisa menjadi admnin / staff bagi pendaftar akun
            'role' => 'user'
        ]);

        if ($createData) {
            // redirect() perindahan halaman, route() nama route yang akan dipanggil
            //with() menngirim data session, biasanya untuk notif
            return redirect()->route('login')->with('success', 'Berhasil membuat akun, silahkan login');
        } else {
            //back() kembali ke halaman sebelumnya yang sudah dia akses
            return redirect()->back()->with('error', 'Gagal! silahkan coba lagi');
        }
    }

    public function loginAuth(Request $request){
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ], [
            'email.required' => 'Email harus diisi',
            'password.required' => 'Password harus diisi'
        ]);
        //menyimpan yang akan diverifikasi
        $data = $request->only(['email', 'password']);
        //Auth::attempt(), verifikasi kecocokan email pw atau username-pw
        if(Auth::attempt($data)){
            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.dashboard')->with('success','Berhasil login!');
            } elseif (Auth::user()->role == 'staff') {
                return redirect()->route('staff.dashboard')->with('success','Berhasil login!');
            } else {
                return redirect()->route('home')->with('success','Berhasil login!');
            }
        } else {
            return redirect()->back()->with('error', 'Gagal! pastikan email dan password sesuai');
        }
    }

    public function logout(){
        //Auth;;logout() : hapus sesi login
        Auth::logout();
        return redirect()->route('home')->with('logout', 'Anda telah logout! silahkan login kembali untuk akses lengkap');
    }
    public function index()
    {
        // $user = User::where('role', '!=', 'user')->get();
        $user = User::where('role', '!=', 'user')->get();
        return view('admin.user.index', compact('user'));
    }

    public function datatables()
    {
        $user = User::where('role', '!=', 'user')->get();
        return DataTables::of($user)->addIndexColumn()
        ->addColumn('btnActions', function($user) {
            $btnEdit = '<a href="'. route('admin.users.edit', $user['id']) .'" class="btn btn-secondary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('admin.users.delete', $user['id']) .'" method="POST"> '.
            @csrf_field() .
            @method_field('DELETE') . '
            <button class="btn btn-danger">Hapus</button>
        </form>';
        return '<div class="d-flex">' . $btnEdit . $btnDelete . '</div>';

        })
        ->rawColumns(['btnActions'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'password.required' => 'Password wajib diisi'
        ]);

        $createData = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff'
        ]);

        if ($createData) {
            return redirect()->route('admin.users.index')->with('success', 'Berhasil tambah data staff!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         //edit($id) : id diambil dari route {id}
         $user = User::find($id);
         return view('admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email salah'
        ]);

        $updateData = User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if($updateData) {
            return redirect()->route('admin.users.index')->with('success', 'Berhasil mengubah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::where('id', $id)->delete();
        return redirect()->route('admin.users.index')->with('success', 'Berhasil menghapus data!');
    }

    public function trash()
    {
        $users = User::onlyTrashed()->get();
        return view('admin.user.trash', compact('users'));
    }

    public function restore($id)
    {
        $users = User::onlyTrashed()->find($id);
        $users->restore();
        return redirect()->route('admin.users.index')->with('success', 'Berhasil mengembalikan data!');
    }

    public function deletePermanent($id)
    {
        $users = User::onlyTrashed()->find($id);
        $users->forceDelete();
        return redirect()->route('admin.users.index')->with('success', 'Berhasil menghapus data secara permanen!');
    }



    public function exportExcel()
    {
        //nama file yang akan diunduh
        $fileName = 'data-user.xlsx';
        //proses download
        return Excel::download(new UserExport, $fileName);
    }
}
