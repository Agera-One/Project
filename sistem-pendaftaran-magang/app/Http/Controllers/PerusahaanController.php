<?php

namespace App\Http\Controllers;

use App\Models\Keahlian;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Posisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerusahaanController extends Controller
{
    public function dashboard()
    {
        $perusahaan = Auth::user()->perusahaan;

        $lowongans = Lowongan::where('perusahaan_id', $perusahaan->id)
            ->with(['posisi', 'keahlian', 'perusahaan'])
            ->withCount('lamaran')
            ->latest()
            ->get();

        $total_lowongan = $lowongans->count();
        $total_pelamar = $lowongans->sum('lamaran_count');
        $rata_rata_pelamar = $total_lowongan > 0 ? round($total_pelamar / $total_lowongan, 1) : 0;

        return view('perusahaan.pages.dashboard', compact(
            'perusahaan',
            'lowongans',
            'total_lowongan',
            'total_pelamar',
            'rata_rata_pelamar'
        ));
    }

    public function lowonganForm()
    {
        $perusahaan = Auth::user()->perusahaan;
        $jurusanId = $perusahaan->jurusan_id;

        $posisi = Posisi::where('jurusan_id', $jurusanId)->orderBy('nama_posisi')->get();
        $keahlian = Keahlian::where('jurusan_id', $jurusanId)->orderBy('nama_keahlian')->get();

        return view('perusahaan.pages.lowongan', compact('posisi', 'keahlian', 'perusahaan'));
    }

    public function lowonganStore(Request $request)
    {
        $perusahaan = Auth::user()->perusahaan;

        $request->validate([
            'posisi_id' => 'required|exists:posisi,id',
            'keahlian_id' => 'required|exists:keahlian,id',
            'jumlah_slot' => 'required|integer|min:1|max:100',
            'durasi_magang' => 'required|string|max:100',
            'deskripsi_lowongan' => 'required|string',
        ]);

        Lowongan::create([
            'jurusan_id' => $perusahaan->jurusan_id,
            'perusahaan_id' => $perusahaan->id,
            'posisi_id' => $request->posisi_id,
            'keahlian_id' => $request->keahlian_id,
            'deskripsi_lowongan' => $request->deskripsi_lowongan,
            'durasi_magang' => $request->durasi_magang,
            'jumlah_slot' => $request->jumlah_slot,
        ]);

        return redirect()->route('perusahaan.dashboard')->with('success', 'Lowongan magang berhasil dibuat!');
    }

    // ==================== EDIT ====================
    public function lowonganEdit($id)
    {
        $perusahaan = Auth::user()->perusahaan;
        $lowongan = Lowongan::where('id', $id)->where('perusahaan_id', $perusahaan->id)->firstOrFail();
        $posisi = Posisi::where('jurusan_id', $perusahaan->jurusan_id)->orderBy('nama_posisi')->get();
        $keahlian = Keahlian::where('jurusan_id', $perusahaan->jurusan_id)->orderBy('nama_keahlian')->get();

        return view('perusahaan.pages.lowongan', compact('posisi', 'keahlian', 'lowongan', 'perusahaan'));
    }

    public function lowonganUpdate(Request $request, $id)
    {
        $perusahaan = Auth::user()->perusahaan;

        $lowongan = Lowongan::where('id', $id)
            ->where('perusahaan_id', $perusahaan->id)
            ->firstOrFail();

        $request->validate([
            'posisi_id' => 'required|exists:posisi,id',
            'keahlian_id' => 'required|exists:keahlian,id',
            'jumlah_slot' => 'required|integer|min:1|max:100',
            'durasi_magang' => 'required|string|max:100',
            'deskripsi_lowongan' => 'required|string',
        ]);

        $lowongan->update([
            'posisi_id' => $request->posisi_id,
            'keahlian_id' => $request->keahlian_id,
            'deskripsi_lowongan' => $request->deskripsi_lowongan,
            'durasi_magang' => $request->durasi_magang,
            'jumlah_slot' => $request->jumlah_slot,
        ]);

        return redirect()->route('perusahaan.dashboard')->with('success', 'Lowongan berhasil diperbarui!');
    }

    // ==================== HAPUS ====================
    public function lowonganDestroy($id)
    {
        $perusahaan = Auth::user()->perusahaan;

        $lowongan = Lowongan::where('id', $id)
            ->where('perusahaan_id', $perusahaan->id)
            ->firstOrFail();

        $lowongan->delete();

        return redirect()->route('perusahaan.dashboard')->with('success', 'Lowongan berhasil dihapus!');
    }

    public function pelamar()
    {
        $perusahaan = Auth::user()->perusahaan;

        // Ambil SEMUA lamaran dari SEMUA lowongan milik perusahaan ini
        $lamarans = Lamaran::with(['siswa.user', 'lowongan.posisi'])
            ->whereHas('lowongan', function ($query) use ($perusahaan) {
                $query->where('perusahaan_id', $perusahaan->id);
            })
            ->latest('tanggal_lamar')
            ->get();

        // Hitung statistik
        $total = $lamarans->count();
        $pending = $lamarans->where('status', 'tertunda')->count();
        $accepted = $lamarans->where('status', 'diterima')->count();
        $rejected = $lamarans->where('status', 'ditolak')->count();

        // Kirim ke view yang sama dengan detail lowongan (pelamar.blade.php)
        return view('perusahaan.pages.pelamar', compact(
            'lamarans',
            'total',
            'pending',
            'accepted',
            'rejected',
            'perusahaan'
        ));
    }

    public function pelamarLowongan(Lowongan $lowongan)
    {
        $perusahaan = Auth::user()->perusahaan;

        if ($lowongan->perusahaan_id !== Auth::user()->perusahaan->id) {
            abort(403);
        }

        $lamarans = Lamaran::with(['siswa.user', 'lowongan.posisi'])
            ->where('lowongan_id', $lowongan->id)
            ->latest('tanggal_lamar')
            ->get();

        $total = $lamarans->count();
        $pending = $lamarans->where('status', 'tertunda')->count();
        $accepted = $lamarans->where('status', 'diterima')->count();
        $rejected = $lamarans->where('status', 'ditolak')->count();

        return view('perusahaan.pages.pelamar', compact(
            'lamarans',
            'lowongan',
            'total',
            'pending',
            'accepted',
            'rejected',
            'perusahaan'
        ));
    }

    public function updateStatusPelamar(Request $request)
    {
        $request->validate([
            'lamaran_id' => 'required|exists:lamaran,id',
            'status' => 'required|in:diterima,ditolak',
        ]);

        $perusahaan = Auth::user()->perusahaan;

        $lamaran = Lamaran::with('lowongan')
            ->findOrFail($request->lamaran_id);

        // Pastikan lamaran ini milik lowongan perusahaan yang login
        if ($lamaran->lowongan->perusahaan_id !== $perusahaan->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Hanya boleh ubah kalau masih tertunda
        if ($lamaran->status !== 'tertunda') {
            return response()->json(['success' => false, 'message' => 'Status sudah diputuskan'], 422);
        }

        $lamaran->status = $request->status;
        $lamaran->save();

        return response()->json(['success' => true, 'status' => $request->status]);
    }
}
