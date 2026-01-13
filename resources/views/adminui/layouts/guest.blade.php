<!--
=========================================================
* Soft UI Dashboard - v1.0.3
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard
* Copyright 2021 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
  @php
    $logoAdmin = \App\Models\LogoAdmin::where('status', true)->first();
    $faviconUrl = $logoAdmin && $logoAdmin->gambar ? asset('storage/' . $logoAdmin->gambar) : null;
    $faviconTimestamp = $logoAdmin && $logoAdmin->updated_at ? $logoAdmin->updated_at->timestamp : time();
    $systemTagline = $logoAdmin && $logoAdmin->tagline && trim($logoAdmin->tagline) !== '' ? $logoAdmin->tagline : null;
  @endphp
  @if($faviconUrl)
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}?v={{ $faviconTimestamp }}">
  @else
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
  @endif
  <title>
    @if($systemTagline){{ $systemTagline }} - @endif Admin Panel
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet" />
  <style>
    .input-group-outline {
      position: relative;
      margin-bottom: 1rem;
      margin-top: 1rem;
    }
    .input-group-outline .form-label {
      position: absolute;
      top: 0.5rem;
      left: 0.75rem;
      font-size: 0.875rem;
      color: #7b809a;
      font-weight: 400;
      line-height: 1.4;
      pointer-events: none;
      transition: all 0.2s ease-in-out;
      z-index: 1;
      margin: 0;
      background: #fff;
      padding: 0 0.25rem;
    }
    .input-group-outline .form-control {
      border: 1px solid #d2d6da;
      border-radius: 0.375rem;
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
      font-weight: 400;
      line-height: 1.4rem;
      color: #495057;
      background-color: #fff;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .input-group-outline .form-control:focus {
      border-color: #e91e63;
      outline: 0;
      box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.25);
    }
    .input-group-outline.is-filled .form-label,
    .input-group-outline .form-control:focus ~ .form-label {
      top: -0.5rem;
      font-size: 0.75rem;
      color: #e91e63;
    }
  </style>
</head>

<body class="bg-gray-200">
  @yield('content')
  
  <!--   Core JS Files   -->
  <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle floating label
      document.querySelectorAll('.input-group-outline .form-control').forEach(function(input) {
        // Check on load if input has value
        if (input.value !== '') {
          input.parentElement.classList.add('is-filled');
        }
        
        // On focus
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('is-filled');
        });
        
        // On blur
        input.addEventListener('blur', function() {
          if (this.value === '') {
            this.parentElement.classList.remove('is-filled');
          }
        });
        
        // On input change
        input.addEventListener('input', function() {
          if (this.value !== '') {
            this.parentElement.classList.add('is-filled');
          } else {
            this.parentElement.classList.remove('is-filled');
          }
        });
      });
    });
  </script>
</body>

</html>