<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
    @php
        $logoAdmin = \App\Models\LogoAdmin::where('status', true)->first();
        $logoUrl = $logoAdmin && $logoAdmin->gambar ? asset('storage/' . $logoAdmin->gambar) : null;
        $logoName = $logoAdmin && $logoAdmin->nama_perusahaan && trim($logoAdmin->nama_perusahaan) !== '' ? $logoAdmin->nama_perusahaan : null;
    @endphp
    <a href="{{ url('/') }}" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        @if($logoUrl)
            {{-- Jika ada logo, tampilkan gambar dan nama LSP --}}
            <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 45px; object-fit: contain;">
            @if($logoName)
                <h2 class="m-0 text-primary ms-2" style="font-size: 1.5rem;">{{ $logoName }}</h2>
            @endif
        @else
            {{-- Jika tidak ada logo, tampilkan icon dan nama LSP --}}
            <i class="fa fa-certificate me-3 text-primary" style="font-size: 2rem;"></i>
            @if($logoName)
                <h2 class="m-0 text-primary">{{ $logoName }}</h2>
            @endif
        @endif
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            @php
                $currentSlug = request()->segment(1) ?: 'home';
                $menuItems = [
                    ['slug' => 'home', 'label' => 'Beranda', 'url' => '/'],
                    ['slug' => 'tentang', 'label' => 'Tentang LSP', 'url' => '/tentang'],
                    ['slug' => 'skema', 'label' => 'Skema', 'url' => '/skema'],
                    ['slug' => 'alur', 'label' => 'Alur Sertifikasi', 'url' => '/alur'],
                    ['slug' => 'persyaratan', 'label' => 'Persyaratan', 'url' => '/persyaratan'],
                    ['slug' => 'kontak', 'label' => 'Kontak', 'url' => '/kontak'],
                    ['slug' => 'status-pra-pendaftaran', 'label' => 'Cek Status', 'url' => '/status-pra-pendaftaran'],
                ];
            @endphp
            
            @foreach($menuItems as $item)
                <a href="{{ url($item['url']) }}" 
                   class="nav-item nav-link {{ ($currentSlug === $item['slug'] || (request()->is('/') && $item['slug'] === 'home')) ? 'active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('daftar') }}" class="btn btn-primary py-4 px-lg-5 d-none d-lg-block">Daftar Sekarang<i class="fa fa-arrow-right ms-3"></i></a>
    </div>
</nav>
<!-- Navbar End -->
