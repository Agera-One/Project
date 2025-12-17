function logout() {
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "/logout";
    }
}

document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", (e) => {
        const hasSection = link.hasAttribute("data-section");

        // Jika TIDAK punya data-section → ini link ke route Laravel → jangan di-prevent!
        if (!hasSection) {
            return; // biarkan browser pindah halaman secara normal
        }

        // Jika punya data-section → baru kita pakai logika SPA (jarang dipakai di proyekmu)
        e.preventDefault();

        // Hapus active dari semua link
        document
            .querySelectorAll(".nav-link")
            .forEach((l) => l.classList.remove("active"));
        link.classList.add("active");

        // Ganti konten section (kalau memang ada)
        document.querySelectorAll(".content-section").forEach((section) => {
            section.classList.remove("active");
        });

        const sectionId = link.getAttribute("data-section");
        const target = document.getElementById(sectionId);
        if (target) target.classList.add("active");
    });
});

// ==== OTOMATIS SET ACTIVE MENU BERDASARKAN URL SAAT INI (VERSI FINAL) ====
document.addEventListener("DOMContentLoaded", () => {
    let currentPath = window.location.pathname.toLowerCase().replace(/\/$/, ""); // normalisasi slash & case

    // Clear semua active dulu
    document
        .querySelectorAll(".nav-link")
        .forEach((l) => l.classList.remove("active"));

    // Tentukan menu active berdasarkan prioritas path
    let activeText = null;

    if (currentPath.includes("siswa/dashboard")) {
        activeText = "Dashboard"; // prioritas untuk detail pelamar (misal /lowongan/{id}/pelamar)
    } else if (currentPath.includes("siswa/lowongan")) {
        activeText = "Daftar Magang"; // untuk create/edit lowongan
    } else {
        activeText = "Dashboard"; // untuk dashboard atau halaman lain
    }

    // Add active ke link yang match activeText
    if (activeText) {
        document.querySelectorAll(".nav-link").forEach((link) => {
            if (link.textContent.trim() === activeText) {
                link.classList.add("active");
            }
        });
    }
});

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1)
        return interval === 1
            ? "1 tahun yang lalu"
            : interval + " tahun yang lalu";

    interval = Math.floor(seconds / 2592000);
    if (interval >= 1)
        return interval === 1
            ? "1 bulan yang lalu"
            : interval + " bulan yang lalu";

    interval = Math.floor(seconds / 86400);
    if (interval >= 1)
        return interval === 1
            ? "1 hari yang lalu"
            : interval + " hari yang lalu";

    interval = Math.floor(seconds / 3600);
    if (interval >= 1)
        return interval === 1 ? "1 jam yang lalu" : interval + " jam yang lalu";

    interval = Math.floor(seconds / 60);
    if (interval >= 1)
        return interval === 1
            ? "1 menit yang lalu"
            : interval + " menit yang lalu";

    return "beberapa detik yang lalu";
}

function getStatusBadge(status) {
    if (status === "tertunda")
        return '<span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Tertunda</span>';
    if (status === "diterima")
        return '<span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">Diterima</span>';
    return '<span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-800">Ditolak</span>';
}

// Toggle dropdown
document.addEventListener("click", function (e) {
    if (e.target.closest(".action-btn")) {
        const dropdown = e.target.closest(".action-btn").nextElementSibling;
        // Tutup semua dropdown lain
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
            if (menu !== dropdown) menu.classList.add("hidden");
        });
        dropdown.classList.toggle("hidden");
    } else {
        // Klik di luar → tutup semua dropdown
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
            menu.classList.add("hidden");
        });
    }
});

function renderTable() {
    const tbody = document.getElementById("applicants-tbody");
    tbody.innerHTML = "";

    const applicants = window.applicants || [];

    if (applicants.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-12 text-gray-500">Belum ada pelamar</td></tr>';
        return;
    }

    applicants.forEach((app, index) => {
        const s = app.siswa;

        const row = document.createElement("tr");
        row.className = "hover:bg-gray-50 transition-colors";
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-semibold text-gray-900">${s.nis}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-base">
                        ${s.nama_lengkap.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">${
                            s.nama_lengkap
                        }</div>
                        <div class="text-sm text-gray-500">${
                            s.user?.email || "-"
                        }</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm">${app.lowongan.perusahaan.nama_perusahaan}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-700">${app.lowongan.posisi.nama_posisi}</td>
            <td class="px-6 py-4 text-sm">
                <div class="location flex items-center gap-2 text-gray-500">
                    <svg class="icon w-4 h-4 stroke-current stroke-2 fill-none" viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    ${app.lowongan.perusahaan.alamat_perusahaan}
                </div>
            </td>
            <td class="px-6 py-4 text-sm">${timeAgo(app.tanggal_lamar)}</td>
            <td class="px-6 py-4">${getStatusBadge(app.status)}</td>
            <td class="px-6 py-4">
                <div class="relative inline-block text-left">
                    <!-- Tombol titik tiga -->
                    <button
                        type="button"
                        class="action-btn inline-flex justify-center w-full rounded-md p-2 text-sm font-medium text-gray-500 hover:bg-gray-100 focus:outline-none transition"
                        onclick="toggleDropdown(event, this)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu hidden absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="py-1">
                            <a href="/storage/${app.file_cv}" target="_blank"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat CV
                            </a>
                            <a href="/storage/${app.file_cv}" download="CV_${s.nama_lengkap.replace(/ /g,"_")}.pdf"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download CV
                            </a>
                        </div>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Search
document.getElementById("search-input")?.addEventListener("input", (e) => {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll("#applicants-tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(term)
            ? ""
            : "none";
    });
});

document.addEventListener("DOMContentLoaded", renderTable);
