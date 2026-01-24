<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('adminui.dashboard') }}">
        @php
            $logoUrl = systemLogoUrl();
            $companyName = systemCompanyName();
        @endphp
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="navbar-brand-img h-100" alt="Logo" style="max-height: 40px; object-fit: contain;">
        @else
            <div class="icon icon-shape icon-sm bg-gradient-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-certificate"></i>
            </div>
        @endif
        <div class="ms-3">
            @if($companyName)
                <div class="font-weight-bold" style="line-height: 1.2;">{{ $companyName }}</div>
            @endif
            <div class="text-xs opacity-8" style="line-height: 1.2;">Admin Panel</div>
        </div>
    </a>
  </div>
  <hr class="horizontal dark mt-0">
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      
      {{-- DASHBOARD --}}
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/dashboard') ? 'active' : '') }}" href="{{ route('adminui.dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>Dashboard</title>
              <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                  <g transform="translate(1716.000000, 291.000000)">
                    <g transform="translate(0.000000, 148.000000)">
                      <path class="color-background opacity-6" d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z"></path>
                      <path class="color-background" d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z"></path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      {{-- MANAJEMEN SECTION - Only show if user has any management permission --}}
      @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('cms.view'))
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Manajemen</h6>
      </li>
      @endif

      {{-- USERS - Only for users with users.view permission --}}
      @if(auth()->user()->hasPermission('users.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/users*') ? 'active' : '') }}" href="{{ route('adminui.users.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path class="color-background" d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z" fill="#FFFFFF"/>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Users</span>
        </a>
      </li>
      @endif

      {{-- CMS LANDING PAGE - Only for users with cms.view permission --}}
      @if(auth()->user()->hasPermission('cms.view'))
      <li class="nav-item">
        <a data-bs-toggle="collapse" href="#cmsLandingSubmenu" class="nav-link {{ (Request::is('adminui/halaman*') || Request::is('adminui/bagian-halaman*') ? '' : 'collapsed') }}" aria-controls="cmsLandingSubmenu" role="button" aria-expanded="{{ (Request::is('adminui/halaman*') || Request::is('adminui/bagian-halaman*') ? 'true' : 'false') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-file-alt text-success text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">CMS Landing Page</span>
        </a>
        <div class="collapse {{ (Request::is('adminui/halaman*') || Request::is('adminui/bagian-halaman*') ? 'show' : '') }}" id="cmsLandingSubmenu">
          <ul class="nav ms-4 ps-3">
            <li class="nav-item">
              <a class="nav-link {{ (Request::is('adminui/halaman*') && !Request::is('adminui/bagian-halaman*') ? 'active' : '') }}" href="{{ route('adminui.halaman.index') }}">
                <span class="sidenav-mini-icon"> H </span>
                <span class="sidenav-normal"> Halaman </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ (Request::is('adminui/bagian-halaman*') ? 'active' : '') }}" href="{{ route('adminui.bagian-halaman.index') }}">
                <span class="sidenav-mini-icon"> B </span>
                <span class="sidenav-normal"> Bagian Halaman </span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      @endif

      {{-- MASTER DATA SECTION --}}
      @if(auth()->user()->hasPermission('pra_pendaftaran.view') || auth()->user()->hasPermission('pendaftaran_sertifikasi.view') || auth()->user()->hasPermission('skema_sertifikasi.view') || auth()->user()->hasPermission('unit_kompetensi.view') || auth()->user()->hasPermission('kuk.view') || auth()->user()->hasPermission('asesmen.view') || auth()->user()->hasPermission('keputusan.view') || auth()->user()->hasPermission('sertifikat.view'))
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Master Data</h6>
      </li>
      @endif

      {{-- PRA-PENDAFTARAN - Only for users with pra_pendaftaran.view permission --}}
      @if(auth()->user()->hasPermission('pra_pendaftaran.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/pra-pendaftaran*') ? 'active' : '') }}" href="{{ route('adminui.pra-pendaftaran.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-clipboard-list text-warning text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Pra-Pendaftaran</span>
        </a>
      </li>
      @endif

      {{-- PENDAFTARAN SERTIFIKASI - For users with pendaftaran_sertifikasi.view --}}
      @if(auth()->user()->hasPermission('pendaftaran_sertifikasi.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/pendaftaran-sertifikasi*') ? 'active' : '') }}" href="{{ route('adminui.pendaftaran-sertifikasi.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user-graduate text-danger text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Pendaftaran Sertifikasi</span>
        </a>
      </li>
      @endif

      {{-- SKEMA SERTIFIKASI - For users with skema_sertifikasi.view --}}
      @if(auth()->user()->hasPermission('skema_sertifikasi.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/skema-sertifikasi*') ? 'active' : '') }}" href="{{ route('adminui.skema-sertifikasi.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-certificate text-info text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Skema Sertifikasi</span>
        </a>
      </li>
      @endif

      {{-- UNIT KOMPETENSI - For users with unit_kompetensi.view --}}
      @if(auth()->user()->hasPermission('unit_kompetensi.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/unit-kompetensi*') ? 'active' : '') }}" href="{{ route('adminui.unit-kompetensi.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-tasks text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Unit Kompetensi</span>
        </a>
      </li>
      @endif

      {{-- KUK (KRITERIA UNJUK KERJA) - For users with kuk.view --}}
      @if(auth()->user()->hasPermission('kuk.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/kuk*') ? 'active' : '') }}" href="{{ route('adminui.kuk.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-list-check text-success text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">KUK</span>
        </a>
      </li>
      @endif

      {{-- ASESMEN - For users with asesmen.view permission --}}
      @if(auth()->user()->hasPermission('asesmen.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/asesmen*') ? 'active' : '') }}" href="{{ route('adminui.asesmen.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-clipboard-check text-warning text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Asesmen</span>
        </a>
      </li>
      @endif

      {{-- KEPUTUSAN SERTIFIKASI - For users with keputusan_sertifikasi.view permission --}}
      @if(auth()->user()->hasPermission('keputusan_sertifikasi.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/keputusan*') ? 'active' : '') }}" href="{{ route('adminui.keputusan.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-gavel text-danger text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Keputusan Sertifikasi</span>
        </a>
      </li>
      @endif

      {{-- SERTIFIKAT - For users with sertifikat.view permission --}}
      @if(auth()->user()->hasPermission('sertifikat.view'))
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/sertifikat*') ? 'active' : '') }}" href="{{ route('adminui.sertifikat.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-award text-success text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Sertifikat</span>
        </a>
      </li>
      @endif

      {{-- AUDIT LOG - For users with audit_log.view permission --}}
      @if(auth()->user()->hasPermission('audit_log.view'))
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Audit & Kepatuhan</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/audit-log*') ? 'active' : '') }}" href="{{ route('adminui.audit-log.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-history text-info text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Audit Log</span>
        </a>
      </li>
      @endif

      {{-- SYSTEM SETTINGS - For users with settings.view permission --}}
      @if(auth()->user()->hasPermission('settings.view'))
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Pengaturan Sistem</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/settings/branding*') ? 'active' : '') }}" href="{{ route('adminui.settings.branding') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-palette text-warning text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Branding & Logo</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/settings/email*') ? 'active' : '') }}" href="{{ route('adminui.settings.email') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-envelope text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Pengaturan Email</span>
        </a>
      </li>
      @endif

      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Akun</h6>
      </li>

      {{-- PROFILE INFO --}}
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('adminui/profile*') ? 'active' : '') }}" href="{{ route('adminui.profile') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Profil Saya</span>
        </a>
      </li>

      {{-- LOGOUT --}}
      <li class="nav-item">
        <form method="POST" action="{{ route('adminui.logout') }}" id="logout-form">
          @csrf
          <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin keluar?')) document.getElementById('logout-form').submit();">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-sign-out-alt text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Keluar</span>
          </a>
        </form>
      </li>

      {{-- USER INFO CARD - Professional Layout --}}
      <li class="nav-item mt-4 mx-3">
        <div class="sidebar-user-card" id="sidenavCard">
          <div class="user-card-content">
            {{-- Avatar with initial --}}
            <div class="user-avatar">
              {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            {{-- User Info --}}
            <div class="user-details">
              <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
              @php
                // Get primary role from new RBAC system
                $user = auth()->user();
                $primaryRole = $user->roles()->first();
                
                if ($primaryRole) {
                    $roleSlug = $primaryRole->name;
                    $roleLabel = $primaryRole->display_name;
                } else {
                    // Fallback to legacy role field
                    $roleSlug = strtolower($user->role ?? 'guest');
                    $roleLabel = match($roleSlug) {
                        'super admin', 'super_admin', 'superadmin' => 'SUPER ADMIN',
                        'admin' => 'ADMIN',
                        'staff', 'asesor' => 'ASESOR',
                        'komite_teknis', 'komite teknis' => 'KOMITE TEKNIS',
                        default => strtoupper($roleSlug)
                    };
                }
                
                $roleClass = match($roleSlug) {
                    'super_admin', 'super admin', 'superadmin' => 'role-super-admin',
                    'admin' => 'role-admin',
                    'asesor', 'staff' => 'role-staff',
                    'komite_teknis', 'komite teknis' => 'role-komite',
                    default => 'role-default'
                };
              @endphp
              <span class="role-badge {{ $roleClass }}">
                {{ strtoupper($roleLabel) }}
              </span>
            </div>
          </div>
          {{-- Quick Actions --}}
          <div class="user-actions">
            <a href="{{ route('adminui.profile') }}" class="btn-user-action" title="Pengaturan Profil">
              <i class="fas fa-cog"></i>
            </a>
          </div>
        </div>
      </li>

    </ul>
  </div>
  
  {{-- Sidebar User Card Styles - Scoped inside aside --}}
  <style>
    /* User Card Container */
    .sidebar-user-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 14px;
      border-radius: 10px;
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .user-card-content {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      min-width: 0;
    }

    /* Avatar Circle */
    .user-avatar {
      width: 40px;
      height: 40px;
      min-width: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 15px;
      color: #fff;
      text-transform: uppercase;
      box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
      flex-shrink: 0;
    }

    /* User Details */
    .user-details {
      display: flex;
      flex-direction: column;
      gap: 3px;
      min-width: 0;
      overflow: hidden;
    }

    .user-name {
      font-weight: 600;
      font-size: 12px;
      color: #fff;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Role Badge */
    .role-badge {
      font-size: 9px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 999px;
      display: inline-block;
      letter-spacing: 0.3px;
      width: fit-content;
      text-transform: uppercase;
    }

    /* Role Colors */
    .role-super-admin {
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
      color: #fff;
    }

    .role-admin {
      background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
      color: #fff;
    }

    .role-staff {
      background: linear-gradient(135deg, #059669 0%, #10b981 100%);
      color: #fff;
    }

    .role-komite {
      background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
      color: #fff;
    }

    .role-default {
      background: linear-gradient(135deg, #475569 0%, #64748b 100%);
      color: #fff;
    }

    /* Action Button */
    .user-actions {
      flex-shrink: 0;
    }

    .btn-user-action {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255, 255, 255, 0.7);
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .btn-user-action:hover {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    .btn-user-action i {
      font-size: 13px;
    }
  </style>
</aside>
