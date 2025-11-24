@extends('layouts.app')

@section('title', 'Kategori')

@section('content')

<div class="content-card-category">
    <div class="card-body-category add-section-category">
        <h3 class="card-title-category">Tambah Kategori Baru</h3>
        <button class="btn-gradient-category" id="open-add-modal-btn">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

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
                
                <div class="form-group icon-modal">
                    <label class="block text-gray-700 text-sm font-bold mb-3">Pilih Ikon</label>

                    <input type="hidden" name="ikon" id="modal-ikon" required>

                    <div class="icon-picker-container">
                        
                        <div class="icon-option" onclick="selectIcon(this, 'logo1.png')">
                            <img src="{{ asset('icons/logo1.png') }}" alt="logo1">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo2.png')">
                            <img src="{{ asset('icons/logo2.png') }}" alt="logo2">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo3.png')">
                            <img src="{{ asset('icons/logo3.png') }}" alt="logo3">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo4.png')">
                            <img src="{{ asset('icons/logo4.png') }}" alt="logo4">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo5.png')">
                            <img src="{{ asset('icons/logo5.png') }}" alt="logo5">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo6.png')">
                            <img src="{{ asset('icons/logo6.png') }}" alt="logo6">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo7.png')">
                            <img src="{{ asset('icons/logo7.png') }}" alt="logo7">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo8.png')">
                            <img src="{{ asset('icons/logo8.png') }}" alt="logo8">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo9.png')">
                            <img src="{{ asset('icons/logo9.png') }}" alt="logo9">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo10.png')">
                            <img src="{{ asset('icons/logo10.png') }}" alt="logo10">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo11.png')">
                            <img src="{{ asset('icons/logo11.png') }}" alt="logo11">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo12.png')">
                            <img src="{{ asset('icons/logo12.png') }}" alt="logo12">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo13.png')">
                            <img src="{{ asset('icons/logo13.png') }}" alt="logo13">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo14.png')">
                            <img src="{{ asset('icons/logo14.png') }}" alt="logo14">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo15.png')">
                            <img src="{{ asset('icons/logo15.png') }}" alt="logo15">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo16.png')">
                            <img src="{{ asset('icons/logo16.png') }}" alt="logo16">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo17.png')">
                            <img src="{{ asset('icons/logo17.png') }}" alt="logo17">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo18.png')">
                            <img src="{{ asset('icons/logo18.png') }}" alt="logo18">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo19.png')">
                            <img src="{{ asset('icons/logo19.png') }}" alt="logo19">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo20.png')">
                            <img src="{{ asset('icons/logo20.png') }}" alt="logo20">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo21.png')">
                            <img src="{{ asset('icons/logo21.png') }}" alt="logo21">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo22.png')">
                            <img src="{{ asset('icons/logo22.png') }}" alt="logo22">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo23.png')">
                            <img src="{{ asset('icons/logo23.png') }}" alt="logo23">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo24.png')">
                            <img src="{{ asset('icons/logo24.png') }}" alt="logo24">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo25.png')">
                            <img src="{{ asset('icons/logo25.png') }}" alt="logo25">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo26.png')">
                            <img src="{{ asset('icons/logo26.png') }}" alt="logo26">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo27.png')">
                            <img src="{{ asset('icons/logo27.png') }}" alt="logo27">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo28.png')">
                            <img src="{{ asset('icons/logo28.png') }}" alt="logo28">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo29.png')">
                            <img src="{{ asset('icons/logo29.png') }}" alt="logo29">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo30.png')">
                            <img src="{{ asset('icons/logo30.png') }}" alt="logo30">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo31.png')">
                            <img src="{{ asset('icons/logo31.png') }}" alt="logo31">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo32.png')">
                            <img src="{{ asset('icons/logo32.png') }}" alt="logo32">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo33.png')">
                            <img src="{{ asset('icons/logo33.png') }}" alt="logo33">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo34.png')">
                            <img src="{{ asset('icons/logo34.png') }}" alt="logo34">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo35.png')">
                            <img src="{{ asset('icons/logo35.png') }}" alt="logo35">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo36.png')">
                            <img src="{{ asset('icons/logo36.png') }}" alt="logo36">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo37.png')">
                            <img src="{{ asset('icons/logo37.png') }}" alt="logo37">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo38.png')">
                            <img src="{{ asset('icons/logo38.png') }}" alt="logo38">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo39.png')">
                            <img src="{{ asset('icons/logo39.png') }}" alt="logo39">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo40.png')">
                            <img src="{{ asset('icons/logo40.png') }}" alt="logo40">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo41.png')">
                            <img src="{{ asset('icons/logo41.png') }}" alt="logo41">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo42.png')">
                            <img src="{{ asset('icons/logo42.png') }}" alt="logo42">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo43.png')">
                            <img src="{{ asset('icons/logo43.png') }}" alt="logo43">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo44.png')">
                            <img src="{{ asset('icons/logo44.png') }}" alt="logo44">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo45.png')">
                            <img src="{{ asset('icons/logo45.png') }}" alt="logo45">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo46.png')">
                            <img src="{{ asset('icons/logo46.png') }}" alt="logo46">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo47.png')">
                            <img src="{{ asset('icons/logo47.png') }}" alt="logo47">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo48.png')">
                            <img src="{{ asset('icons/logo48.png') }}" alt="logo48">
                        </div>

                        </div>
                    
                    <small id="icon-error" class="text-red-500 text-xs hidden mt-1">Silakan pilih ikon terlebih dahulu.</small>
                </div>
                
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" id="modal-cancel-btn">Batal</button>
                <button type="submit" class="btn btn-primary-modal" id="modal-submit-btn">Tambah Kategori</button>
            </div>
        </form>
        
    </div>
