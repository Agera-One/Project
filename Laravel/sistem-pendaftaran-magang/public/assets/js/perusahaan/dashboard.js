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

    if (currentPath.includes("perusahaan/pelamar")) {
        activeText = "Daftar Pelamar"; // prioritas untuk detail pelamar (misal /lowongan/{id}/pelamar)
    } else if (currentPath.includes("perusahaan/lowongan")) {
        activeText = "Buat Lowongan"; // untuk create/edit lowongan
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

// === DROPDOWN YANG BENAR & ANTI-RUSAK SETELAH HAPUS ===
function toggleDropdown(button) {
    const currentDropdown = button.closest(".dropdown");

    // Tutup semua dropdown lain
    document.querySelectorAll(".dropdown.active").forEach((dropdown) => {
        if (dropdown !== currentDropdown) {
            dropdown.classList.remove("active");
        }
    });

    // Toggle dropdown yang diklik
    currentDropdown.classList.toggle("active");
}

// Tutup dropdown kalau klik di luar
document.addEventListener("click", function (e) {
    if (!e.target.closest(".dropdown")) {
        document.querySelectorAll(".dropdown.active").forEach((dropdown) => {
            dropdown.classList.remove("active");
        });
    }
});

// Confirm hapus lowongan
document.querySelectorAll(".dropdown-item.danger").forEach((item) => {
    item.addEventListener("click", function (e) {
        if (!confirm("Yakin ingin menghapus lowongan ini?")) {
            e.preventDefault();
        }
    });
});
