<!DOCTYPE html>
<html lang="en">

    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
          <link rel="stylesheet" type="text/css" href="{{ asset('/front/css/index.css?v=19.3') }}">




          <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/editors/quill/quill.snow.css">
          <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/editors/quill/quill.bubble.css">

        <title>@yield('title','Home')</title>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <meta content="width=device-width, initial-scale=1" name="viewport" />

        <x-application-icon/>
    </head>

    @yield('style')

        @php
            use App\Models\SiteSetting;
            $activePage = request()->route()->getName();
            $activePage = ($activePage == 'pages') ? request()->route()->parameters()['slug'] : $activePage;
            // isset(request()->route()->parameters()['type'])
            $settingData = SiteSetting::where('type',$activePage)->pluck('value','key')->toArray();
        @endphp
    <style>
        :root {
            --blue: {{ $settingData['secondary_colour'] ?? "#191970" }};
            --green:{{ $settingData['primary_colour'] ?? "#006D5B" }};
        }

        /*li.nav-item.active a {*/
        /*    color: white !important;*/
        /*    border-radius: 6px;*/
        /*    background: #006d5b;*/
        /*}*/

        li.nav-item.active a {
            color: white !important;
            border-radius: 6px;
            background: var(--green);
            padding: 0.5rem;
        }

        .btn_submit{
                background-color:var(--green);
                border: var(--green);
                color:#fff;
        }

        .form_img{
            max-width: 100%;
            height: 180px;
        }

        h5#exampleModalLabel {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 1.5rem;
        }

        .modal-header{
        border:none!important;
        }

        .footer__modal{
        border:none;
        }

        i.bi {
            font-size: 26px;
        }

        ul.social-media {
            display: flex;
            flex-direction: revert;
            justify-content:start;
            margin-top: 1rem;
        }

        ul.social-media li {
            margin-left: 0.5rem;
        }

        ul.social-media li a {
            color: #000;
        }

        .ques {
            display: inline;
            font-size: 19px!important;
        }

        .mob__number {
            font-size: 13px;
            font-family: Montserrat;
        }

        .bi-envelope {
            color: var(--green);
        }

        /*.mymodal{*/
        /* background-color:#006d5b!important;   */
        /*}    */

        /*.mtitle{*/
        /*color:#fff!important;    */
        /*}*/


        img.card-img-top.mycard {
        opacity: .7;
        }
    </style>

    <body class=" @auth login @endauth">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light main__nav">
                <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('').getPageContent('site', 'header_logo') }}" class="img-fluid logo__img"></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item {{ navActive(['about']) }}"> <a class="nav-link" href="{{ route('about') }}">About</a> </li>
                        <li class="nav-item {{ navActive(['poadcast', 'poadcast-detail']) }}"> <a class="nav-link" href="{{ route('poadcast') }}">Podcast</a> </li>
                        <li class="nav-item {{ navActive(['coaching', 'session-detail', 'session-booking']) }}"> <a class="nav-link" href="{{ route('coaching') }}">Coaching</a> </li>
                        <li class="nav-item {{ navActive(['course']) }}"> <a class="nav-link" href="{{ route('course') }}">Courses and Training</a> </li>
                        <li class="nav-item {{ navActive(['blog','blog-detail']) }}"> <a class="nav-link" href="{{ route('blog') }}">Blog</a> </li>
                        @if (getPages()->count())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                                Pages
                                </a>
                                <div class="dropdown-menu">
                                    @foreach (getPages()->get() as $page)
                                        <a class="dropdown-item" href="{{ route('pages', $page->slug) }}">{{ $page->title }}</a>
                                    @endforeach
                                </div>
                            </li>
                        @endif
                        <li class="nav-item"> <a class="nav-link" href="{{ route('login') }}">@auth Dashboard @endauth @guest Login @endguest</a> </li>
                        <li class="nav-item contact__info">
                            <a class="nav-link" href="mailto:contact@nearycoaching.com"><i class="bi bi-envelope"></i>
                                <div class="mobile_sec"> <span class="ques">Contact us</span>
                                <span class="mob__number">{{ getPageContent('site', 'email') }}</span> </div>
                            </a>
                        </li>
                        <!--<li class="nav-item contact__info">-->
                        <!--    <a class="nav-link" href="#"><i class="bi bi-envelope"></i> <img class='img-fluid phone_icon' src="{{ asset('/front') }}/images/phone.png">-->
                        <!--        <div class="mobile_sec"> <span class="ques">Contact us</span> -->
                        <!--        <span class="mob__number">602-688-2629</span> </div>-->
                        <!--    </a>-->
                        <!--</li>-->
                    </ul>
                </div>
            </nav>
        </div>

        @yield('content')

        <!--<div class="container-fluid main_banner2">-->
        <!--    <div class="container">-->
        <!--        <div class="row">-->
        <!--            <div class="col-sm-12 col-md-12 col-lg-6">-->
        <!--                <div class="banner_info banner_new">-->
        <!--                    <h2>Subscribe to Quick Thoughts</h2>-->
        <!--                    <h1>Here’s a quick thought to change your week</h1>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--            <div class="col-sm-12 col-md-12 col-lg-6">-->
        <!--                <form id="newsletter-form" action="{{ route('newsletter-create') }}" method="POST">-->
        <!--                    @csrf-->
        <!--                    <div class="form-group mb-2">-->
        <!--                        <input type="text" name="email" required class="form-control form_setup" value="" placeholder="Email here">-->
        <!--                        <span class="form_sub">Newsletter for small actionable steps.</span>-->
        <!--                    </div>-->
        <!--                    <button type="submit" class="btn btn-success set__but mb-2">Subscribe</button>-->
        <!--                </form>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->

        <div class="container-fluid main_banner2">
            <div class="container">
                <div class="row sec_news">
                    <div class="col-sm-12 col-md-12 col-lg-6">
                        <div class="banner_info banner_new">
                            <h2>Subscribe to Quick Thoughts</h2>
                            <h1>Here’s a quick thought to change your week</h1>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-6">
                        <form id="newsletter-form" action="{{ route('newsletter-create') }}" method="POST">
                             @csrf
                                <div class="form-group mb-2 form_set">
                                <input type="text" name="email" required="" class="form-control form_setup2" value="" placeholder="Email here">
                                 <button type="submit" class="btn btn-success set__but2">Subscribe</button>

                            </div>
                            <span class="form_sub">Newsletter for small actionable steps.</span>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer container-fluid">

            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <div class="logo__des"> <img class="img-fluid" src="{{ asset('').getPageContent('site', 'footer_logo') }}">
                            <p>{{ getPageContent('site','footer_text') }}</p>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3 links pl-5 quick_link">
                        <h3>Company</h3>
                        <ul>
                            <li><a class="footer-link" href="#">Support</a></li>
                            <!--<li><a class="footer-link" href="#">FAQs</a></li>-->
                            <li><a class="footer-link" href="{{ route('about') }}">About Us</a></li>
                            <li><a class="footer-link" href="{{ route('blog') }}">Blog</a></li>
                            <li><a class="footer-link" href="{{ route('poadcast') }}">PodCast</a></li>
                            <li><a class="footer-link" href="#">YouTube</a></li>
                            <!--@foreach (getPages()->get() as $page)-->
                            <!--    <li> <a class="footer-link" href="{{ route('pages', $page->slug) }}">{{ $page->title }}</a></li>-->
                            <!--@endforeach-->
                        </ul>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3 links pl-5">
                        <h3>Services</h3>
                        <ul>
                            <li><a class="footer-link" href="{{ route('coaching') }}">1 on 1 Coaching</a></li>
                            <li><a class="footer-link" data-toggle="modal" data-target="#GroupCoachingModal"  href="">Group Coaching</a></li>
                            <li><a class="footer-link" data-toggle="modal" data-target="#WebinarsModal"  href="">Webinars</a></li>
                            <li><a class="footer-link" href="{{ route('course') }}">Mini Course</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3 links">
                        <h3>How Can We Help?</h3>
                        <ul>
                            <li><img src="{{ asset('/front') }}/images/icon_ph.png"></li>
                            <li>Contact Now</li>
                            <li>{{ getPageContent('site','phone') }}</li>
                            <!--<li>24/7 At Your Service</li>-->
                        </ul>
                        <ul class="social-media">
                        <li><a href="{{ getPageContent('site','facebook') }}" target="_blank"><i class="bi bi-facebook"></i></a></li>
                        <li><a href="{{ getPageContent('site','linkedin') }}" target="_blank"><i class="bi bi-linkedin"></i></a></li>
                        <li><a href="{{ getPageContent('site','instagram') }}" target="_blank"><i class="bi bi-instagram"></i></a></li>
                        <li><a href="{{ getPageContent('site','tiktok') }}" target="_blank"><i class="bi bi-tiktok"></i></a></li>
                        <li><a href="{{ getPageContent('site','youtube') }}" target="_blank"><i class="bi bi-youtube"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="row lower__footer">
                    <div class="col-sm-5"><span class="copy_right">Copyright {{ date('Y') }} All Right Reserved</span></div>
                    <div class="col-sm-7">
                        <nav class="nav footer__menu">
                            @foreach (getPages()->limit(4)->get() as $page)
                                <a class="nav-link" href="{{ route('pages', $page->slug) }}">{{ $page->title }}</a>
                                {{-- <a class="nav-link" href="#">PRIVACY POLICY</a>
                                <a class="nav-link" href="#">LOCATIONS</a>
                                <a class="nav-link" href="#">ACCESSIBILITY</a> --}}
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>


   <!--email modal here-->
