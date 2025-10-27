@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')

<div class="content-card">

    <div class="tabs-nav-container">
        <nav class="tabs-nav">
            <a href="#" class="tab-item active" data-tab="usaha">
                <i class="fa-solid fa-shop"></i> Profil Usaha
            </a>
            <a href="#" class="tab-item" data-tab="akun">
                <i class="fa-solid fa-user-gear"></i> Profil Akun
            </a>
            <a href="#" class="tab-item" data-tab="kategori">
                <i class="fa-solid fa-tags"></i> Manajemen Kategori
            </a>
        </nav>
    </div>
    <div class="tab-pane active" data-tab-pane="usaha">
        <form action="#" method="POST" enctype="multipart/form-data">
            
            <div class="form-group-row">
                <label for="nama_usaha">Nama Usaha</label>
                <input type="text" id="nama_usaha" class="form-input" value="uemkas">
                <small>Nama usaha akan muncul di laporan PDF</small>
            </div>
            
            <div class="form-group-row">
                <label for="logo_usaha">Logo Usaha</label>
                <input type="file" id="logo_usaha" class="form-input-file">
                <small>Logo akan ditampilkan di laporan (Format: PNG, JPG, max 2MB)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            
        </form>
            <div class="stats-container">

                <div class="stat-card">
                    <div class="stat-card-top">
                        <h2>Rp 9.207.200,00</h2>
                        <span class="badge badge-green">
                            <i class="fa-solid fa-arrow-up"></i> 18%
                        </span>
                    </div>
                    <div class="stat-card-bottom">
                        <p>Total Pendapatan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-top">
                        <h2>Rp 5.041.050,00</h2>
                        <span class="badge badge-green">
                            <i class="fa-solid fa-arrow-up"></i> 9%
                        </span>
                    </div>
                    <div class="stat-card-bottom">
                        <p>Total Pengeluaran</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-top">
                        <h2>Rp 4.166.150,00</h2>
                        <span class="badge badge-grey">
                            9%
                        </span>
                    </div>
                    <div class="stat-card-bottom">
                        <p>Total Keuntungan</p>
                    </div>
                </div>
                
            </div>
    </div>
    </div>

    <div class="tab-pane" data-tab-pane="akun">
        <form action="#" method="POST">
            <div class="form-group-row">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" class="form-input" value="Budi Santoso">
            </div>
            
            <div class="form-group-row">
                <label for="email">Email</label>
                <input type="email" id="email" class="form-input" value="budi@uemka.com">
            </div>

            <hr class="form-divider">

            <h3 class="form-section-title">Ubah Password</h3>
            
            <div class="form-group-row">
                <label for="pass_saat_ini">Password Saat Ini</label>
                <input type="password" id="pass_saat_ini" class="form-input">
            </div>
            <div class="form-group-row">
                <label for="pass_baru">Password Baru</label>
                <input type="password" id="pass_baru" class="form-input">
            </div>
            <div class="form-group-row">
                <label for="konfirmasi_pass_baru">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_pass_baru" class="form-input">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <div class="tab-pane" data-tab-pane="kategori">
        <h3 class="form-section-title">Tambah Kategori Baru</h3>
        
        <form action="#" method="POST" class="add-category-form">
            <div class="form-group-row flex-grow">
                <input type="text" id="nama_kategori" class="form-input" placeholder="Nama kategori">
            </div>
            <div class="form-group-row">
                 <select class="form-input" style="min-width: 150px;">
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
            </div>
            <div class="form-group-row">
                <button type="submit" class="btn btn-primary btn-with-icon">
                    <i class="fa-solid fa-plus"></i> Tambah
                </button>
            </div>
        </form>

        <hr class="form-divider">

        <div class="category-section">
            <h4 class="category-section-title dot-pemasukan">Kategori Pemasukan</h4>
            <div class="category-grid">
                <div class="category-item">
                    <span>Penjualan Produk</span>
                    <button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button>
                </div>
                <div class="category-item">
                    <span>Jasa Konsultasi</span>
                    <button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button>
                </div>
                <div class="category-item">
                    <span>Investasi</span>
                    <button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        </div>
        
        <div class="category-section">
            <h4 class="category-section-title dot-pengeluaran">Kategori Pengeluaran</h4>
            <div class="category-grid">
                <div class="category-item"><span>Gaji Karyawan</span><button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button></div>
                <div class="category-item"><span>Sewa Tempat</span><button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button></div>
                <div class="category-item"><span>Utilitas</span><button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button></div>
                <div class="category-item"><span>Pemasaran</span><button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button></div>
                 <div class="category-item"><span>Bahan Baku</span><button class="category-delete-btn"><i class="fa-solid fa-trash-can"></i></button></div>
            </div>
        </div>

    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabLinks = document.querySelectorAll('.tab-item');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');

                tabLinks.forEach(item => item.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));

                this.classList.add('active');
                const targetPane = document.querySelector(`.tab-pane[data-tab-pane="${tabId}"]`);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    });
</script>

@endsection