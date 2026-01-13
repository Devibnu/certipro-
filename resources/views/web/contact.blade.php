<!DOCTYPE html>
<html lang="en">
  <head>
    @php $systemName = systemCompanyName(); @endphp
    <title>{{ $kontak->judul_halaman ?? 'Contact Us' }}@if($systemName) - {{ $systemName }}@endif</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('website/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/animate.css') }}">
    
    <link rel="stylesheet" href="{{ asset('website/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/magnific-popup.css') }}">

    <link rel="stylesheet" href="{{ asset('website/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('website/css/ionicons.min.css') }}">
    
    <link rel="stylesheet" href="{{ asset('website/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/style.css') }}">
  </head>
  <body>
	  <div class="bg-top navbar-light">
    	<div class="container">
    		<div class="row no-gutters d-flex align-items-center align-items-stretch">
    			<div class="col-md-4 d-flex align-items-center py-4">
    				@php $systemName = systemCompanyName(); $logoUrl = systemLogoUrl(); @endphp
    				@if($logoUrl)
    				    <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ $logoUrl }}" alt="Logo" style="max-height: 40px;"></a>
    				@elseif($systemName)
    				    <a class="navbar-brand" href="{{ route('home') }}">{{ $systemName }}</a>
    				@else
    				    <a class="navbar-brand" href="{{ route('home') }}">LSP</a>
    				@endif
    			</div>
	    		<div class="col-lg-8 d-block">
		    		<div class="row d-flex">
					    <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
					    	<div class="icon d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
					    	<div class="text">
					    		<span>Email</span>
						    	<span>{{ $kontak->email ?? 'info@jasaibnu.id' }}</span>
						    </div>
					    </div>
					    <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
					    	<div class="icon d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
						    <div class="text">
						    	<span>Call</span>
						    	<span>Call Us: {{ $kontak->telepon ?? '+1 235 2355 98' }}</span>
						    </div>
					    </div>
					    <div class="col-md topper d-flex align-items-center justify-content-end">
					    	<p class="mb-0">
					    		<a href="#" class="btn py-2 px-3 btn-primary d-flex align-items-center justify-content-center">
					    			<span>Free Consulting</span>
					    		</a>
					    	</p>
					    </div>
				    </div>
			    </div>
		    </div>
		  </div>
    </div>
	  <nav class="navbar navbar-expand-lg navbar-dark bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container d-flex align-items-center px-4">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>
	      <form action="#" class="searchform order-lg-last">
          <div class="form-group d-flex">
            <input type="text" class="form-control pl-3" placeholder="Search">
            <button type="submit" placeholder="" class="form-control search"><span class="ion-ios-search"></span></button>
          </div>
        </form>
	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav mr-auto">
	        	<li class="nav-item"><a href="{{ route('home') }}" class="nav-link pl-0">Home</a></li>
	        	<li class="nav-item"><a href="{{ route('about') }}" class="nav-link">About</a></li>
	        	<li class="nav-item"><a href="#" class="nav-link">Projects</a></li>
	        	<li class="nav-item"><a href="{{ route('services') }}" class="nav-link">Services</a></li>
	        	<li class="nav-item"><a href="#" class="nav-link">Blog</a></li>
	          <li class="nav-item active"><a href="{{ route('contact') }}" class="nav-link">Contact</a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->
    
    <section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('website/images/bg_1.jpg') }}');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
            <h1 class="mb-2 bread">{{ $kontak->judul_halaman ?? 'Contact Us' }}</h1>
            <p class="breadcrumbs">
              <span class="mr-2"><a href="{{ route('home') }}">Home <i class="ion-ios-arrow-forward"></i></a></span> 
              <span>Contact <i class="ion-ios-arrow-forward"></i></span>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section contact-section">
      <div class="container">
        <div class="row d-flex mb-5 contact-info justify-content-center">
        	<div class="col-md-8">
        		<div class="row mb-5">
		          <div class="col-md-4 text-center py-4">
		          	<div class="icon">
		          		<span class="icon-map-o"></span>
		          	</div>
		            <p><span>Address:</span> {{ $kontak->alamat ?? '198 West 21th Street, Suite 721 New York NY 10016' }}</p>
		          </div>
		          <div class="col-md-4 text-center border-height py-4">
		          	<div class="icon">
		          		<span class="icon-mobile-phone"></span>
		          	</div>
		            <p><span>Phone:</span> <a href="tel:{{ str_replace(' ', '', $kontak->telepon ?? '+1235235598') }}">{{ $kontak->telepon ?? '+ 1235 2355 98' }}</a></p>
		          </div>
		          <div class="col-md-4 text-center py-4">
		          	<div class="icon">
		          		<span class="icon-envelope-o"></span>
		          	</div>
		            <p><span>Email:</span> <a href="mailto:{{ $kontak->email ?? 'info@yoursite.com' }}">{{ $kontak->email ?? 'info@yoursite.com' }}</a></p>
		          </div>
		        </div>
          </div>
        </div>
        <div class="row block-9 justify-content-center mb-5">
          <div class="col-md-8 mb-md-5">
          	@if($kontak && $kontak->deskripsi_pesan)
          		<h2 class="text-center">{{ $kontak->deskripsi_pesan }}</h2>
          	@else
          		<h2 class="text-center">If you got any questions <br>please do not hesitate to send us a message</h2>
          	@endif
            <form id="contactForm" action="{{ route('contact.submit') }}" method="POST" class="bg-light p-5 contact-form">
              @csrf
              <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
              </div>
              <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
              </div>
              <div class="form-group">
                <input type="text" name="subject" class="form-control" placeholder="Subject" required>
              </div>
              <div class="form-group">
                <textarea name="message" id="" cols="30" rows="7" class="form-control" placeholder="Message" required></textarea>
              </div>
              <div class="form-group">
                <input type="submit" value="Send Message" class="btn btn-primary py-3 px-5">
              </div>
            </form>
          
          </div>
        </div>
      </div>
    </section>

    @if($kontak && $kontak->map_embed)
    <section class="ftco-section ftco-no-pb ftco-no-pt">
    	<div class="container-fluid px-0">
    		<div class="row justify-content-center">
        	<div class="col-md-12">
        		<div class="bg-white" style="width: 100%; height: 400px;">
        			{!! $kontak->map_embed !!}
        		</div>
        	</div>
        </div>
    	</div>
    </section>
    @endif
		
    @include('web.partials.footer')
    
  

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke="#eeeeee" stroke-width="4"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke="#F96D00" stroke-width="4" stroke-miterlimit="10"/></svg></div>


  <script src="{{ asset('website/js/jquery.min.js') }}"></script>
  <script src="{{ asset('website/js/jquery-migrate-3.0.1.min.js') }}"></script>
  <script src="{{ asset('website/js/popper.min.js') }}"></script>
  <script src="{{ asset('website/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('website/js/jquery.easing.1.3.js') }}"></script>
  <script src="{{ asset('website/js/jquery.waypoints.min.js') }}"></script>
  <script src="{{ asset('website/js/jquery.stellar.min.js') }}"></script>
  <script src="{{ asset('website/js/owl.carousel.min.js') }}"></script>
  <script src="{{ asset('website/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('website/js/aos.js') }}"></script>
  <script src="{{ asset('website/js/jquery.animateNumber.min.js') }}"></script>
  <script src="{{ asset('website/js/scrollax.min.js') }}"></script>
  <script src="{{ asset('website/js/main.js') }}"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    // Show session flash messages
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745'
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
      });
    @endif

    // Contact form submission
    $(document).ready(function() {
      $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        var submitBtn = $(this).find('input[type="submit"]');
        var originalValue = submitBtn.val();
        submitBtn.val('Mengirim...').prop('disabled', true);
        
        $.ajax({
          url: $(this).attr('action'),
          method: 'POST',
          data: $(this).serialize(),
          success: function(response) {
            if(response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
              });
              $('#contactForm')[0].reset();
            }
          },
          error: function(xhr) {
            var errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
            if(xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: errorMessage,
              confirmButtonText: 'OK',
              confirmButtonColor: '#dc3545'
            });
          },
          complete: function() {
            submitBtn.val(originalValue).prop('disabled', false);
          }
        });
      });
    });
  </script>
    
  </body>
</html>