<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
            </svg>
        </div>
        <h2>Siswa Magang</h2>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('siswa.dashboard') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M5.25 5.25h4.75v4.75h-4.75z M14 5.25h4.75v4.75H14z M5.25 14h4.75v4.75h-4.75z M14 14h4.75v4.75H14z" />
                </svg>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('siswa.lowongan') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.4 14a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Daftar Magang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </li>
    </ul>

    <div class="user-profile">
        <div class="profile-avatar">{{ strtoupper(substr($siswa->user->username, 0, 2)) }}</div>
        <div class="profile-info">
            <div class="profile-name">{{ $siswa->user->username }}</div>
            <div class="profile-email">{{ $siswa->user->email }}</div>
        </div>
    </div>
</aside>
