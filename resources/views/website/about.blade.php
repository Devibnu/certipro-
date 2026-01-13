<!DOCTYPE html>
<html lang="en">
  <head>
    @php $systemName = systemCompanyName(); $logoUrl = systemLogoUrl(); @endphp
    <title>About Us{{ $systemName ? ' - ' . $systemName : '' }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">
    
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
	  <div class="bg-top navbar-light">
    	<div class="container">
    		<div class="row no-gutters d-flex align-items-center align-items-stretch">
    			<div class="col-md-4 d-flex align-items-center py-4">
    				@if($logoUrl)
    				    <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ $logoUrl }}" alt="Logo" style="max-height: 40px;"></a>
    				@elseif($systemName)
    				    <a class="navbar-brand" href="{{ url('/') }}">{{ $systemName }}</a>
    				@else
    				    <a class="navbar-brand" href="{{ url('/') }}">LSP</a>
    				@endif
    			</div>
	    		<div class="col-lg-8 d-block">
		    		<div class="row d-flex">
					    <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
					    	<div class="icon d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
					    	<div class="text">
					    		<span>Email</span>
						    	<span>info@jasaibnu.id</span>
						    </div>
					    </div>
					    <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
					    	<div class="icon d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
						    <div class="text">
						    	<span>Call</span>
						    	<span>Call Us: +62 812 3456 7890</span>
						    </div>
					    </div>
					    <div class="col-md topper d-flex align-items-center justify-content-end">
					    	<p class="mb-0">
					    		<a href="{{ url('/contact') }}" class="btn py-2 px-3 btn-primary d-flex align-items-center justify-content-center">
					    			<span>Free Consultation</span>
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
	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav mr-auto">
	        	<li class="nav-item"><a href="{{ url('/') }}" class="nav-link pl-0">Home</a></li>
	        	<li class="nav-item active"><a href="{{ url('/about') }}" class="nav-link">About</a></li>
	        	<li class="nav-item"><a href="{{ url('/services') }}" class="nav-link">Services</a></li>
	        	<li class="nav-item"><a href="{{ url('/project') }}" class="nav-link">Project</a></li>
	        	<li class="nav-item"><a href="{{ url('/blog') }}" class="nav-link">Blog</a></li>
	          <li class="nav-item"><a href="{{ url('/contact') }}" class="nav-link">Contact</a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->
    
    <!-- Header Slideshow Section -->
    @if($headers->count() > 0)
    <section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('storage/' . $headers->first()->image_path) }}');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-end justify-content-center">
          <div class="col-md-9 ftco-animate pb-5 text-center">
          	<p class="breadcrumbs text-center"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>About <i class="ion-ios-arrow-forward"></i></span></p>
            <h1 class="mb-3 bread text-center">About Us</h1>
          </div>
        </div>
      </div>
    </section>

    @if($headers->count() > 1)
    <!-- Additional Headers Carousel -->
    <section class="ftco-section">
      <div class="container">
        <div id="aboutCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($headers as $index => $header)
              <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                <img src="{{ asset('storage/' . $header->image_path) }}" class="d-block w-100" alt="Header Image" style="height: 400px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block">
                  <h5>Header {{ $index + 1 }}</h5>
                </div>
              </div>
            @endforeach
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </section>
    @endif
    @else
    <!-- Default Header when no headers available -->
    <section class="hero-wrap hero-wrap-2" style="background-image: url('images/bg_1.jpg');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-end justify-content-center">
          <div class="col-md-9 ftco-animate pb-5 text-center">
          	<p class="breadcrumbs text-center"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>About <i class="ion-ios-arrow-forward"></i></span></p>
            <h1 class="mb-3 bread text-center">About Us</h1>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- About Content Sections -->
    @if($contents->count() > 0)
      @foreach($contents as $index => $content)
        <section class="ftco-section">
          <div class="container">
            <div class="row d-flex">
              @if($index % 2 == 0)
                <!-- Content on left, image on right -->
                <div class="col-md-7 wrap-about pr-md-4 ftco-animate">
                  <h2 class="mb-4">{{ $content->title }}</h2>
                  <p class="lead">{{ $content->short_description }}</p>
                  
                  <!-- Content with Read More functionality -->
                  <div class="content-wrapper">
                    <div class="content-preview" id="content-preview-{{ $index }}">
                      {!! nl2br(e(Str::limit($content->content, 300))) !!}
                      @if(strlen($content->content) > 300)
                        <span class="text-dots">...</span>
                      @endif
                    </div>
                    
                    @if(strlen($content->content) > 300)
                      <div class="content-full" id="content-full-{{ $index }}" style="display: none;">
                        {!! nl2br(e($content->content)) !!}
                      </div>
                      
                      <div class="read-more-controls mt-3">
                        <button class="btn btn-outline-primary btn-sm read-more-btn" 
                                onclick="toggleContent({{ $index }})" 
                                id="toggle-btn-{{ $index }}">
                          <i class="icon-plus"></i> Baca Selengkapnya
                        </button>
                      </div>
                    @else
                      <div class="content-full">
                        {!! nl2br(e($content->content)) !!}
                      </div>
                    @endif
                  </div>
                  
                  <!-- Tombol CTA dihapus dari area teks -->
                </div>
                <div class="col-md-5 order-md-last wrap-about align-items-stretch">
                  <div class="wrap-about-border">
                    @if($content->image_path)
                      <div class="img" style="background-image: url('{{ asset('storage/' . $content->image_path) }}');"></div>
                    @else
                      <div class="img" style="background-image: url('images/about.jpg');"></div>
                    @endif
                    <div class="text">
                      <!-- Judul dan deskripsi dihilangkan, hanya tombol yang tetap -->
                      @if($content->cta_link && $content->cta_text)
                        <p><a href="{{ $content->cta_link }}" class="btn btn-primary py-3 px-4" target="_blank">{{ $content->cta_text }}</a></p>
                      @endif
                    </div>
                  </div>
                </div>
              @else
                <!-- Image on left, content on right -->
                <div class="col-md-5 wrap-about align-items-stretch">
                  <div class="wrap-about-border">
                    @if($content->image_path)
                      <div class="img" style="background-image: url('{{ asset('storage/' . $content->image_path) }}');"></div>
                    @else
                      <div class="img" style="background-image: url('images/about.jpg');"></div>
                    @endif
                    <div class="text">
                      <!-- Judul dan deskripsi dihilangkan, hanya tombol yang tetap -->
                      @if($content->cta_link && $content->cta_text)
                        <p><a href="{{ $content->cta_link }}" class="btn btn-primary py-3 px-4" target="_blank">{{ $content->cta_text }}</a></p>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="col-md-7 wrap-about pl-md-4 ftco-animate">
                  <h2 class="mb-4">{{ $content->title }}</h2>
                  <p class="lead">{{ $content->short_description }}</p>
                  
                  <!-- Content with Read More functionality -->
                  <div class="content-wrapper">
                    <div class="content-preview" id="content-preview-{{ $index }}">
                      {!! nl2br(e(Str::limit($content->content, 300))) !!}
                      @if(strlen($content->content) > 300)
                        <span class="text-dots">...</span>
                      @endif
                    </div>
                    
                    @if(strlen($content->content) > 300)
                      <div class="content-full" id="content-full-{{ $index }}" style="display: none;">
                        {!! nl2br(e($content->content)) !!}
                      </div>
                      
                      <div class="read-more-controls mt-3">
                        <button class="btn btn-outline-primary btn-sm read-more-btn" 
                                onclick="toggleContent({{ $index }})" 
                                id="toggle-btn-{{ $index }}">
                          <i class="icon-plus"></i> Baca Selengkapnya
                        </button>
                      </div>
                    @else
                      <div class="content-full">
                        {!! nl2br(e($content->content)) !!}
                      </div>
                    @endif
                  </div>
                  
                  <!-- Tombol CTA dihapus dari area teks -->
                </div>
              @endif
            </div>
          </div>
        </section>
      @endforeach
    @else
      <!-- Default content when no database content available -->
      <section class="ftco-section">
        <div class="container">
          <div class="row d-flex">
            <div class="col-md-5 order-md-last wrap-about align-items-stretch">
              <div class="wrap-about-border">
                <div class="img" style="background-image: url(images/about.jpg);"></div>
                <div class="text">
                  <h3>Welcome to {{ $systemName ?? 'LSP' }}</h3>
                  <p>Providing quality technology services for your business needs.</p>
                  <p><a href="{{ url('/contact') }}" class="btn btn-primary py-3 px-4">Contact us</a></p>
                </div>
              </div>
            </div>
            <div class="col-md-7 wrap-about pr-md-4 ftco-animate">
              <h2 class="mb-4">Welcome to {{ $systemName ?? 'LSP' }}</h2>
              <p>We are a technology service provider that focuses on delivering innovative solutions for your business. With years of experience in the industry, we understand your needs and are ready to help you achieve your goals.</p>
              <p>Our services include web development, mobile applications, system integration, and IT consulting. We always prioritize quality and customer satisfaction in every project we handle.</p>
              <p>Let us help you transform your business with the right technology solutions. Contact us today for a free consultation.</p>
            </div>
          </div>
        </div>
      </section>
    @endif

    <!-- Call to Action Section -->
    <section class="ftco-intro ftco-no-pb img" style="background-image: url(images/bg_3.jpg);">
    	<div class="container">
    		<div class="row justify-content-center mb-5">
          <div class="col-md-10 text-center heading-section heading-section-white ftco-animate">
            <h2 class="mb-0">Ready to Get Started?</h2>
            <p class="mb-4">Let's discuss your project and see how we can help you achieve your goals.</p>
            <p><a href="{{ url('/contact') }}" class="btn btn-primary py-3 px-4">Get Free Consultation</a></p>
          </div>
        </div>	
    	</div>
    </section>

    <!-- Footer -->
    <footer class="ftco-footer ftco-bg-dark ftco-section">
      <div class="container">
        <div class="row mb-5">
          <div class="col-md">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">{{ $systemName ?? 'LSP' }}</h2>
              <p>Your trusted technology partner for innovative business solutions.</p>
              <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
                <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="col-md">
            <div class="ftco-footer-widget mb-4 ml-md-5">
              <h2 class="ftco-heading-2">Services</h2>
              <ul class="list-unstyled">
                <li><a href="{{ url('/services') }}" class="py-2 d-block">Web Development</a></li>
                <li><a href="{{ url('/services') }}" class="py-2 d-block">Mobile Apps</a></li>
                <li><a href="{{ url('/services') }}" class="py-2 d-block">System Integration</a></li>
                <li><a href="{{ url('/services') }}" class="py-2 d-block">IT Consulting</a></li>
              </ul>
            </div>
          </div>
          <div class="col-md">
            <div class="ftco-footer-widget mb-4">
            	<h2 class="ftco-heading-2">Contact Info</h2>
            	<div class="block-23 mb-3">
	              <ul>
	                <li><span class="icon icon-map-marker"></span><span class="text">Jakarta, Indonesia</span></li>
	                <li><a href="#"><span class="icon icon-phone"></span><span class="text">+62 812 3456 7890</span></a></li>
	                <li><a href="#"><span class="icon icon-envelope"></span><span class="text">info@jasaibnu.id</span></a></li>
	              </ul>
	            </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <p>&copy; <script>document.write(new Date().getFullYear());</script> {{ $systemName ?? 'LSP' }}. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
    
  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke="#eeeeee" stroke-width="4"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke="#F96D00" stroke-width="4" stroke-miterlimit="10"/></svg></div>

  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/jquery.animateNumber.min.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>

  <!-- Custom CSS for Read More functionality -->
  <style>
    .content-wrapper {
      position: relative;
    }
    
    .content-preview, .content-full {
      line-height: 1.6;
      text-align: justify;
    }
    
    .text-dots {
      color: #999;
      font-weight: bold;
    }
    
    .read-more-controls {
      margin-top: 15px;
    }
    
    .read-more-btn {
      border: 2px solid #f39c12;
      color: #f39c12;
      background: transparent;
      padding: 8px 16px;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .read-more-btn:hover {
      background: #f39c12;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
    }
    
    .read-more-btn i {
      margin-right: 5px;
    }
    
    .content-full {
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>

  <!-- JavaScript for Read More functionality -->
  <script>
    function toggleContent(index) {
      const preview = document.getElementById('content-preview-' + index);
      const full = document.getElementById('content-full-' + index);
      const button = document.getElementById('toggle-btn-' + index);
      
      if (full.style.display === 'none') {
        // Show full content
        preview.style.display = 'none';
        full.style.display = 'block';
        button.innerHTML = '<i class="icon-minus"></i> Tutup';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-outline-secondary');
      } else {
        // Show preview content
        preview.style.display = 'block';
        full.style.display = 'none';
        button.innerHTML = '<i class="icon-plus"></i> Baca Selengkapnya';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-outline-primary');
      }
    }
  </script>
    
  </body>
</html>