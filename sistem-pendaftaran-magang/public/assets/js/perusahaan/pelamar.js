function logout() {
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "/logout";
    }
}

// Active menu logic
document.addEventListener("DOMContentLoaded", () => {
    let currentPath = window.location.pathname.toLowerCase().replace(/\/$/, "");
    document.querySelectorAll(".nav-link").forEach((l) => l.classList.remove("active"));

    let activeText = "Dashboard";
    if (currentPath.includes("perusahaan/pelamar")) activeText = "Daftar Pelamar";
    else if (currentPath.includes("perusahaan/lowongan")) activeText = "Buat Lowongan";

    document.querySelectorAll(".nav-link").forEach((link) => {
        if (link.textContent.trim() === activeText) link.classList.add("active");
    });
});

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) return interval === 1 ? "1 tahun yang lalu" : interval + " tahun yang lalu";

    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) return interval === 1 ? "1 bulan yang lalu" : interval + " bulan yang lalu";

    interval = Math.floor(seconds / 86400);
    if (interval >= 1) return interval === 1 ? "1 hari yang lalu" : interval + " hari yang lalu";

    interval = Math.floor(seconds / 3600);
    if (interval >= 1) return interval === 1 ? "1 jam yang lalu" : interval + " jam yang lalu";

    interval = Math.floor(seconds / 60);
    if (interval >= 1) return interval === 1 ? "1 menit yang lalu" : interval + " menit yang lalu";

    return "beberapa detik yang lalu";
}

function getStatusBadge(status) {
    if (status === "tertunda")
        return '<span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Tertunda</span>';
    if (status === "diterima")
        return '<span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">Diterima</span>';
    return '<span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-800">Ditolak</span>';
}

// Update statistik kartu
function updateStats() {
    const applicants = window.applicants || [];
    const total = applicants.length;
    const pending = applicants.filter(a => a.status === 'tertunda').length;
    const accepted = applicants.filter(a => a.status === 'diterima').length;
    const rejected = applicants.filter(a => a.status === 'ditolak').length;

    document.getElementById('total-count').textContent = total;
    document.getElementById('pending-count').textContent = pending;
    document.getElementById('accepted-count').textContent = accepted;
    document.getElementById('rejected-count').textContent = rejected;
}

// Update status lamaran
async function changeStatus(lamaranId, newStatus, button) {
    try {
        const response = await axios.post('/perusahaan/pelamar/update-status', {
            lamaran_id: lamaranId,
            status: newStatus
        }, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        if (response.data.success) {
            // Update data di memory
            const app = window.applicants.find(a => a.id === lamaranId);
            if (app) {
                app.status = newStatus;
            }

            // Re-render tabel dan stats
            renderTable();
            updateStats();

            // Tutup dropdown
            button.closest('.dropdown-menu').classList.add('hidden');

            alert(`Lamaran berhasil ${newStatus === 'diterima' ? 'diterima' : 'ditolak'}!`);
        }
    } catch (error) {
        let message = 'Gagal mengupdate status.';
        if (error.response && error.response.data.message) {
            message = error.response.data.message;
        }
        alert(message);
    }
}

function renderTable() {
    const tbody = document.getElementById("applicants-tbody");
    tbody.innerHTML = "";

    const applicants = window.applicants || [];

    if (applicants.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-12 text-gray-500">Belum ada pelamar</td></tr>';
        return;
    }

    applicants.forEach((app) => {
        const s = app.siswa;

        const row = document.createElement("tr");
        row.className = "hover:bg-gray-50 transition-colors";
        row.innerHTML = `
            <td class="px-6 py-4 text-sm text-gray-900">${s.nis}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-700">${app.lowongan.posisi.nama_posisi}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-base">
                        ${s.nama_lengkap.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">${s.nama_lengkap}</div>
                        <div class="text-sm text-gray-500">${s.user?.email || "-"}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm">${s.kelas}</td>
            <td class="px-6 py-4 text-sm">${s.nomor_telepon}</td>
            <td class="px-6 py-4 text-sm">${timeAgo(app.tanggal_lamar)}</td>
            <td class="px-6 py-4">${getStatusBadge(app.status)}</td>
            <td class="px-6 py-4">
                <div class="relative inline-block text-left">
                    <button type="button" class="action-btn inline-flex justify-center w-full rounded-md p-2 text-sm font-medium text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                    </button>

                    <div class="dropdown-menu hidden absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="/storage/${app.file_cv}" target="_blank"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat CV
                            </a>
                            <a href="/storage/${app.file_cv}" download="CV_${s.nama_lengkap.replace(/ /g, "_")}.pdf"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download CV
                            </a>

                            ${app.status === 'tertunda' ? `
                            <div class="border-t border-gray-200"></div>
                            <button onclick="changeStatus(${app.id}, 'diterima', this)"
                                    class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Terima Lamaran
                            </button>
                            <button onclick="changeStatus(${app.id}, 'ditolak', this)"
                                    class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tolak Lamaran
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Re-bind dropdown setelah render ulang
    bindDropdowns();
}

// Handler dropdown yang aman dari duplikat event
let dropdownHandler = function(e) {
    const actionBtn = e.target.closest(".action-btn");
    if (actionBtn) {
        const dropdown = actionBtn.nextElementSibling;
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            if (menu !== dropdown) menu.classList.add("hidden");
        });
        dropdown.classList.toggle("hidden");
    } else {
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
    }
};

function bindDropdowns() {
    document.removeEventListener("click", dropdownHandler);
    document.addEventListener("click", dropdownHandler);
}

// Search functionality
document.getElementById("search-input")?.addEventListener("input", (e) => {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll("#applicants-tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? "" : "none";
    });
});

// Initial render
document.addEventListener("DOMContentLoaded", () => {
    renderTable();
    updateStats();
});
