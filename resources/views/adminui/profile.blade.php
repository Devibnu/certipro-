@extends('adminui.layouts.auth')

@section('title', 'Profile')

@section('content')

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-8 mb-4">
            {{-- Avatar Upload Card --}}
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-transparent pb-0">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-camera me-2"></i>Foto Profile</h5>
                </div>
                <div class="card-body px-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                            <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                            <span class="alert-text">{{ session('success') }}</span>
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
                    
                    <div class="row align-items-center">
                        <div class="col-auto">
                            {{-- Current Avatar Preview --}}
                            <div class="position-relative" style="width: 100px; height: 100px;">
                                @if($user->avatar)
                                    <img id="avatarPreview" src="{{ asset('storage/' . $user->avatar) }}" 
                                         class="rounded-circle border border-3 border-white shadow" 
                                         style="width: 100px; height: 100px; object-fit: cover;" 
                                         alt="{{ $user->name }}">
                                @else
                                    <div id="avatarInitial" class="avatar avatar-xl bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 100px; height: 100px;">
                                        <span class="text-white font-weight-bold" style="font-size: 2rem;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <img id="avatarPreview" src="" 
                                         class="rounded-circle border border-3 border-white shadow d-none" 
                                         style="width: 100px; height: 100px; object-fit: cover;" 
                                         alt="{{ $user->name }}">
                                @endif
                            </div>
                        </div>
                        <div class="col">
                            <form action="{{ route('adminui.profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                @csrf
                                @method('PUT')
                                {{-- Hidden fields to preserve other data --}}
                                <input type="hidden" name="name" value="{{ $user->name }}">
                                <input type="hidden" name="email" value="{{ $user->email }}">
                                <input type="hidden" name="phone" value="{{ $user->phone }}">
                                <input type="hidden" name="location" value="{{ $user->location }}">
                                <input type="hidden" name="about_me" value="{{ $user->about_me }}">
                                
                                <div class="mb-2">
                                    <label for="avatar" class="form-label mb-1">Upload Foto Baru</label>
                                    <input class="form-control form-control-sm @error('avatar') is-invalid @enderror" 
                                           type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <small class="text-muted">Format: JPEG, PNG, GIF, WebP. Maksimal 2MB</small>
                                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm bg-gradient-primary">
                                        <i class="fas fa-upload me-1"></i>Upload
                                    </button>
                                    @if($user->avatar)
                                        <button type="button" class="btn btn-sm bg-gradient-danger" data-bs-toggle="modal" data-bs-target="#deleteAvatarModal">
                                            <i class="fas fa-trash me-1"></i>Hapus Foto
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Profile Card --}}
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-transparent pb-0">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body px-4">
                    <form action="{{ route('adminui.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input class="form-control @error('phone') is-invalid @enderror" type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input class="form-control @error('location') is-invalid @enderror" type="text" id="location" name="location" value="{{ old('location', $user->location) }}">
                                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="about_me" class="form-label">About Me</label>
                                <textarea class="form-control @error('about_me') is-invalid @enderror" id="about_me" name="about_me" rows="3">{{ old('about_me', $user->about_me) }}</textarea>
                                @error('about_me')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn bg-gradient-primary px-4 py-2">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card shadow border-0">
                <div class="card-header bg-transparent pb-0">
                    <h5 class="mb-0 font-weight-bold">Change Password</h5>
                </div>
                <div class="card-body px-4">
                    <form action="{{ route('adminui.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input class="form-control @error('current_password') is-invalid @enderror" type="password" id="current_password" name="current_password" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="password" class="form-label">New Password</label>
                                <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" required>
                                <small class="text-muted">Minimal 8 karakter</small>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn bg-gradient-dark px-4 py-2">
                                <i class="fas fa-key me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card card-profile shadow border-0 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 class="rounded-circle img-fluid border border-3 border-white shadow" 
                                 style="width: 120px; height: 120px; object-fit: cover;" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="avatar avatar-xl position-relative bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; margin: 0 auto;">
                                <span class="text-white font-weight-bold" style="font-size: 2.5rem;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-sm text-muted mb-2">
                        <span class="badge bg-gradient-info">{{ $user->userRole?->display_name ?? 'User' }}</span>
                    </p>
                    <div class="mb-2 text-secondary small">
                        <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                    </div>
                    @if($user->phone)
                        <div class="mb-2 text-secondary small">
                            <i class="fas fa-phone me-2"></i>{{ $user->phone }}
                        </div>
                    @endif
                    @if($user->location)
                        <div class="mb-2 text-secondary small">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ $user->location }}
                        </div>
                    @endif
                    @if($user->about_me)
                        <div class="mt-3">
                            <p class="text-sm text-muted">{{ $user->about_me }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Avatar Modal --}}
<div class="modal fade" id="deleteAvatarModal" tabindex="-1" aria-labelledby="deleteAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAvatarModalLabel">Hapus Foto Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus foto profile? Foto akan diganti dengan inisial nama.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('adminui.profile.avatar.remove') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn bg-gradient-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Avatar Preview Script --}}
<script>
document.getElementById('avatar').addEventListener('change', function(event) {
    const input = event.target;
    const preview = document.getElementById('avatarPreview');
    const initial = document.getElementById('avatarInitial');
    
    if (input.files && input.files[0]) {
        // Validate file size (2MB max)
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (initial) {
                initial.classList.add('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
});
</script>
@endsection