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
    } else if (currentPath.includes("siswa/lamaran")) {
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

const formGroups = document.querySelectorAll(".form-group");
formGroups.forEach((group, index) => {
    group.style.animationDelay = `${0.4 + index * 0.05}s`;
});

const checkboxItems = document.querySelectorAll(".checkbox-item");
checkboxItems.forEach((item, index) => {
    item.style.animationDelay = `${0.8 + index * 0.03}s`;
});

const skillsSection = document.querySelector(".skills-section");
skillsSection.style.animationDelay = "0.7s";

function cancel() {
    if (
        confirm(
            "Apakah Anda yakin ingin membatalkan? Data yang telah diisi akan hilang."
        )
    ) {
        window.location.href = "/perusahaan/dashboard";

    }
}

function logout() {
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        console.log("Logging out...");
        window.location.href = "/logout";
    }
}
