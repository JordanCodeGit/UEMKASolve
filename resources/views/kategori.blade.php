@extends('layouts.app')

@section('title', 'Kategori')

@section('content')

{{-- // Bagian Header & Tombol Tambah Kategori --}}
<div class="content-card-category">
    <div class="card-body-category add-section-category">
        <h3 class="card-title-category">Tambah Kategori Baru</h3>
        <button class="btn-gradient-category" id="open-add-modal-btn">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

{{-- // Bagian Daftar Kategori (Pemasukan & Pengeluaran) --}}
<div class="main-category-container">

    <div class="category-section">
        <div class="section-header-green">
            <span class="dot-indicator"></span>
            <h3>Kategori Pemasukan</h3>
        </div>
        <div class="grid-container-category" id="category-list-pemasukan">
            <p class="loading-text">Memuat kategori...</p>
        </div>
    </div>

    <div class="section-spacer"></div>

    <div class="category-section">
        <div class="section-header-green">
            <span class="dot-indicator dot-orange"></span>
            <h3>Kategori Pengeluaran</h3>
        </div>
        <div class="grid-container-category" id="category-list-pengeluaran">
            <p class="loading-text">Memuat kategori...</p>
        </div>
    </div>

</div>
{{-- // Modal Tambah/Edit Kategori --}}

<div class="modal-overlay" id="category-modal-overlay" style="display: none;">
    <div class="modal-box">

        <div class="modal-header">
            <h2 id="modal-title">Tambah Kategori Baru</h2>
            <button class="modal-close-btn" id="modal-close-btn">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form id="category-form">
            <div class="modal-body">

                <div id="modal-form-message";></div>

                <div class="modal-tabs" id="modal-tab-container">
                    <button type="button" class="modal-tab-item active" data-tab-type="pengeluaran">Pengeluaran</button>
                    <button type="button" class="modal-tab-item" data-tab-type="pemasukan">Pemasukan</button>
                    <input type="hidden" id="modal-tipe" name="tipe" value="pengeluaran">
                </div>

                <div class="form-group-modal">
                    <label for="modal-nama-kategori">Nama Kategori</label>
                    <input type="text" id="modal-nama-kategori" name="nama_kategori" class="form-input-modal" placeholder="Masukkan nama kategori..." required>
                </div>

                <div class="form-group-modal icon-modal">
                    <label>Pilih Ikon</label>

                    <input type="hidden" name="ikon" id="modal-kat-ikon" required>

                    <div id="dynamic-icon-grid" class="icon-picker-container grid-pengeluaran">
                    </div>

                    <small id="icon-error" class="text-red-500 text-xs hidden mt-1">Silakan pilih ikon.</small>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" id="modal-cancel-btn">Batal</button>
                <button type="submit" class="btn btn-primary-modal" id="modal-submit-btn">Tambah Kategori</button>
            </div>
        </form>

    </div>
</div>
{{-- // Modal Konfirmasi Hapus Kategori --}}

<div class="modal-overlay" id="delete-modal-overlay" style="display: none;">
    <div class="modal-box delete-modal-box">

        <div class="delete-icon-wrapper">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h2 class="delete-title">Hapus Kategori?</h2>

        <p class="delete-message">
            Anda akan menghapus kategori <strong id="delete-target-name">...</strong>.<br>
            <span class="text-danger-warning">
                Tindakan ini akan menghapus <strong>SELURUH DATA TRANSAKSI</strong> yang terhubung dengan kategori ini.
            </span>
            <br>
            Apakah Anda yakin ingin melanjutkan?
        </p>

        <div class="modal-footer delete-footer">
            <button type="button" class="btn btn-secondary-modal" id="cancel-delete-btn">Batal</button>
            <button type="button" class="btn btn-danger-modal" id="confirm-delete-btn">
                Ya, Hapus Permanen
            </button>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '{{ url("/login") }}';
            return;
        }

        // --- Daftar Nama File Icon ---
        const iconFiles = [
            'Button.png', 'Button-1.png', 'Button-2.png', 'Button-3.png', 'Button-4.png',
            'Button-5.png', 'Button-6.png', 'Button-7.png', 'Button-8.png', 'Button-9.png',
            'Button-10.png', 'Button-11.png', 'Button-12.png', 'Button-13.png', 'Button-14.png',
            'Button-15.png', 'Button-16.png', 'Button-17.png', 'Button-18.png', 'Button-19.png',
            'Button-20.png', 'Button-21.png', 'Button-22.png', 'Button-23.png', 'Button-24.png',
            'Button-25.png', 'Button-26.png', 'Button-27.png', 'Button-28.png', 'Button-29.png',
            'Button-30.png', 'Button-31.png', 'Button-32.png', 'Button-33.png', 'Button-34.png',
            'Button-35.png', 'Button-36.png', 'Button-37.png', 'Button-38.png', 'Button-39.png',
            'Button-40.png', 'Button-41.png', 'Button-42.png', 'Button-43.png', 'Button-44.png',
            'Button-45.png', 'Button-46.png', 'Button-47.png'
        ];

        // --- Elemen Utama Halaman ---
        const listPemasukan = document.getElementById('category-list-pemasukan');
        const listPengeluaran = document.getElementById('category-list-pengeluaran');
        const openAddModalBtn = document.getElementById('open-add-modal-btn');

        // --- Elemen Modal ---
        const modalOverlay = document.getElementById('category-modal-overlay');
        const modalTitle = document.getElementById('modal-title');
        const modalForm = document.getElementById('category-form');
        const modalMessage = document.getElementById('modal-form-message');
        const modalSubmitBtn = document.getElementById('modal-submit-btn');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        const modalCancelBtn = document.getElementById('modal-cancel-btn');
        const modalTabs = document.querySelectorAll('.modal-tab-item');
        const tabContainer = document.getElementById('modal-tab-container');

        // Input Form
        const inputTipe = document.getElementById('modal-tipe');
        const inputNama = document.getElementById('modal-nama-kategori');
        const inputIkon = document.getElementById('modal-kat-ikon');

        let currentEditingId = null;
// Kode fungsi untuk mengamankan teks HTML
        
        function escapeHtml(text) {
            if (!text) return text;
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")  // Mengubah < menjadi kode &lt; (tampil sebagai text <)
                .replace(/>/g, "&gt;")  // Mengubah > menjadi kode &gt;
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- Variabel API ---
        const API_CATEGORIES = '{{ url("/api/categories") }}';
        const API_HEADERS = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        };
        const API_HEADERS_GET = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Cache-Control': 'no-cache'
        };


        // Kode fungsi mengambil data kategori dari API
        // --- 1. [READ] Fungsi Mengambil & Menampilkan Kategori ---
        async function fetchCategories() {
            listPemasukan.innerHTML = '<p class="text-gray-500 italic">Memuat...</p>';
            listPengeluaran.innerHTML = '<p class="text-gray-500 italic">Memuat...</p>';

            try {
                const response = await fetch(API_CATEGORIES, { headers: API_HEADERS_GET });

                // [FIX] Cek jika token expired atau tidak login
                if (response.status === 401) return window.location.href = '{{ url("/login") }}';

                // [FIX] Cek apakah server mengembalikan error (selain 200 OK)
                if (!response.ok) {
                    throw new Error(`Server Error: ${response.status} ${response.statusText}`);
                }

                const categories = await response.json();

                // [DEBUG] Lihat data asli di Console (Tekan F12)
                console.log("Data Kategori dari Server:", categories);

                renderCategories(categories);

            } catch (error) {
                console.error('Error fetching categories:', error);
                // Tampilkan pesan error yang lebih spesifik di layar
                const errorMsg = `<p style="color: red; font-size: 0.9rem;">Gagal: ${error.message}</p>`;
                listPemasukan.innerHTML = errorMsg;
                listPengeluaran.innerHTML = errorMsg;
            }
        }

        // --- 2. Fungsi Merender HTML Kategori ---
        // Kode fungsi render elemen kategori ke grid
        // --- 2. Fungsi Merender HTML Kategori (DIPERBAIKI) ---
        function renderCategories(rawData) {
            listPemasukan.innerHTML = '';
            listPengeluaran.innerHTML = '';

            // [FIX] Normalisasi data
            let categories = rawData;
            if (rawData.data && Array.isArray(rawData.data)) {
                categories = rawData.data;
            }

            // [FIX] Validasi akhir
            if (!Array.isArray(categories)) {
                console.error("Format data salah:", rawData);
                listPemasukan.innerHTML = '<p style="color: red;">Format data dari server salah.</p>';
                return;
            }

            if (categories.length === 0) {
                listPemasukan.innerHTML = '<p class="text-gray-400 italic">Belum ada kategori.</p>';
                return;
            }

            categories.forEach(cat => {
                const item = document.createElement('div');
                item.className = 'category-item-large';
                item.dataset.id = cat.id;

                // 1. LOGIKA ICON (GAMBAR VS FONTAWESOME)
                let iconHtml = '';

                // Cek apakah data ikon ada DAN berakhiran ekstensi gambar
                if (cat.ikon && (cat.ikon.includes('.png') || cat.ikon.includes('.jpg') || cat.ikon.includes('.svg') || cat.ikon.includes('.jpeg'))) {

                    // Render sebagai IMAGE
                    // Icon path bisa berupa "pengeluaran/Button.png" atau "Button.png"
                    // Jika tidak ada folder prefix, gunakan tipe kategori
                    let iconPath = cat.ikon;
                    if (!iconPath.includes('/')) {
                        iconPath = `${cat.tipe}/${iconPath}`;
                    }
                    const iconUrl = `{{ asset('icons') }}/${iconPath}`;

                    iconHtml = `<img src="${iconUrl}" alt="icon" class="w-6 h-6 object-contain">`;

                } else {

                    // Render sebagai FontAwesome (Data Lama / Default)
                    const iconClass = cat.ikon ? escapeHtml(cat.ikon) : 'fa-solid fa-tag';
                    iconHtml = `<i class="${iconClass}"></i>`;
                }

                // 2. LOGIKA WARNA BACKGROUND
                const bgClass = cat.tipe === 'pemasukan' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600';

                // Tambahkan class shape berdasarkan tipe
                const shapeClass = cat.tipe === 'pemasukan' ? 'icon-shape-pemasukan' : 'icon-shape-pengeluaran';

                // 3. KEAMANAN XSS
                const safeNama = escapeHtml(cat.nama_kategori);
                // Jika ikon FontAwesome, aman karena sudah di-escape di atas. Jika Image, aman karena path server.
                // Kita simpan value asli ikon untuk data-ikon tombol edit (hati-hati di sini)
                const safeDataIkon = cat.ikon ? escapeHtml(cat.ikon) : '';

                // 4. SUSUN HTML KE DALAM ITEM
                item.innerHTML = `
                    <span class="icon-wrapper ${shapeClass} flex items-center justify-center mr-3">
                        ${iconHtml}
                    </span>

                    <span class="category-name flex-1 font-medium text-gray-700">${safeNama}</span>

                    <div class="category-actions flex gap-2">
                        <button class="btn-icon btn-edit text-blue-500 hover:bg-blue-50 p-2 rounded"
                                data-id="${cat.id}"
                                data-nama="${safeNama}"
                                data-tipe="${cat.tipe}"
                                data-ikon="${safeDataIkon}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button class="btn-icon btn-delete text-red-500 hover:bg-red-50 p-2 rounded"
                                data-id="${cat.id}"
                                data-nama="${safeNama}">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                `;

                if (cat.tipe === 'pemasukan') {
                    listPemasukan.appendChild(item);
                } else {
                    listPengeluaran.appendChild(item);
                }
            });
            // Re-attach event listener
            document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', handleEditClick));
            document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', handleDeleteClick));
        }

        // Kode fungsi reset pilihan ikon di modal
        // --- Helper: Reset Icon Selection ---
        function resetIconSelection() {
            const container = document.getElementById('dynamic-icon-grid');
            if (container) {
                container.querySelectorAll('.icon-option').forEach(el => {
                    el.classList.remove('active');
                });
            }
            document.getElementById('modal-kat-ikon').value = '';
        }

        // Kode fungsi membuka modal tambah kategori
        // --- 3. [CREATE] Fungsi Modal "Tambah Kategori" ---
        function openAddModal() {
            currentEditingId = null;
            modalForm.reset();
            resetIconSelection(); // Reset grid icon

            // [UBAH] Judul Default
            modalTitle.textContent = 'Tambah Kategori Baru';

            // [UBAH] Tampilkan Tab Pilihan Tipe
            if(tabContainer) tabContainer.style.display = 'flex';

            modalSubmitBtn.textContent = 'Tambah Kategori';
            modalMessage.textContent = '';
            setActiveTab('pengeluaran'); // Default tab
            renderIconGrid('pengeluaran'); // Render icon grid
            modalOverlay.style.display = 'flex';
        }
// Kode fungsi membuka modal edit kategori
        
        // --- 4. [UPDATE] Fungsi Modal "Edit Kategori" ---
        function handleEditClick(e) {
            // Ambil data dari tombol
            const btn = e.currentTarget; // Gunakan currentTarget agar aman jika klik icon
            currentEditingId = btn.dataset.id;
            const tipe = btn.dataset.tipe; // 'pemasukan' atau 'pengeluaran'

            modalForm.reset();
            resetIconSelection(); // Reset dulu seleksi lama

            // [LOGIKA BARU] Ubah Judul Sesuai Tipe
            // Huruf pertama besar (Capitalize)
            const tipeCapitalized = tipe.charAt(0).toUpperCase() + tipe.slice(1);
            modalTitle.textContent = `Edit Kategori ${tipeCapitalized}`;

            // [LOGIKA BARU] Sembunyikan Tab Pilihan Tipe (Sesuai Gambar 2)
            // Karena saat edit, user biasanya tidak boleh ubah tipe (dari pemasukan jadi pengeluaran)
            if(tabContainer) tabContainer.style.display = 'none';

            modalSubmitBtn.textContent = 'Simpan Perubahan';
            modalMessage.textContent = '';

            // Isi Form dengan data lama
            inputNama.value = btn.dataset.nama;
            inputTipe.value = tipe; // Set tipe di hidden input

            // Render grid dan set active icon
            renderIconGrid(tipe);

            // Auto-select Icon di Grid
            if (btn.dataset.ikon) {
                selectIconLogic(btn.dataset.ikon, tipe);
                document.getElementById('modal-kat-ikon').value = btn.dataset.ikon;
            }

            modalOverlay.style.display = 'flex';
        }

        let categoryIdToDelete = null;
        const deleteModalOverlay = document.getElementById('delete-modal-overlay');
        const deleteTargetName = document.getElementById('delete-target-name');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
// Kode fungsi menangani klik tombol hapus kategori
        
        // --- 5. [DELETE] Fungsi Hapus Kategori ---
        function handleDeleteClick(e) {
            const btn = e.currentTarget;
            const id = btn.dataset.id;
            const nama = btn.dataset.nama;

            // Simpan ID ke variabel global
            categoryIdToDelete = id;

            // Update teks di modal
            deleteTargetName.textContent = nama;

            // Tampilkan Modal
            deleteModalOverlay.style.display = 'flex';
        }

        // --- 2. [ACTION] Fungsi Saat Tombol "Ya, Hapus" Diklik ---
        confirmDeleteBtn.addEventListener('click', async function() {
            if (!categoryIdToDelete) return;

            // Ubah tombol jadi loading
            const originalText = confirmDeleteBtn.textContent;
            confirmDeleteBtn.textContent = 'Menghapus...';
            confirmDeleteBtn.disabled = true;

            try {
                const response = await fetch(`${API_CATEGORIES}/${categoryIdToDelete}`, {
                    method: 'DELETE',
                    headers: API_HEADERS
                });

                if (response.status === 204 || response.ok) {
                    // Sukses
                    closeDeleteModal();
                    fetchCategories(); // Refresh list
                    // Opsional: Tampilkan notifikasi sukses kecil (Toast)
                } else {
                    const result = await response.json();
                    alert('Gagal: ' + (result.message || 'Error server'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal terhubung ke server.');
            } finally {
                // Reset Tombol
                confirmDeleteBtn.textContent = originalText;
                confirmDeleteBtn.disabled = false;
            }
        });

        const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function() {
                closeDeleteModal();
            });
        }
// Kode fungsi menutup modal konfirmasi hapus
        
        // --- 4. Helper Tutup Modal Hapus ---
        function closeDeleteModal() {
            const deleteModalOverlay = document.getElementById('delete-modal-overlay');
            if (deleteModalOverlay) {
                deleteModalOverlay.style.display = 'none';
            }
            categoryIdToDelete = null; // Reset ID
        }

        // Kode fungsi simpan kategori (Tambah/Edit)
        // --- 6. [CREATE/UPDATE] Fungsi Submit Form ---
        // --- 6. [CREATE/UPDATE] Fungsi Submit Form (FIXED) ---
        async function handleFormSubmit(e) {
            e.preventDefault();
            modalMessage.textContent = 'Menyimpan...';

            const formData = new FormData(modalForm);
            const data = Object.fromEntries(formData.entries());

            // Tambahkan prefix folder type ke ikon jika perlu
            if (data.ikon && !data.ikon.includes('/')) {
                data.ikon = `${data.tipe}/${data.ikon}`;
            }

            let url = API_CATEGORIES;

            // [FIX] Selalu gunakan POST agar aman di hosting
            let method = 'POST';

            // Jika sedang Edit, tambahkan ID ke URL & sisipkan _method: PUT
            if (currentEditingId) {
                url = `${API_CATEGORIES}/${currentEditingId}`;
                data._method = 'PUT'; // Trick Laravel agar membaca ini sebagai PUT
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: API_HEADERS,
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    closeModal();
                    fetchCategories(); // Refresh list
                    // Opsional: Tampilkan alert sukses
                } else if (response.status === 422) {
                    // Error Validasi Laravel
                    const errors = result.errors;
                    const firstError = Object.values(errors)[0][0];
                    modalMessage.textContent = 'Error: ' + firstError;
                } else {
                    modalMessage.textContent = 'Error: ' + (result.message || 'Terjadi kesalahan server.');
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                modalMessage.textContent = 'Gagal terhubung ke server.';
            }
        }
// Kode fungsi menutup modal kategori
        function closeModal() {
            modalOverlay.style.display = 'none';
        }
        // Kode fungsi mengatur tab aktif (Pemasukan/Pengeluaran)
        function closeModal(){
            modalOverlay.style.display = 'none';
        }
        function setActiveTab(tipe) {
            inputTipe.value = tipe;
            renderIconGrid(tipe); // Render icon grid saat ganti tab
            modalTabs.forEach(tab => {
                if (tab.dataset.tabType === tipe) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
        // Kode fungsi menangani perpindahan kategori via drag and drop
                }
            });
        }
        // --- 8. [DRAG & DROP] Update Tipe Kategori (FIXED) ---
        async function handleCategoryDrop(event) {
            const categoryId = event.item.dataset.id;

            // Validasi ID
            if (!categoryId) {
                console.error("ID Kategori tidak ditemukan pada elemen");
                return;
            }

            // Tentukan tipe baru berdasarkan container tujuan
            const newTipe = event.to.id === 'category-list-pemasukan' ? 'pemasukan' : 'pengeluaran';
            const oldTipe = newTipe === 'pemasukan' ? 'pengeluaran' : 'pemasukan';

            try {
                // [FIX] Gunakan POST dengan _method: 'PUT' agar aman di semua hosting
                const response = await fetch(`${API_CATEGORIES}/${categoryId}`, {
                    method: 'POST',
                    headers: API_HEADERS,
                    body: JSON.stringify({
                        _method: 'PUT', // Memberitahu Laravel ini sebenarnya PUT
                        tipe: newTipe
                    })
                });

                if (!response.ok) {
                    throw new Error('Gagal update di server');
                }

                // --- UPDATE UI LANGSUNG (Tanpa Refresh) ---

                // 1. Update data-tipe pada tombol edit
                const btnEdit = event.item.querySelector('.btn-edit');
                if (btnEdit) btnEdit.dataset.tipe = newTipe;

                // 2. Update Warna & Shape Icon
                const iconWrapper = event.item.querySelector('.icon-wrapper');
                if (iconWrapper) {
                    // Reset class lama
                    iconWrapper.classList.remove('icon-shape-pemasukan', 'icon-shape-pengeluaran', 'bg-green-100', 'bg-red-100', 'text-green-600', 'text-red-600');

                    // Tambah class baru
                    if (newTipe === 'pemasukan') {
                        iconWrapper.classList.add('icon-shape-pemasukan', 'bg-green-100', 'text-green-600');
                    } else {
                        iconWrapper.classList.add('icon-shape-pengeluaran', 'bg-red-100', 'text-red-600');
                    }

                    // 3. Update Source Gambar Icon (Agar pindah folder warna)
                    const iconImg = iconWrapper.querySelector('img');
                    if (iconImg) {
                        const currentSrc = iconImg.src;
                        // Ambil nama file saja (misal: Button.png)
                        const filename = currentSrc.substring(currentSrc.lastIndexOf('/') + 1);
                        // Set path baru
                        iconImg.src = `{{ asset('icons') }}/${newTipe}/${filename}`;
                    }
                }

                console.log(`Berhasil memindahkan kategori ${categoryId} ke ${newTipe}`);

            } catch (error) {
                console.error('Error dropping category:', error);

                // [ROLLBACK] Kembalikan item ke tempat asal jika error
                event.from.appendChild(event.item);
                alert('Gagal memindahkan kategori. Silakan coba lagi.');
            }
        }

        // --- Event Listeners ---
        openAddModalBtn.addEventListener('click', openAddModal);
        modalCloseBtn.addEventListener('click', closeModal);
        modalCancelBtn.addEventListener('click', closeModal);
        modalForm.addEventListener('submit', handleFormSubmit);

        modalTabs.forEach(tab => {
            tab.addEventListener('click', () => setActiveTab(tab.dataset.tabType));
        });

        // --- [BARU] Inisialisasi SortableJS (Drag-and-Drop) ---
        // UX Mobile: user harus HOLD (long-press) dulu baru bisa drag,
        // supaya scroll list kategori tetap nyaman.
        const sortableCommonOptions = {
            group: 'kategori',
            animation: 150,
            delay: 300,
            delayOnTouchOnly: true,
            touchStartThreshold: 8,
            onEnd: handleCategoryDrop
        };

        Sortable.create(listPemasukan, sortableCommonOptions);
        Sortable.create(listPengeluaran, sortableCommonOptions);

        // Kode fungsi render grid ikon di modal
        // --- Panggilan Awal ---
        fetchCategories(); // Ambil data saat halaman dimuat

        // --- Fungsi Render Icon Grid ---
        function renderIconGrid(type) { // type = 'pemasukan' atau 'pengeluaran'
            const container = document.getElementById('dynamic-icon-grid');
            if (!container) return;

            container.innerHTML = ''; // Bersihkan isi lama

            // Ganti Class Container untuk CSS Warna
            container.className = `icon-picker-container grid-${type}`;

            // Tentukan folder asal (pemasukan/pengeluaran)
            const folder = type;

            iconFiles.forEach(filename => {
                const div = document.createElement('div');
                div.className = 'icon-option';

                // Buat Gambar
                const img = document.createElement('img');
                // Path default (Warna Hijau/Biru)
                img.src = `{{ asset('icons') }}/${folder}/${filename}`;
                img.dataset.filename = filename; // Simpan nama file asli
                img.alt = 'icon';

                // Event Klik (Logic Swap Gambar)
                div.onclick = function() {
                    selectIconLogic(filename, type);
                };

                div.appendChild(img);
                container.appendChild(div);
            });

            // Reset input hidden
            document.getElementById('modal-kat-ikon').value = '';

            // --- Setup scroll event untuk menampilkan/menyembunyikan scrollbar ---
            let scrollTimeout;
            container.addEventListener('scroll', function() {
                // Tambah class 'scrolling' saat user scroll
                container.classList.add('scrolling');

                // Clear timeout sebelumnya
                clearTimeout(scrollTimeout);

                // Hapus class 'scrolling' 1 detik setelah scroll berhenti
                scrollTimeout = setTimeout(() => {
                    container.classList.remove('scrolling');
                }, 1000);
        // Kode fungsi menangani pemilihan ikon di grid
            });
        }

        // --- Fungsi Pilih Icon ---
        function selectIconLogic(filename, type) {
            const container = document.getElementById('dynamic-icon-grid');

            // 1. Reset semua icon lain
            container.querySelectorAll('.icon-option').forEach(el => {
                el.classList.remove('active');
                const img = el.querySelector('img');
                // Balikin ke folder asal (pemasukan/pengeluaran)
                const originalFilename = img.dataset.filename;
                img.src = `{{ asset('icons') }}/${type}/${originalFilename}`;
            });

            // 2. Cari elemen dengan filename yang sesuai dan set sebagai active
            const targetOption = Array.from(container.querySelectorAll('.icon-option')).find(el => {
                const img = el.querySelector('img');
                return img.dataset.filename === filename;
            });

            if (targetOption) {
                targetOption.classList.add('active');
                const activeImg = targetOption.querySelector('img');
                // Gunakan folder 'netral' untuk icon putih/active
                activeImg.src = `{{ asset('icons/netral') }}/${filename}`;
            }

            // 3. Simpan ke Input Hidden
            document.getElementById('modal-kat-ikon').value = filename;
        }

        // Render icon grid default saat halaman dimuat
        renderIconGrid('pengeluaran');
    });
</script>
@endpush
