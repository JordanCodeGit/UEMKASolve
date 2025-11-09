@extends('layouts.app')

@section('title', 'Kategori')

@section('content')

<div class="content-card">
    <div class="card-body add-category-card">
        <h3 class="card-title">Tambah Kategori Baru</h3>
        <button class="btn btn-gradient" id="open-add-modal-btn">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

<div class="content-card category-list-card">
    <div class="card-header">
        <h3 class="card-title dot-pemasukan">Kategori Pemasukan</h3>
    </div>
    <div class="card-body">
        <div class="category-grid-large" id="category-list-pemasukan">
            <p>Memuat kategori...</p>
        </div>
    </div>
</div>

<div class="content-card category-list-card">
    <div class="card-header">
        <h3 class="card-title dot-pengeluaran">Kategori Pengeluaran</h3>
    </div>
    <div class="card-body">
        <div class="category-grid-large" id="category-list-pengeluaran">
            <p>Memuat kategori...</p>
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
                
                <div id="modal-form-message" style="color: red; margin-bottom: 15px; font-size: 14px;"></div>

                <div class="modal-tabs">
                    <button type="button" class="modal-tab-item active" data-tab-type="pengeluaran">Pengeluaran</button>
                    <button type="button" class="modal-tab-item" data-tab-type="pemasukan">Pemasukan</button>
                    <input type="hidden" id="modal-tipe" name="tipe" value="pengeluaran">
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-nama-kategori">Nama Kategori</label>
                    <input type="text" id="modal-nama-kategori" name="nama_kategori" class="form-input-modal" placeholder="Masukkan nama kategori..." required>
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-ikon">Pilih Ikon (contoh: 'fa-solid fa-store')</label>
                    <input type="text" id="modal-ikon" name="ikon" class="form-input-modal" placeholder="fa-solid fa-store">
                </div>
                
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" id="modal-cancel-btn">Batal</button>
                <button type="submit" class="btn btn-primary-modal" id="modal-submit-btn">Tambah Kategori</button>
            </div>
        </form>
        
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
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
        
        // Input Form
        const inputTipe = document.getElementById('modal-tipe');
        const inputNama = document.getElementById('modal-nama-kategori');
        const inputIkon = document.getElementById('modal-ikon');
        
        let currentEditingId = null; 

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
            listPemasukan.innerHTML = '<p>Memuat...</p>';
            listPengeluaran.innerHTML = '<p>Memuat...</p>';

            try {
                const response = await fetch(API_CATEGORIES, { headers: API_HEADERS_GET });
                if (response.status === 401) return window.location.href = '{{ url("/login") }}';
                
                const categories = await response.json();
                renderCategories(categories);

            } catch (error) {
                console.error('Error fetching categories:', error);
                listPemasukan.innerHTML = '<p style="color: red;">Gagal memuat data.</p>';
            }
        }

        // --- 2. Fungsi Merender HTML Kategori ---
        function renderCategories(categories) {
            listPemasukan.innerHTML = '';
            listPengeluaran.innerHTML = '';

            categories.forEach(cat => {
                const item = document.createElement('div');
                item.className = 'category-item-large';
                
                // [MODIFIKASI] Tambahkan data-id ke item utama untuk drag-and-drop
                item.dataset.id = cat.id; 
                
                item.innerHTML = `
                    <span class="icon-wrapper ${cat.tipe === 'pemasukan' ? 'bg-green-light' : 'bg-blue-light'}">
                        <i class="${cat.ikon || 'fa-solid fa-question'}"></i>
                    </span>
                    <span class="category-name">${cat.nama_kategori}</span>
                    <div class="category-actions">
                        <button class="btn-icon btn-edit" 
                                data-id="${cat.id}" 
                                data-nama="${cat.nama_kategori}" 
                                data-tipe="${cat.tipe}" 
                                data-ikon="${cat.ikon || ''}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button class="btn-icon btn-delete" data-id="${cat.id}" data-nama="${cat.nama_kategori}">
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

            // Tambahkan event listener ke tombol yang baru dibuat
            document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', handleEditClick));
            document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', handleDeleteClick));
        }

        // --- 3. [CREATE] Fungsi Modal "Tambah Kategori" ---
        function openAddModal() {
            currentEditingId = null;
            modalForm.reset();
            modalTitle.textContent = 'Tambah Kategori Baru';
            modalSubmitBtn.textContent = 'Tambah Kategori';
            modalMessage.textContent = '';
            setActiveTab('pengeluaran');
            modalOverlay.style.display = 'flex';
        }
        
        // --- 4. [UPDATE] Fungsi Modal "Edit Kategori" ---
        function handleEditClick(e) {
            const btn = e.currentTarget;
            currentEditingId = btn.dataset.id; 

            modalForm.reset();
            modalTitle.textContent = 'Edit Kategori';
            modalSubmitBtn.textContent = 'Simpan Perubahan';
            modalMessage.textContent = '';
            
            inputNama.value = btn.dataset.nama;
            inputIkon.value = btn.dataset.ikon;
            setActiveTab(btn.dataset.tipe);
            
            modalOverlay.style.display = 'flex';
        }

        // --- 5. [DELETE] Fungsi Hapus Kategori ---
        async function handleDeleteClick(e) {
            const btn = e.currentTarget;
            const id = btn.dataset.id;
            const nama = btn.dataset.nama;

            if (confirm(`Anda yakin ingin menghapus kategori "${nama}"?`)) {
                try {
                    const response = await fetch(`${API_CATEGORIES}/${id}`, {
                        method: 'DELETE',
                        headers: API_HEADERS
                    });

                    if (response.status === 204) {
                        fetchCategories(); // Sukses, muat ulang
                    } else {
                        // Cek jika ada error (misal: kategori masih dipakai)
                        const result = await response.json(); 
                        alert('Gagal menghapus: ' + (result.message || 'Error server.'));
                    }
                } catch (error) {
                    console.error('Error deleting category:', error);
                    alert('Gagal terhubung ke server.');
                }
            }
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

        // --- 8. [DRAG & DROP - BARU] Fungsi Update Tipe Kategori ---
        async function handleCategoryDrop(event) {
            // Dapatkan ID item yang di-drag
            const categoryId = event.item.dataset.id;
            
            // Tentukan tipe baru berdasarkan list tujuan
            const newTipe = event.to.id === 'category-list-pemasukan' ? 'pemasukan' : 'pengeluaran';
            
            // Siapkan data untuk dikirim ke API
            const data = {
                tipe: newTipe
            };

            try {
                // Panggil API Update (PUT)
                const response = await fetch(`${API_CATEGORIES}/${categoryId}`, {
                    method: 'PUT',
                    headers: API_HEADERS,
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    // Jika gagal, batalkan perpindahan di UI
                    console.error('Gagal update tipe kategori via drag');
                    event.from.appendChild(event.item); // Kembalikan item
                    alert('Gagal memperbarui tipe kategori.');
                }
                
                // Jika sukses, BE sudah update (kita tidak perlu refresh)
                
            } catch (error) {
                console.error('Error dropping category:', error);
                event.from.appendChild(event.item); // Kembalikan item
                alert('Gagal terhubung ke server.');
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
</script>
@endpush