<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5,3V21H11V17.5H13V21H19V3H5M7,5H9V7H7V5M11,5H13V7H11V5M15,5H17V7H15V5M7,9H9V11H7V9M11,9H13V11H11V9M15,9H17V11H15V9M7,13H9V15H7V13M11,13H13V15H11V13M15,13H17V15H15V13M7,17H9V19H7V17M15,17H17V19H15V17Z" />
            </svg>
        </div>
        <h2>Portal Magang</h2>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('perusahaan.dashboard') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M5.25 5.25h4.75v4.75h-4.75z M14 5.25h4.75v4.75H14z M5.25 14h4.75v4.75h-4.75z M14 14h4.75v4.75H14z" />
                </svg>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('perusahaan.lowongan.form') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.4 14a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Buat Lowongan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('perusahaan.pelamar') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Daftar Pelamar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </li>
    </ul>

    <div class="user-profile">
        <div class="profile-avatar">{{ strtoupper(substr($perusahaan->user->username, 0, 2)) }}</div>
        <div class="profile-info">
            <div class="profile-name">{{ $perusahaan->user->username }}</div>
            <div class="profile-email">{{ $perusahaan->user->email }}</div>
        </div>
    </div>
</aside>
