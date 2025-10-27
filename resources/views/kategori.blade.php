@extends('layouts.app')

@section('title', 'Kategori')

@section('content')

<div class="content-card">
    <div class="card-body add-category-card">
        <h3 class="card-title">Tambah Kategori Baru</h3>
        <button class="btn btn-gradient">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

<div class="content-card category-list-card">
    <div class="card-header">
        <h3 class="card-title dot-pemasukan">Kategori Pemasukan</h3>
    </div>
    <div class="card-body">
        <div class="category-grid-large">
            
            <div class="category-item-large">
                <span class="icon-wrapper bg-green-light">
                    <i class="fa-solid fa-shopping-cart"></i>
                </span>
                <span class="category-name">Penjualan Produk</span>
                <div class="category-actions">
                    <button class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn-icon btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
            </div>
    </div>
</div>

<div class="content-card category-list-card">
    <div class="card-header">
        <h3 class="card-title dot-pengeluaran">Kategori Pengeluaran</h3>
    </div>
    <div class="card-body">
        <div class="category-grid-large">
            
            <div class="category-item-large">
                <span class="icon-wrapper bg-blue-light">
                    <i class="fa-solid fa-users"></i>
                </span>
                <span class="category-name">Gaji Karyawan</span>
                <div class="category-actions">
                    <button class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn-icon btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
            
            <div class="category-item-large">
                <span class="icon-wrapper bg-blue-light">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                <span class="category-name">Belanja Bahan Baku</span>
                <div class="category-actions">
                    <button class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn-icon btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
            
            <div class="category-item-large">
                <span class="icon-wrapper bg-blue-light">
                    <i class="fa-solid fa-building"></i>
                </span>
                <span class="category-name">Sewa Ruko</span>
                <div class="category-actions">
                    <button class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn-icon btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>

            <div class="category-item-large">
                <span class="icon-wrapper bg-blue-light">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                <span class="category-name">Penjualan Produk</span> <div class="category-actions">
                    <button class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="btn-icon btn-delete"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal-overlay" id="addCategoryModalOverlay">
    <div class="modal-box">
        
        <div class="modal-header">
            <h2>Tambah Kategori Baru</h2>
            <button class="modal-close-btn" id="closeAddCategoryModal">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            
            <div class="modal-tabs">
                <button class="modal-tab-item active" data-tab-type="pengeluaran">Pengeluaran</button>
                <button class="modal-tab-item" data-tab-type="pemasukan">Pemasukan</button>
            </div>
            
            <div class="form-group-modal">
                <label for="modal_nama_kategori">Nama Kategori</label>
                <input type="text" id="modal_nama_kategori" class="form-input-modal" placeholder="Masukkan nama kategori...">
            </div>
            
            <div class="form-group-modal">
                <label>Pilih Ikon</label>
                <div class="icon-picker-grid">
                    <div class="icon-placeholder selected"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                    <div class="icon-placeholder"></div>
                </div>
            </div>
            
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary-modal" id="cancelAddCategoryModal">Batal</button>
            <button class="btn btn-primary-modal">Tambah Kategori</button>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elemen-elemen Modal
        const modalOverlay = document.getElementById('addCategoryModalOverlay');
        const openModalBtn = document.querySelector('.add-category-card .btn-gradient'); // Tombol "Tambah Kategori" di header
        const closeModalBtn = document.getElementById('closeAddCategoryModal');
        const cancelModalBtn = document.getElementById('cancelAddCategoryModal');
        const modalTabs = document.querySelectorAll('.modal-tab-item');
        const iconPlaceholders = document.querySelectorAll('.icon-placeholder');

        // Fungsi untuk buka modal
        function openModal() {
            if (modalOverlay) {
                modalOverlay.style.display = 'flex';
            }
        }

        // Fungsi untuk tutup modal
        function closeModal() {
            if (modalOverlay) {
                modalOverlay.style.display = 'none';
            }
        }

        // Event Listener Tombol Buka Modal
        if (openModalBtn) {
            openModalBtn.addEventListener('click', openModal);
        }

        // Event Listener Tombol Tutup (X)
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }

        // Event Listener Tombol Batal
        if (cancelModalBtn) {
            cancelModalBtn.addEventListener('click', closeModal);
        }

        // Event Listener Klik di Overlay (luar modal box)
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function(event) {
                // Hanya tutup jika klik DI LUAR modal box
                if (event.target === modalOverlay) {
                    closeModal();
                }
            });
        }
        
        // Event Listener untuk Tabs di Modal
        modalTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                modalTabs.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                // Nanti Anda bisa tambahkan logika berdasarkan data-tab-type
            });
        });

        // Event Listener untuk Icon Picker
        iconPlaceholders.forEach(icon => {
            icon.addEventListener('click', function() {
                iconPlaceholders.forEach(item => item.classList.remove('selected'));
                this.classList.add('selected');
                // Nanti Anda simpan value ikon yang dipilih
            });
        });

    });
</script>

@endsection