<div class="modal fade" id="email__modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<form id="guide-store" class="needs-validation" novalidate method="post" action="{{ route('guide-store') }}">
            @csrf
			<div class="modal-content mymodal">
				<div class="modal-header">
					<h5 class="modal-title mtitle" id="exampleModalLabel">Thank you for your interest</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
				</div>
				<div class="modal-body">
					<div class="container-contact1 row">
						<!--<div class="col-lg-4 col-md-12 col-sm-12 d-none d-lg-block text-center">-->
						<!--      <img src="{{ asset('app-assets/images/ct2.png')}}" class="img-fluid form_img"> -->
						<!--</div>		-->
						<div class="col-lg-12  ct__form">
							<div class="form-row">
								<div class="col-md-12 mb-3">
									<input type="email" name="email" class="form-control" id="validationCustom02" required placeholder="Email">
									<div class="invalid-feedback"> Enter a valid Email </div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer footer__modal">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button class="btn btn_submit" type="submit">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>
<!--email modal end -->


<!--modal code here -->
<div class="modal fade" id="GroupCoachingModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<form id="query" class="needs-validation" action="{{ route('store-query', "group-coaching") }}" method="post">
            @csrf
			<div class="modal-content mymodal">
				<div class="modal-header">
					<h5 class="modal-title mtitle" id="exampleModalLabel">Thank you for your interest</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
				</div>
				<div class="modal-body">
					<div class="container-contact1 row">
						<div class="col-lg-4 col-md-12 col-sm-12 d-none d-lg-block text-center"> <img src="{{ asset('app-assets/images/ct2.png')}}" class="img-fluid form_img"> </div>
						<div class="col-lg-8 col-md-12 col-sm-12 ct__form">
							<!--<span class="contact1-form-title">-->
							<!--     Thank you for your interest -->
							<!--   </span> -->
							<!--<form class="needs-validation" novalidate>-->
							<div class="form-row">
								<div class="col-md-12 mb-3">
									<!-- <label for="validationCustom01">First name</label> -->
									<input type="text" class="form-control" id="validationCustom01" required placeholder="Name" name="name">
									<div class="invalid-feedback"> Please provide a valid Name. </div>
								</div>
								<div class="col-md-12 mb-3">
									<input type="email" class="form-control" id="validationCustom02" required placeholder="Email" name="email">
									<div class="invalid-feedback"> Enter a valid Email </div>
								</div>
							</div>
							<div class="form-group">
								<textarea class="form-control" id="validationTextarea" placeholder="Message" name="message" required></textarea>
								<div class="invalid-feedback"> Please enter a message in the textarea. </div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer footer__modal">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button class="btn btn_submit" type="submit">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!--modal code here -->
