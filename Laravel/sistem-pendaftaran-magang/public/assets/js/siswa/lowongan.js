function logout() {
    if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "/logout";
    }
}

// Active menu sidebar
document.addEventListener("DOMContentLoaded", () => {
    let currentPath = window.location.pathname.toLowerCase().replace(/\/$/, "");
    document.querySelectorAll(".nav-link").forEach((l) => l.classList.remove("active"));

    let activeText = "Dashboard";
    if (currentPath.includes("siswa/lowongan")) {
        activeText = "Daftar Magang";
    }

    document.querySelectorAll(".nav-link").forEach((link) => {
        if (link.textContent.trim() === activeText) {
            link.classList.add("active");
        }
    });
});

// ==== MAIN LOGIC ====
(() => {
    const filterOptions = document.querySelector(".filter-options");
    const subContainer = document.querySelector(".subfilter-container");
    const subLabel = document.querySelector(".subfilter-label");
    const searchInput = document.querySelector(".search-box input");
    const container = document.querySelector(".internship-container");
    const emptyState = document.querySelector(".main-card");

    let allCards = [];
    let activeJurusan = null;
    let activePosisi = null;
    let data = null;

    // ==== ANIMASI STAGGER MENGGUNAKAN slideUpGrid ====
    function animateVisibleCards() {
        const visibleCards = container.querySelectorAll('.internship-card:not([style*="display: none"])');

        visibleCards.forEach((card, index) => {
            // Reset animasi
            card.classList.remove('animated');
            card.style.animationDelay = '';

            // Trigger reflow agar animasi bisa jalan ulang
            void card.offsetWidth;

            // Tambahkan class animated + delay stagger
            card.classList.add('animated');
            card.style.animationDelay = `${index * 0.25}s`; // 120ms stagger, bisa ubah ke 0.15 atau 0.1
        });
    }

    // ==== RENDER CARD ====
    function renderCards(items) {
        container.innerHTML = "";
        allCards = [];

        if (items.length === 0) {
            emptyState.style.display = "block";
            return;
        }
        emptyState.style.display = "none";

        items.forEach((item) => {
            const card = document.createElement("div");
            card.className = "internship-card"; // tanpa .animated dulu

            const skills = Array.isArray(item.keahlian) ? item.keahlian : [];
            const searchText = [item.nama_perusahaan, item.nama_posisi, item.deskripsi, item.alamat, ...skills]
                .join(" ").toLowerCase();

            card.dataset.jurusan = (item.nama_jurusan || "").toLowerCase();
            card.dataset.posisi = (item.nama_posisi || "").toLowerCase();
            card.dataset.search = searchText;

            const skillTags = skills.slice(0, 3)
                .map(s => `<span class="skill-label">${s}</span>`).join("");

            card.innerHTML = `
                <div class="slot-badge">${item.jumlah_slot} Slots</div>
                <h3 class="company-name">${item.nama_perusahaan}</h3>
                <h4 class="position-title">${item.nama_posisi}</h4>
                <p class="card-description">${item.deskripsi}</p>
                <div class="card-details">
                    <span><i class="fas fa-map-marker-alt"></i> ${item.alamat}</span>
                    <span><i class="fas fa-clock"></i> ${item.durasi_magang}</span>
                </div>
                <div class="skill-tags">${skillTags}</div>
                <button onclick="window.location.href='/siswa/lamaran/${item.id}'" class="apply-btn">Apply Now</button>
            `;

            container.appendChild(card);
            allCards.push(card);
        });

        // Animasi setelah semua card masuk DOM
        requestAnimationFrame(() => {
            animateVisibleCards();
        });
    }

    // ==== FILTER & SEARCH ====
    function applyFilters() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        allCards.forEach((card) => {
            const matchJurusan = !activeJurusan || card.dataset.jurusan === activeJurusan;
            const matchPosisi = !activePosisi || card.dataset.posisi === activePosisi;
            const matchSearch = !query || card.dataset.search.includes(query);

            if (matchJurusan && matchPosisi && matchSearch) {
                card.style.display = "";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        emptyState.style.display = visibleCount === 0 ? "block" : "none";

        // Animasi ulang hanya card yang visible
        if (visibleCount > 0) {
            requestAnimationFrame(() => {
                animateVisibleCards();
            });
        }
    }

    // ==== SUBFILTER POSISI ====
    function renderSubFilters(jurusan) {
        subContainer.innerHTML = "";
        subLabel.textContent = "";
        activePosisi = null;

        if (!jurusan || jurusan === "Semua Jurusan") return;

        const posisis = data.subOptions?.[jurusan] || [];
        if (posisis.length === 0) return;

        subLabel.textContent = "PILIH SESUAI POSISI";

        const wrapper = document.createElement("div");
        wrapper.className = "subfilter-wrapper";

        posisis.forEach(p => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "subfilter-item";
            btn.textContent = p;
            btn.dataset.posisi = p.toLowerCase();

            btn.addEventListener("click", () => {
                document.querySelectorAll(".subfilter-item").forEach(b => b.classList.remove("active"));
                if (btn.classList.contains("active")) {
                    btn.classList.remove("active");
                    activePosisi = null;
                } else {
                    btn.classList.add("active");
                    activePosisi = p.toLowerCase();
                }
                applyFilters();
            });

            wrapper.appendChild(btn);
        });

        subContainer.appendChild(wrapper);
    }

    // ==== FETCH DATA ====
    fetch("/siswa/get-lowongan-data")
        .then(r => {
            if (!r.ok) throw new Error("Gagal load data");
            return r.json();
        })
        .then(res => {
            data = res;
            renderCards(data.kartu || []);

            // Jurusan buttons
            const btnAll = document.createElement("button");
            btnAll.className = "filter-btn active";
            btnAll.textContent = "Semua Jurusan";
            btnAll.dataset.jurusan = "Semua Jurusan";
            filterOptions.appendChild(btnAll);

            (data.jurusanList || []).forEach(jur => {
                const btn = document.createElement("button");
                btn.className = "filter-btn";
                btn.textContent = jur;
                btn.dataset.jurusan = jur;
                filterOptions.appendChild(btn);
            });

            // Event jurusan
            filterOptions.querySelectorAll(".filter-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    filterOptions.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");

                    const val = btn.dataset.jurusan;
                    activeJurusan = val === "Semua Jurusan" ? null : val.toLowerCase();
                    renderSubFilters(val);
                    applyFilters();
                });
            });

            // Search
            searchInput.addEventListener("input", () => {
                applyFilters();
            });
        })
        .catch(err => {
            console.error(err);
            emptyState.style.display = "block";
        });
})();
