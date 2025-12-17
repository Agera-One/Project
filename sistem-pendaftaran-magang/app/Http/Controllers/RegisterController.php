<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function registerChoose()
    {
        return view('auth.registerChoose');
    }

    public function registerPerusahaanForm()
    {
        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();
        return view('auth.registerPerusahaan', compact('jurusan'));
    }

    public function registerSiswaForm()
    {
        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();
        return view('auth.registerSiswa', compact('jurusan'));
    }

    public function registerSiswaStore(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'jurusan_id'     => 'required|exists:jurusan,id',
            'username'       => 'required|string|unique:users,username',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'nis'            => 'required|string|max:50|unique:siswa,nis',
            'nama_lengkap'   => 'required|string|max:255',
            'kelas'          => 'required|string|max:50',
            'nomor_telepon'  => 'required|string|max:20',
            'tempat_lahir'   => 'required|string|max:100',
            'tanggal_lahir'  => 'required|date',
            'alamat'         => 'required|string|max:255',
            'jenis_kelamin'  => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {

            // 1. Simpan ke tabel users (akun)
            $user = User::create([
                'username'  => $request->username,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'siswa',
            ]);

            // 2. Simpan ke tabel siswa (data profil siswa)
            Siswa::create([
                'user_id'        => $user->id,
                'jurusan_id'     => $request->jurusan_id,
                'nis'            => $request->nis,
                'nama_lengkap'   => $request->nama_lengkap,
                'kelas'          => $request->kelas,
                'nomor_telepon'  => $request->nomor_telepon,
                'tempat_lahir'   => $request->tempat_lahir,
                'tanggal_lahir'  => $request->tanggal_lahir,
                'alamat'         => $request->alamat,
                'jenis_kelamin'  => $request->jenis_kelamin,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Akun berhasil didaftarkan, tunggu verifikasi admin.');

        } catch (\Exception $error) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: '.$error->getMessage());
        }
    }

    public function registerPerusahaanStore(Request $request)
    {
        $request->validate([
            'jurusan_id'         => 'required|exists:jurusan,id',
            'username'           => 'required|string|unique:users,username',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:6|confirmed',
            'nama_perusahaan'    => 'required|string|max:255',
            'alamat_perusahaan'  => 'required|string|max:255',
            'nomor_telepon'      => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {

            // 1. Simpan ke tabel users (akun)
            $user = User::create([
                'username'  => $request->username,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'perusahaan',
            ]);

            // 2. Simpan ke tabel siswa (data profil siswa)
            Perusahaan::create([
                'user_id'            => $user->id,
                'jurusan_id'         => $request->jurusan_id,
                'nomor_telepon'      => $request->nomor_telepon,
                'nama_perusahaan'    => $request->nama_perusahaan,
                'alamat_perusahaan'  => $request->alamat_perusahaan,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Akun berhasil didaftarkan, tunggu verifikasi admin.');

        } catch (\Exception $error) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: '.$error->getMessage());
        }
    }
}