<div class="modal fade" id="WebinarsModal" tabindex="-1" aria-labelledby="webinars" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<form id="query" class="needs-validation" action="{{ route('store-query', "webinars") }}" method="post">
            @csrf
			<div class="modal-content mymodal">
				<div class="modal-header">
					<h5 class="modal-title mtitle" id="exampleModalLabel">Thank you for your interest</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
				</div>
				<div class="modal-body">
					<div class="container-contact1 row">
						<div class="col-lg-4 col-md-12 col-sm-12 d-none d-lg-block text-center"> <img src="{{ asset('app-assets/images/ct2.png')}}" class="img-fluid form_img"> </div>
						<div class="col-lg-8 col-md-12 col-sm-12 ct__form">
							<!--<span class="contact1-form-title">-->
							<!--     Thank you for your interest -->
							<!--   </span> -->
							<!--<form class="needs-validation" novalidate>-->
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <!-- <label for="validationCustom01">First name</label> -->
                                    <input type="text" class="form-control" id="validationCustom01" required placeholder="Name" name="name">
                                    <div class="invalid-feedback"> Please provide a valid Name. </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <input type="email" class="form-control" id="validationCustom02" required placeholder="Email" name="email">
                                    <div class="invalid-feedback"> Enter a valid Email </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="validationTextarea" placeholder="Message" name="message" required></textarea>
                                <div class="invalid-feedback"> Please enter a message in the textarea. </div>
                            </div>
						</div>
					</div>
				</div>
				<div class="modal-footer footer__modal">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button class="btn btn_submit" type="submit">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>


