<?php

namespace App\Http\Controllers;

use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = Auth::user()->siswa;

        $lamarans = Lamaran::with(['siswa.user', 'lowongan.posisi', 'lowongan.perusahaan'])
            ->whereHas('lowongan', function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);
            })
            ->latest('tanggal_lamar')
            ->get();

        // Hitung statistik
        $total = $lamarans->count();
        $pending = $lamarans->where('status', 'tertunda')->count();
        $accepted = $lamarans->where('status', 'diterima')->count();
        $rejected = $lamarans->where('status', 'ditolak')->count();

        // Kirim ke view yang sama dengan detail lowongan (pelamar.blade.php)
        return view('siswa.pages.dashboard', compact(
            'lamarans',
            'total',
            'pending',
            'accepted',
            'rejected',
            'siswa',
        ));
    }

    public function lowongan()
    {
        $siswa = Auth::user()->siswa;

        return view('siswa.pages.lowongan', compact('siswa'));
    }

    public function getLowonganData()
    {
        $lowongans = Lowongan::with(['perusahaan', 'posisi', 'jurusan', 'keahlian'])
            ->where('jumlah_slot', '>', 0)
            ->get();

        $kartu = $lowongans->map(function ($l) {
            return [
                'id' => $l->id,
                'nama_perusahaan' => $l->perusahaan?->nama_perusahaan ?? 'Tidak diketahui',
                'alamat' => $l->perusahaan?->alamat_perusahaan ?? '-',
                'nama_posisi' => $l->posisi?->nama_posisi ?? '-',
                'nama_jurusan' => $l->jurusan?->nama_jurusan ?? 'Umum',
                'deskripsi' => $l->deskripsi_lowongan ?? '-',
                'durasi_magang' => $l->durasi_magang ?? '-',
                'jumlah_slot' => $l->jumlah_slot ?? 0,
                'keahlian' => $l->keahlian?->nama_keahlian ? [$l->keahlian->nama_keahlian] : [],
            ];
        })->toArray();

        $jurusanList = \App\Models\Jurusan::orderBy('nama_jurusan')->pluck('nama_jurusan')->toArray();

        $subOptions = [];
        foreach ($lowongans as $l) {
            $jur = $l->jurusan?->nama_jurusan ?? 'Umum';
            $pos = $l->posisi?->nama_posisi;
            if ($pos) {
                $subOptions[$jur][] = $pos;
            }
        }
        foreach ($subOptions as $j => $arr) {
            $subOptions[$j] = array_values(array_unique($arr));
        }

        return response()->json([
            'kartu' => $kartu,
            'jurusanList' => $jurusanList,
            'subOptions' => $subOptions,
        ]);
    }

    // FORM LAMARAN
    public function lamaranForm($lowongan_id)
    {
        $siswa = Auth::user()->siswa;

        $lowongan = Lowongan::with('perusahaan', 'posisi')->findOrFail($lowongan_id);

        if (Lamaran::where('siswa_id', Auth::id())->where('lowongan_id', $lowongan_id)->exists()) {
            return back()->with('error', 'Kamu sudah melamar lowongan ini sebelumnya!');
        }

        return view('siswa.pages.lamaran', compact('lowongan', 'siswa'));
    }

    // SUBMIT LAMARAN
    public function lamaranStore(Request $request, $lowongan_id)
    {
        try {
            Lowongan::findOrFail($lowongan_id);

            $request->validate([
                'foto_formal' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'file_cv' => 'required|mimes:pdf|max:10000',
                'alasan' => 'required|string|min:20',
                'harapan' => 'required|string|min:20',
            ]);

            $fotoPath = $request->file('foto_formal')->store('lamaran/foto', 'public');
            $cvPath = $request->file('file_cv')->store('lamaran/cv', 'public');
            $siswaId = Auth::user()->siswa->id;

            Lamaran::create([
                'siswa_id' => $siswaId,
                'lowongan_id' => $lowongan_id,
                'foto_formal' => $fotoPath,
                'file_cv' => $cvPath,
                'alasan' => $request->alasan,
                'harapan' => $request->harapan,
                'status' => 'tertunda',
                'tanggal_lamar' => now(),
            ]);

            return redirect()->route('siswa.lowongan')
                ->with('success', 'Lamaran berhasil dikirim dan tersimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal simpan: '.$e->getMessage());
        }
    }
}
