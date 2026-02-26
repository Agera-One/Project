<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Pelamar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/perusahaan/pelamar.css') }}">
</head>

<body class="bg-gray-50 min-h-screen">
    @include('perusahaan.layouts.sidebar')

    <div class="ml-[260px] p-[35px] bg-[#] flex-1 min-h-screen transition-margin duration-300 ease-in-out">
        <div class="main-content min-w-[1400px] mx-auto">
            <!-- STAT CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[35px] mb-[35px]">
                <div class="stat-card bg-white rounded-xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-blue-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">+12%</span>
                    </div>
                    <div class="stat-number text-3xl font-bold text-gray-900 mb-1" id="total-count">{{ $total ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-500">Total Pelamar</div>
                </div>

                <!-- Pending Card -->
                <div class="stat-card bg-white rounded-xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-yellow-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded">Review</span>
                    </div>
                    <div class="stat-number text-3xl font-bold text-gray-900 mb-1" id="pending-count">
                        {{ $pending ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-500">Tertunda</div>
                </div>

                <!-- Accepted Card -->
                <div class="stat-card bg-white rounded-xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-green-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">+8%</span>
                    </div>
                    <div class="stat-number text-3xl font-bold text-gray-900 mb-1" id="accepted-count">
                        {{ $accepted ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-500">Diterima</div>
                </div>

                <!-- Rejected Card -->
                <div class="stat-card bg-white rounded-xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-red-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">-3%</span>
                    </div>
                    <div class="stat-number text-3xl font-bold text-gray-900 mb-1" id="rejected-count">
                        {{ $rejected ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-500">Ditolak</div>
                </div>
            </div>

            <!-- TABEL PELAMAR -->
            <div class="main-card bg-white rounded-lg p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        @if (isset($lowongan))
                            Pelamar {{ $lowongan->posisi->nama_posisi }}
                        @else
                            Daftar Semua Pelamar
                        @endif
                    </h3>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" id="search-input" placeholder="Search..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64" />
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="w-full">
                        <thead class="border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    NIS
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Posisi
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Pelamar
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Kelas
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    No. Telepon
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Tanggal Lamar
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody id="applicants-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- JS akan isi di sini -->
                        </tbody>
                    </table>

                    @if (($lamarans ?? collect())->isEmpty())
                        <div class="text-center py-16 text-gray-500">
                            Belum ada pelamar untuk lowongan ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DATA DINAMIS DARI LARAVEL (tetap di Blade, tapi terpisah rapi) --}}
    @include('perusahaan.pages.pelamarData')

    {{-- JS EKSTERNAL 100% BERSIH --}}
    <script src="{{ asset('assets/js/perusahaan/pelamar.js') }}?v={{ time() }}"></script>
</body>

</html>
