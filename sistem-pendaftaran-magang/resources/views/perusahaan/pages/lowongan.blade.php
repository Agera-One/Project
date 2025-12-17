<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('assets/css/perusahaan/lowongan.css') }}">
    <script src="{{ asset('assets/js/perusahaan/lowongan.js') }}" defer></script>
    <title>{{ isset($lowongan) ? 'Edit' : 'Buat' }} Lowongan Magang</title>
</head>

<body>
    @include('perusahaan.layouts.sidebar')

    <div class="container">
        <div class="main-content">
            <a href="{{ route('perusahaan.dashboard') }}" class="back-link">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" />
                </svg>
                Kembali
            </a>

            <div class="form-card">
                <h2 class="form-title">
                    {{ isset($lowongan) ? 'Edit Lowongan Magang' : 'Buat Lowongan Magang Baru' }}
                </h2>

                <form id="jobForm"
                    action="{{ isset($lowongan) ? route('perusahaan.lowongan.update', $lowongan->id) : route('perusahaan.lowongan.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($lowongan))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="posisi_id">Posisi <span class="required">*</span></label>
                        <select id="posisi_id" name="posisi_id" required>
                            <option value="">Pilih Posisi</option>
                            @foreach ($posisi as $p)
                                <option value="{{ $p->id }}"
                                    {{ isset($lowongan) && $lowongan->posisi_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_posisi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah_slot">Jumlah Slot <span class="required">*</span></label>
                        <div class="number-wrapper">
                            <input type="number" id="jumlah_slot" name="jumlah_slot" min="1"
                                value="{{ isset($lowongan) ? $lowongan->jumlah_slot : '1' }}" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="durasi_magang">Durasi <span class="required">*</span></label>
                        <input type="text" id="durasi_magang" name="durasi_magang" placeholder="3 bulan"
                            value="{{ isset($lowongan) ? $lowongan->durasi_magang : '' }}" required />
                    </div>

                    <div class="form-group">
                        <label for="deskripsi_lowongan">Deskripsi <span class="required">*</span></label>
                        <textarea id="deskripsi_lowongan" name="deskripsi_lowongan" required>{{ isset($lowongan) ? $lowongan->deskripsi_lowongan : '' }}</textarea>
                    </div>

                    <div class="skills-section">
                        <span class="skills-label">Keahlian yang Dibutuhkan</span>
                        <div class="skills-grid">
                            @foreach ($keahlian as $k)
                                <label class="skill-item radio-item">
                                    <input type="radio" name="keahlian_id" value="{{ $k->id }}"
                                        id="keahlian-{{ $k->id }}"
                                        {{ isset($lowongan) && $lowongan->keahlian_id == $k->id ? 'checked' : '' }}
                                        required>
                                    <span class="radio-mark"></span>
                                    <span class="skill-text">{{ $k->nama_keahlian }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($lowongan) ? 'Update Lowongan' : 'Buat Lowongan' }}
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancel()">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
