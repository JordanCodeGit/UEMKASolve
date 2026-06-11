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

                                <button type="button" class="anggota-delete"
                                    data-delete-member="{{ $member->id }}"
                                    data-member-email="{{ $member->user->email }}"
                                    data-member-role="{{ $label }}">
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

    <div class="modal-overlay" id="member-delete-modal-overlay" style="display: none;">
        <div class="modal-box delete-modal-box">
            <div class="delete-icon-wrapper">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h2 class="delete-title">Hapus Anggota?</h2>

            <p class="delete-message">
                Anda akan menghapus akun <strong id="member-delete-target-email">...</strong>
                dari role <strong id="member-delete-target-role">...</strong>.
                <span class="text-danger-warning">
                    Anggota yang dihapus tidak lagi memiliki akses ke usaha ini.
                </span>
                Apakah Anda yakin ingin melanjutkan?
            </p>

            <div class="modal-footer delete-footer">
                <button type="button" class="btn btn-secondary-modal" id="member-delete-cancel">Batal</button>
                <button type="button" class="btn btn-danger-modal" id="member-delete-confirm">
                    Ya, Hapus Anggota
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('member-modal-overlay');
            const deleteOverlay = document.getElementById('member-delete-modal-overlay');
            const deleteTargetEmail = document.getElementById('member-delete-target-email');
            const deleteTargetRole = document.getElementById('member-delete-target-role');
            const deleteConfirmBtn = document.getElementById('member-delete-confirm');
            const deleteCancelBtn = document.getElementById('member-delete-cancel');
            const form = document.getElementById('member-form');
            const message = document.getElementById('member-form-message');
            const submitBtn = document.getElementById('member-submit-btn');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let memberDeleteTarget = null;

            const openModal = () => {
                message.textContent = '';
                message.className = '';
                form.reset();
                overlay.style.display = 'flex';
            };

            const closeModal = () => overlay.style.display = 'none';
            const closeDeleteModal = () => {
                deleteOverlay.style.display = 'none';
                memberDeleteTarget = null;
            };

            const showMemberToast = (type, text) => {
                if (window.showAppToast) {
                    window.showAppToast(type, text);
                    return;
                }

                let container = document.getElementById('app-alert-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'app-alert-container';
                    container.className = 'app-alert-container';
                    document.body.appendChild(container);
                }

                const isSuccess = type === 'success';
                const toast = document.createElement('div');
                toast.className = `alert-popup ${isSuccess ? 'alert-success' : 'alert-error'}`;
                toast.innerHTML = `
                    <div class="alert-icon">
                        <i class="fa-solid ${isSuccess ? 'fa-check' : 'fa-xmark'}"></i>
                    </div>
                    <div class="alert-message">
                        <strong>${isSuccess ? 'Berhasil!' : 'Gagal!'}</strong>
                        <span></span>
                    </div>
                    <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
                `;
                toast.querySelector('.alert-message span').textContent = text;
                toast.querySelector('.alert-close').addEventListener('click', () => toast.remove());
                container.appendChild(toast);
                setTimeout(() => toast.remove(), 4200);
            };

            const parseJsonResponse = async (response) => {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                }

                return {};
            };

            const refreshEmptyState = (role) => {
                const list = document.getElementById(`anggota-list-${role}`);
                if (!list || list.querySelector('.anggota-item')) return;

                const label = role === 'sekretaris' ? 'sekretaris' : 'bendahara';
                list.innerHTML = `<div class="anggota-empty">Belum ada anggota ${label}.</div>`;
            };

            document.getElementById('btn-open-member-modal').addEventListener('click', openModal);
            document.getElementById('member-modal-close').addEventListener('click', closeModal);
            document.getElementById('member-modal-cancel').addEventListener('click', closeModal);
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal();
            });
            deleteCancelBtn.addEventListener('click', closeDeleteModal);
            deleteOverlay.addEventListener('click', e => {
                if (e.target === deleteOverlay) closeDeleteModal();
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
                        successText += ' Link undangan tersedia di bawah.';
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
                button.addEventListener('click', function() {
                    memberDeleteTarget = {
                        id: this.dataset.deleteMember,
                        email: this.dataset.memberEmail || 'anggota ini',
                        roleLabel: this.dataset.memberRole || 'Anggota',
                        roleKey: this.closest('.anggota-items')?.id?.replace('anggota-list-', '') || '',
                        row: this.closest('.anggota-item'),
                    };

                    deleteTargetEmail.textContent = memberDeleteTarget.email;
                    deleteTargetRole.textContent = memberDeleteTarget.roleLabel;
                    deleteOverlay.style.display = 'flex';
                });
            });

            deleteConfirmBtn.addEventListener('click', async function() {
                if (!memberDeleteTarget) return;

                const originalText = deleteConfirmBtn.textContent;
                deleteConfirmBtn.disabled = true;
                deleteConfirmBtn.textContent = 'Menghapus...';

                try {
                    const response = await fetch(`/anggota/${memberDeleteTarget.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                    const result = await parseJsonResponse(response);

                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal menghapus anggota.');
                    }

                    memberDeleteTarget.row?.remove();
                    refreshEmptyState(memberDeleteTarget.roleKey);
                    closeDeleteModal();
                    showMemberToast('success', result.message || 'Anggota berhasil dihapus.');
                } catch (error) {
                    showMemberToast('error', error.message || 'Gagal menghapus anggota.');
                } finally {
                    deleteConfirmBtn.disabled = false;
                    deleteConfirmBtn.textContent = originalText;
                }
            });
        });
    </script>
@endpush