<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();



            </script>

        </footer>

        <script src="https://kit.fontawesome.com/f8f06c3dcc.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

        @yield('scripts')

        <script>

            $(document).on("submit","#guide-store",function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                let url = $(this).attr('action');
                let $this = $(this);
                $(this).find('button[type="submit"]').prop('disabled',true);

                $.ajax({
                    type: 'post',
                    url: url,
                    data: formData,
                    dataType : 'json',
                    success: function (response) {
                        $this.find('button[type="submit"]').prop('disabled',false);

                        if(!response.status){
                            swal({
                                title: "Error!",
                                text: response.message,
                                icon: "warning",
                                button: "Close",
                            });
                        }
                        else{
                            $this.trigger("reset");
                            location.href = 'https://drive.google.com/file/d/1rBvy7DWVSHHBgMy_4NeGnjsknuTpPG1h/view?usp=share_link';
                            // swal({
                            //     //title: "Good job!",
                            //     text: response.message,
                            //     icon: "success",
                            //     button: "Close",
                            // }).then((willDelete) => {
                            //     $this.trigger("reset");
                            // });
                        }
                    },
                    error : function(errorThrown){
                    console.log(errorThrown);
                    }
                });
            });

             $('#newsletter-form').submit(function(e){
                e.preventDefault();
                var formData = $(this).serialize();
                let url = $(this).attr('action');
                let $this = $(this);
                $(this).find('button[type="submit"]').prop('disabled',true);

                $.ajax({
                    type: 'post',
                    url: url,
                    data: formData,
                    dataType : 'json',
                    success: function (response) {
                        $this.find('button[type="submit"]').prop('disabled',false);

                        if(!response.status){
                            swal({
                                title: "Error!",
                                text: response.message,
                                icon: "warning",
                                button: "Close",
                            });
                        }
                        else{
                            swal({
                                //title: "Good job!",
                                text: response.message,
                                icon: "success",
                                button: "Close",
                            }).then((willDelete) => {
                                $this.trigger("reset");
                            });
                        }
                    },
                    error : function(errorThrown){
                    console.log(errorThrown);
                    }
                });
            })

            $(document).on("submit","#query",function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                let url = $(this).attr('action');
                let $this = $(this);
                $(this).find('button[type="submit"]').prop('disabled',true);

                $.ajax({
                    type: 'post',
                    url: url,
                    data: formData,
                    dataType : 'json',
                    success: function (response) {
                        $this.find('button[type="submit"]').prop('disabled',false);

                        if(!response.status){
                            swal({
                                title: "Error!",
                                text: response.message,
                                icon: "warning",
                                button: "Close",
                            });
                        }
                        else{

                            swal({
                                //title: "Good job!",
                                text: response.message,
                                icon: "success",
                                button: "Close",
                            }).then((willDelete) => {
                                $(".side_form ").removeClass("slide_form");
                                $(".close ").click();
                                $this.trigger("reset");
                            });
                        }
                    },
                    error : function(errorThrown){
                    console.log(errorThrown);
                    }
                });
            })

        </script>

    </body>

</html>
