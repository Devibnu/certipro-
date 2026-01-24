@extends('adminui.layouts.auth')
@section('title', 'Tambah User')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0 mb-4 rounded-3">
                <div class="card-header bg-transparent pb-0 px-5 pt-4">
                    <h5 class="mb-0 font-weight-bold">Tambah User Baru</h5>
                </div>
                <div class="card-body px-5 py-5">
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
                    <form action="{{ route('adminui.users.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-lg-6 col-md-12">
                                <div class="card h-100 shadow-sm border-0 rounded-3">
                                    <div class="card-body px-5 py-5">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                            <small class="text-muted">Minimal 8 karakter</small>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                                <option value="">-- Pilih Role --</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Hak akses ditentukan otomatis berdasarkan role.</small>
                                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card h-100 shadow-sm border-0 rounded-3 bg-light">
                                    <div class="card-header bg-gradient-light pb-2 px-4">
                                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-info-circle me-2"></i>Informasi Role</h6>
                                    </div>
                                    <div class="card-body pt-3 pb-3 px-4">
                                        <div class="mb-3">
                                            <strong>Super Admin</strong>
                                            <p class="text-muted mb-2 small">Akses penuh ke seluruh sistem tanpa batasan.</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Admin</strong>
                                            <p class="text-muted mb-2 small">Mengelola operasional sistem, pengguna, dan data.</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Asesor</strong>
                                            <p class="text-muted mb-2 small">Melakukan asesmen dan penilaian kompetensi.</p>
                                        </div>
                                        <div class="mb-0">
                                            <strong>Komite Teknis</strong>
                                            <p class="text-muted mb-0 small">Review hasil asesmen dan keputusan sertifikasi.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn bg-gradient-primary px-4 py-2">
                                <i class="fas fa-save me-2"></i>Simpan User
                            </button>
                            <a href="{{ route('adminui.users.index') }}" class="btn btn-link ms-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
