<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/perusahaan/dashboard.css') }}" />
    <script src="{{ asset('assets/js/perusahaan/dashboard.js') }}" defer></script>
    <title>Company - Portal Perusahaan</title>
</head>

<body>
    @include('perusahaan.layouts.sidebar')

    <div class="container">
        <div class="main-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Lowongan</span>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20,6H12L10,4H4A2,2 0 0,0 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8A2,2 0 0,0 20,6Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $total_lowongan }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Pelamar</span>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16,13C15.71,13 15.38,13 15.03,13.05C16.19,13.89 17,15 17,16.5V19H23V16.5C23,14.17 18.33,13 16,13M8,13C5.67,13 1,14.17 1,16.5V19H15V16.5C15,14.17 10.33,13 8,13M8,11A3,3 0 0,0 11,8A3,3 0 0,0 8,5A3,3 0 0,0 5,8A3,3 0 0,0 8,11M16,11A3,3 0 0,0 19,8A3,3 0 0,0 16,5A3,3 0 0,0 13,8A3,3 0 0,0 16,11Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $total_pelamar }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Rata-rata per Lowongan</span>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $rata_rata_pelamar }}</div>
                </div>
            </div>

            <div class="main-card">
                <div class="card-header">
                    <h2 class="card-title">Kelola Lowongan Magang</h2>
                    <a href="{{ route('perusahaan.lowongan.form') }}" class="btn-add">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                        </svg>
                        Tambah Lowongan
                    </a>
                </div>

                @if ($lowongans->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">
                            <div class="folder-icon"></div>
                        </div>
                        <h2 class="empty-title">
                            Tidak ditemukan kandidat yang sesuai dengan kebutuhan perusahaan Anda
                        </h2>
                        <p class="empty-description">
                            Coba sesuaikan filter atau parameter pencarian Anda
                        </p>

                        <div class="loading-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>

                        <button class="action-btn" onclick="window.location.href='{{ route('perusahaan.lowongan.form') }}'">
                            Buat Lowongan Baru
                        </button>
                    </div>
                @else
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Posisi</th>
                                <th>Keahlian</th>
                                <th>Alamat</th>
                                <th>Durasi Magang</th>
                                <th>Jumlah Pelamar</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowongans as $lowongan)
                                <tr>
                                    <td>
                                        <strong>{{ $lowongan->posisi->nama_posisi }}</strong>
                                    </td>
                                    <td>
                                        {{ $lowongan->keahlian->nama_keahlian }}
                                    </td>
                                    <td>
                                        <div class="location">
                                            <svg class="icon" viewBox="0 0 24 24">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            {{ Str::limit($lowongan->perusahaan->alamat_perusahaan, 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge duration">{{ $lowongan->durasi_magang }}</span>
                                    </td>
                                    <td>
                                        @if ($lowongan->lamaran_count > 0)
                                            <a href="{{ route('perusahaan.pelamar.lowongan', $lowongan) }}"
                                                class="applicant-link">
                                                {{ $lowongan->lamaran_count }} pelamar
                                            </a>
                                        @else
                                            <span class="text-gray-400">0 pelamar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge badge" title="{{ $lowongan->created_at }}">
                                            {{ $lowongan->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="actions-btn" onclick="toggleDropdown(this)">
                                                <svg class="icon" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="1.5"></circle>
                                                    <circle cx="12" cy="5" r="1.5"></circle>
                                                    <circle cx="12" cy="19" r="1.5"></circle>
                                                </svg>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('perusahaan.lowongan.edit', $lowongan) }}"
                                                    class="dropdown-item">
                                                    <svg class="icon" viewBox="0 0 24 24">
                                                        <path
                                                            d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7">
                                                        </path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z">
                                                        </path>
                                                    </svg>
                                                    Edit
                                                </a>

                                                <form action="{{ route('perusahaan.lowongan.destroy', $lowongan) }}"
                                                    method="POST" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item danger">
                                                        <svg class="icon" viewBox="0 0 24 24">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path
                                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                            </path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</body>

</html>

{{-- <button class="dropdown-item" onclick="alert('Lihat Detail')">
    <svg class="icon" viewBox="0 0 24 24">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
    </svg>
    Lihat Detail
</button>
<button class="dropdown-item" onclick="alert('Duplicate')">
    <svg class="icon" viewBox="0 0 24 24">
        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1">
        </path>
    </svg>
    Duplicate
</button> --}}
