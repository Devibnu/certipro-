@extends('adminui.layouts.auth')
@section('title', 'Users')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-white text-capitalize ps-3">Kelola User</h6>
                            </div>
                            <div class="col-auto pe-3">
                                <a href="{{ route('adminui.users.create') }}" class="btn bg-gradient-dark mb-0">
                                    <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Tambah User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 text-white font-weight-bold">
                            <i class="fas fa-check me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 text-white font-weight-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Created</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1 align-items-center">
                                            <div>
                                                @if($user->photo)
                                                    {{-- user->photo already stores path like 'uploads/profile/filename' so use it directly --}}
                                                    <img src="{{ asset($user->photo) }}" class="avatar avatar-lg me-3 border-radius-lg" alt="avatar">
                                                @else
                                                    <div class="avatar avatar-lg me-3 border-radius-lg bg-gradient-info d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-info text-white">{{ $user->role }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-gradient-success">AKTIF</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $user->created_at->format('d M Y') }}</span><br>
                                        <span class="text-xs text-muted">{{ $user->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('adminui.users.edit', $user->id) }}" class="btn btn-sm btn-dark me-2"><i class="fas fa-edit"></i> EDIT</a>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete-user" 
                                                data-user-id="{{ $user->id }}" 
                                                data-user-name="{{ $user->name }}">
                                            <i class="fas fa-trash"></i> HAPUS
                                        </button>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('adminui.users.destroy', $user->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada user.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger">
                <h5 class="modal-title text-white" id="deleteUserModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-user-times text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-2">Yakin ingin menghapus user ini?</h5>
                <p class="text-muted mb-0">User <strong id="delete-user-name"></strong> akan dihapus secara permanen.</p>
                <p class="text-danger small mt-2"><i class="fas fa-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn bg-gradient-danger px-4" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    const deleteUserName = document.getElementById('delete-user-name');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let currentUserId = null;

    // Handle delete button clicks
    document.querySelectorAll('.btn-delete-user').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentUserId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            deleteUserName.textContent = userName;
            deleteModal.show();
        });
    });

    // Handle confirm delete
    confirmDeleteBtn.addEventListener('click', function() {
        if (currentUserId) {
            document.getElementById('delete-form-' + currentUserId).submit();
        }
    });
});
</script>
@endpush
