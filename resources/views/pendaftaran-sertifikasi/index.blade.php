@extends('frontend.layouts.app')

@section('title', 'Pendaftaran Sertifikasi' . (systemCompanyName() ? ' - ' . systemCompanyName() : ''))

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('website/images/bg_1.jpg') }}');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Pendaftaran Sertifikasi</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('home') }}">Home <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Pendaftaran Sertifikasi</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Form Daftar Sertifikasi -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="ion-ios-add-circle mr-2"></i>Daftar Sertifikasi Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pendaftaran-sertifikasi.store') }}" method="POST">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <div class="form-group mb-0">
                                        <label for="skema_sertifikasi_id">Pilih Skema Sertifikasi</label>
                                        <select class="form-control @error('skema_sertifikasi_id') is-invalid @enderror" 
                                                id="skema_sertifikasi_id" name="skema_sertifikasi_id" required>
                                            <option value="">-- Pilih Skema --</option>
                                            @foreach($skemaList as $skema)
                                                @if(!in_array($skema->id, $registeredSkemaIds))
                                                    <option value="{{ $skema->id }}">
                                                        {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                                        ({{ $skema->jenis_label }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('skema_sertifikasi_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="ion-ios-paper-plane mr-1"></i> Ajukan Pendaftaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Pendaftaran -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="ion-ios-list mr-2"></i>Riwayat Pendaftaran Sertifikasi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No. Pendaftaran</th>
                                        <th>Skema Sertifikasi</th>
                                        <th>Tanggal Daftar</th>
                                        <th class="text-center">Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendaftaran as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->nomor_pendaftaran }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $item->skemaSertifikasi->kode_skema }}</strong><br>
                                                <small class="text-muted">{{ $item->skemaSertifikasi->nama_skema }}</small>
                                            </td>
                                            <td>{{ $item->tanggal_daftar->format('d M Y') }}</td>
                                            <td class="text-center">
                                                @switch($item->status)
                                                    @case('draft')
                                                        <span class="badge badge-secondary">Draft</span>
                                                        @break
                                                    @case('diajukan')
                                                        <span class="badge badge-warning">Diajukan</span>
                                                        @break
                                                    @case('diverifikasi')
                                                        <span class="badge badge-info">Diverifikasi</span>
                                                        @break
                                                    @case('ditolak')
                                                        <span class="badge badge-danger">Ditolak</span>
                                                        @break
                                                    @case('siap_asesmen')
                                                        <span class="badge badge-success">Siap Asesmen</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($item->catatan_admin)
                                                    <small class="text-muted">{{ Str::limit($item->catatan_admin, 50) }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-muted mb-0">Belum ada pendaftaran sertifikasi.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($pendaftaran->hasPages())
                        <div class="card-footer">
                            {{ $pendaftaran->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
