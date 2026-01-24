@extends('adminui.layouts.auth')
@section('title', 'Edit User')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <span class="alert-text">
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- User Information Card --}}
            <div class="card shadow-lg border-0 mb-4 rounded-3">
                <div class="card-header bg-transparent pb-0 px-5 pt-4">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                </div>
                <div class="card-body px-5 py-4">
                    <form action="{{ route('adminui.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-lg-6 col-md-12">
                                <div class="card h-100 shadow-sm border-0 rounded-3">
                                    <div class="card-body px-5 py-5">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                                <option value="">-- Pilih Role --</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Hak akses ditentukan otomatis berdasarkan role.</small>
                                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card h-100 shadow-sm border-0 rounded-3 bg-light">
                                    <div class="card-header bg-gradient-light pb-2 px-4">
                                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-info-circle me-2"></i>Informasi User</h6>
                                    </div>
                                    <div class="card-body pt-3 pb-3 px-4">
                                        <div class="mb-3">
                                            <strong>ID:</strong> {{ $user->id }}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Role Saat Ini:</strong> 
                                            <span class="badge bg-primary">{{ $user->userRole?->name ? ucwords(str_replace('_', ' ', $user->userRole->name)) : 'Tidak ada' }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Terdaftar:</strong> {{ $user->created_at->format('d M Y H:i') }}
                                        </div>
                                        <div class="mb-0">
                                            <strong>Terakhir Update:</strong> {{ $user->updated_at->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <a href="{{ route('adminui.users.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Keamanan Akun Section - Only for Super Admin / Admin --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && auth()->id() !== $user->id)
            <div class="card shadow-lg border-0 mb-4 rounded-3">
                <div class="card-header bg-transparent pb-0 px-5 pt-4">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-shield-alt me-2 text-warning"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body px-5 py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">Reset Password</h6>
                            <p class="text-muted mb-0 text-sm">
                                Kirim link reset password ke email user. Link berlaku 60 menit dan hanya dapat digunakan sekali.
                                Password lama tetap aktif hingga user mereset password-nya.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                <i class="fas fa-key me-1"></i> Reset Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reset Password Modal --}}
@if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && auth()->id() !== $user->id)
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="resetPasswordModalLabel">
                    <i class="fas fa-key text-warning me-2"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong>
                </div>
                <p class="mb-3">
                    Anda akan mengirimkan link reset password ke:
                </p>
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-gradient-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-white font-weight-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Link reset berlaku 60 menit</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Hanya dapat digunakan sekali</li>
                    <li><i class="fas fa-check text-success me-2"></i>Password lama tetap aktif sampai user mereset</li>
                </ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('adminui.users.reset-password', $user->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i>Kirim Link Reset
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
