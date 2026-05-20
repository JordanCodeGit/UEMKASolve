@extends('layouts.app')

@section('title', 'Anggota')

@section('content')
    <div class="anggota-page">
        <div class="anggota-header-card">
            <h2>Tambah Anggota Staf</h2>
            <button type="button" class="btn-primary-green" id="btn-open-member-modal">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Staf</span>
            </button>
        </div>

        <div class="anggota-list-card">
            @foreach (['sekretaris' => 'Sekretaris', 'bendahara' => 'Bendahara'] as $role => $label)
                <section class="anggota-group">
                    <div class="anggota-group-title">
                        <span class="anggota-dot anggota-dot--{{ $role }}"></span>
                        <h3>{{ $label }}</h3>
                    </div>

                    <div class="anggota-items" id="anggota-list-{{ $role }}">
                        @php
                            $roleMembers = $members->where('role', $role);
                        @endphp

                        @forelse ($roleMembers as $member)
                            <div class="anggota-item" data-member-id="{{ $member->id }}">
                                <div class="anggota-user">
                                    <span class="anggota-avatar anggota-avatar--{{ $role }}">
                                        <i class="fa-solid fa-users"></i>
                                    </span>
                                    <div>
                                        <strong>{{ $member->user->email }}</strong>
                                        <small>{{ $member->status === 'accepted' ? 'Aktif' : 'Menunggu undangan diterima' }}</small>
                                    </div>
                                </div>

                                <button type="button" class="anggota-delete" data-delete-member="{{ $member->id }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        @empty
                            <div class="anggota-empty">Belum ada anggota {{ strtolower($label) }}.</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <div class="modal-overlay" id="member-modal-overlay" style="display: none;">
        <div class="modal-box anggota-modal-box">
            <div class="modal-header">
                <h2>Tambah Anggota Staf</h2>
                <button class="modal-close-btn" type="button" id="member-modal-close">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form id="member-form">
                <div class="modal-body">
                    <div id="member-form-message"></div>

                    <div class="form-group-modal">
                        <label for="member-email">Gmail Anggota</label>
                        <input type="email" id="member-email" name="email" class="form-input-modal" placeholder="Masukkan akun gmail" required>
                    </div>

                    <div class="form-group-modal">
                        <label for="member-role">Kategori Anggota</label>
                        <select id="member-role" name="role" class="form-input-modal" required>
                            <option value="">Pilih kategori</option>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-modal" id="member-modal-cancel">Batal</button>
                    <button type="submit" class="btn btn-primary-modal" id="member-submit-btn">Undang Anggota</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('member-modal-overlay');
            const form = document.getElementById('member-form');
            const message = document.getElementById('member-form-message');
            const submitBtn = document.getElementById('member-submit-btn');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const openModal = () => {
                message.textContent = '';
                message.className = '';
                form.reset();
                overlay.style.display = 'flex';
            };

            const closeModal = () => overlay.style.display = 'none';

            document.getElementById('btn-open-member-modal').addEventListener('click', openModal);
            document.getElementById('member-modal-close').addEventListener('click', closeModal);
            document.getElementById('member-modal-cancel').addEventListener('click', closeModal);
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal();
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
                message.textContent = 'Mengirim undangan...';
                message.className = 'member-message-info';

                try {
                    const formData = new FormData(form);
                    const response = await fetch("{{ route('anggota.store') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(Object.fromEntries(formData.entries()))
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal mengundang anggota.');
                    }

                    let successText = result.message;
                    if (result.invitation_link) {
                        successText += ' Mailer aplikasi masih mode log. Link undangan localhost muncul di bawah.';
                    }
                    message.textContent = successText;
                    message.className = 'member-message-success';
                    if (result.invitation_link) {
                        const linkBox = document.createElement('div');
                        linkBox.className = 'member-invitation-link';
                        linkBox.innerHTML = `
                            <input type="text" readonly value="${result.invitation_link}">
                            <button type="button">Salin</button>
                        `;
                        message.appendChild(linkBox);
                        const copyBtn = linkBox.querySelector('button');
                        const linkInput = linkBox.querySelector('input');
                        copyBtn.addEventListener('click', async () => {
                            await navigator.clipboard.writeText(linkInput.value);
                            copyBtn.textContent = 'Tersalin';
                        });
                    } else {
                        setTimeout(() => window.location.reload(), 700);
                    }
                } catch (error) {
                    message.textContent = error.message;
                    message.className = 'member-message-error';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Undang Anggota';
                }
            });

            document.querySelectorAll('[data-delete-member]').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm('Hapus anggota ini?')) return;

                    const id = this.dataset.deleteMember;
                    const response = await fetch(`/anggota/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
@endpush