</div>

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
        const inputIkon = document.getElementById('modal-ikon');
        
        let currentEditingId = null; 

        function escapeHtml(text) {
            if (!text) return text;
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
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
                    // Pastikan script ini ada di file .blade.php agar
                    const iconUrl = `{{ asset('icons') }}/${cat.ikon}`;
                    
                    // Gunakan class Tailwind (w-5 h-5) agar ukurannya pas di tengah lingkaran
                    iconHtml = `<img src="${iconUrl}" alt="icon" class="w-6 h-6 object-contain">`;
                    
                } else {
                    
                    // Render sebagai FontAwesome (Data Lama / Default)
                    const iconClass = cat.ikon ? escapeHtml(cat.ikon) : 'fa-solid fa-tag';
                    iconHtml = `<i class="${iconClass}"></i>`;
                }

                // 2. LOGIKA WARNA BACKGROUND
                const bgClass = cat.tipe === 'pemasukan' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600';
                
                // 3. KEAMANAN XSS
                const safeNama = escapeHtml(cat.nama_kategori);
                // Jika ikon FontAwesome, aman karena sudah di-escape di atas. Jika Image, aman karena path server.
                // Kita simpan value asli ikon untuk data-ikon tombol edit (hati-hati di sini)
                const safeDataIkon = cat.ikon ? escapeHtml(cat.ikon) : ''; 

                // 4. SUSUN HTML KE DALAM ITEM
                item.innerHTML = `
                    <span class="icon-wrapper ${bgClass} w-10 h-10 flex items-center justify-center rounded-full mr-3">
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
            modalOverlay.style.display = 'flex';
        }
        
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
            
            // Auto-select Icon di Grid
            if (btn.dataset.ikon) {
                // Cari elemen icon di grid yang sesuai dan tambahkan class selected
                const targetIcon = document.querySelector(`.icon-option[onclick*="'${btn.dataset.ikon}'"]`);
                if (targetIcon) {
                    selectIcon(targetIcon, btn.dataset.ikon);
                }
                document.getElementById('modal-ikon').value = btn.dataset.ikon;
            }
            
            modalOverlay.style.display = 'flex';
        }

        let categoryIdToDelete = null;
        const deleteModalOverlay = document.getElementById('delete-modal-overlay');
        const deleteTargetName = document.getElementById('delete-target-name');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

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

        // --- 4. Helper Tutup Modal Hapus ---
        function closeDeleteModal() {
            const deleteModalOverlay = document.getElementById('delete-modal-overlay');
            if (deleteModalOverlay) {
                deleteModalOverlay.style.display = 'none';
            }
            categoryIdToDelete = null; // Reset ID
        }

        // --- 6. [CREATE/UPDATE] Fungsi Submit Form ---
        async function handleFormSubmit(e) {
            e.preventDefault();
            modalMessage.textContent = 'Menyimpan...';
            
            const formData = new FormData(modalForm);
            const data = Object.fromEntries(formData.entries());

            let url = API_CATEGORIES;
            let method = 'POST';

            if (currentEditingId) {
                url = `${API_CATEGORIES}/${currentEditingId}`;
                method = 'PUT';
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
                    fetchCategories(); 
                } else if (response.status === 422) {
                    const firstError = Object.values(result.errors)[0][0];
                    modalMessage.textContent = 'Error: ' + firstError;
                } else {
                    modalMessage.textContent = 'Error: ' + (result.message || 'Terjadi kesalahan server.');
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                modalMessage.textContent = 'Gagal terhubung ke server.';
            }
        }
        
        // --- 7. Helper Modal (Tutup & Ganti Tab) ---
        function closeModal() {
            modalOverlay.style.display = 'none';
        }
        function setActiveTab(tipe) {
            inputTipe.value = tipe;
            modalTabs.forEach(tab => {
                if (tab.dataset.tabType === tipe) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }

        // --- 8. [DRAG & DROP] Update Tipe Kategori & UI ---
        async function handleCategoryDrop(event) {
            const categoryId = event.item.dataset.id;
            
            // Tentukan tipe baru berdasarkan list tujuan
            const newTipe = event.to.id === 'category-list-pemasukan' ? 'pemasukan' : 'pengeluaran';
            
            // Simpan tipe lama untuk rollback jika gagal
            const oldTipe = newTipe === 'pemasukan' ? 'pengeluaran' : 'pemasukan';

            try {
                const response = await fetch(`${API_CATEGORIES}/${categoryId}`, {
                    method: 'PUT',
                    headers: API_HEADERS,
                    body: JSON.stringify({ tipe: newTipe })
                });

                if (!response.ok) {
                    throw new Error('Gagal update');
                }
                
                // --- [PERBAIKAN UTAMA DI SINI] ---
                // Update data atribut pada tombol edit agar modal judulnya benar
                const btnEdit = event.item.querySelector('.btn-edit');
                if (btnEdit) {
                    btnEdit.dataset.tipe = newTipe; // Ubah data-tipe="pemasukan"
                }

                // Update Visual Warna Background Icon (Hijau <-> Biru)
                const iconWrapper = event.item.querySelector('.icon-wrapper-category');
                if (iconWrapper) {
                    // Hapus kelas warna lama & baru (biar bersih)
                    iconWrapper.classList.remove('bg-green-category', 'bg-blue-category');
                    
                    // Tambahkan kelas warna baru sesuai tipe
                    if (newTipe === 'pemasukan') {
                        iconWrapper.classList.add('bg-green-category');
                    } else {
                        iconWrapper.classList.add('bg-blue-category');
                    }
                }

            } catch (error) {
                console.error('Error dropping category:', error);
                event.from.appendChild(event.item); // Kembalikan item jika error
                
                // Kembalikan data-tipe jika error (opsional tapi bagus)
                const btnEdit = event.item.querySelector('.btn-edit');
                if (btnEdit) btnEdit.dataset.tipe = oldTipe;

                alert('Gagal memindahkan kategori. Periksa koneksi internet.');
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
        Sortable.create(listPemasukan, {
            group: 'kategori', // Nama grup yang sama
            animation: 150,
            onEnd: handleCategoryDrop // Panggil fungsi saat drop selesai
        });

        Sortable.create(listPengeluaran, {
            group: 'kategori', // Nama grup yang sama
            animation: 150,
            onEnd: handleCategoryDrop // Panggil fungsi saat drop selesai
        });

        // --- Panggilan Awal ---
        fetchCategories(); // Ambil data saat halaman dimuat
    });

        // Fungsi Pilih Icon
    function selectIcon(element, filename) {
        // 1. Hapus class 'selected' dari semua icon yang ada
        const allIcons = document.querySelectorAll('.icon-option');
        allIcons.forEach(el => el.classList.remove('selected'));
        
        // 2. Tambahkan class 'selected' ke elemen yang diklik
        element.classList.add('selected');
        
        // 3. Masukkan nama file ke input hidden agar terkirim ke database
        document.getElementById('modal-ikon').value = filename;
    }

    // Fungsi Reset (Panggil ini saat tombol "Tambah Kategori" diklik untuk membuka modal)
    function resetIconSelection() {
        document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
        document.getElementById('modal-ikon').value = '';
    }
</script>
@endpush