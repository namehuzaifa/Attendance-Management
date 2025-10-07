@extends('layouts.frontMaster')
@section('Join  | '.config('app.name'))
@section('content')
<!-- banner -->

<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info2">
			<h2>Home / Coaching WITH ZEF NEARY / Join Courageous</h2> </div>
	</div>
</div>
<!-- section join -->
<section class="container mt-5">
	<div class="row">
		<div class="col-sm-12 col-lg-6"> <img src="{{ asset($session->image_id) }}" class="img-fluid"> </div>
		<div class="col-sm-12 col-lg-5 mydescription">
			<h2>{{ $session?->title }}</h2>
            <h4> <span>PRICE</span> : {{ ($session?->price_per_session) ? "$".$session?->price_per_session : 'Free' }}</h4>
            <h5>Description</h5>
            <pre style="white-space: pre-wrap;">{{ $session?->description }}</pre>
            <a href="{{ route('session-booking', $session->slug) }}" id="btn__setup" class="btn btn btn-outline-primary">join now</a>
        </div>
	</div>
</section>

<div class="container mt-5">
	<div class="box3">
		<!-- first -->
        @forelse ($sessions as $session1)
            <div class="row unit">
                <div class="col-sm-4 col-lg-2"><img src="{{ asset($session1->image_id) }}" class="img-fluid set__img"></div>
                <div class="col-sm-6 col-lg-8 projects">
                    <div class="new_section">
                        <h2>{{ $session1?->title }}</h2>
                        {{-- <img src="{{ asset('/front') }}/images/star.png" class="start_img">  --}}
                    </div>
                    <p>{{ $session1?->description }}</p>
                    <a href="{{ route('session-detail', $session1?->slug) }}" id="btn__setup2" class="btn btn-outline-primary">join now</a>
                </div>
                <div class="col-sm-2"> <a href="javascript:;" id="btn__setup3" class="btn btn-outline-primary">{{ ($session1?->price_per_session) ? "PRICE $".$session1?->price_per_session : 'Free' }}</a> </div>
            </div>
        @empty
            <h2>No other Session available </h2>
        @endforelse
	</div>
</div>

<!-- testimonial slider -->

@include('modules.front.slider')

<!-- end section -->
<!--<div class="container mt-5 mb-5">-->
<!--	<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">-->
<!--		<div class="carousel-inner">-->
<!--			<div class="carousel-item active">-->
<!--				<div class="box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t1.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div class="box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t2.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div class=" box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t3.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
<!--			<div class="carousel-item">-->
<!--				<div class="box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t1.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div class="box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t2.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div class=" box">-->
<!--					<p>Tree. Let moved Midst fifth appear, night seasons they're whose one you seed heaven behold multiply open form. God. Upon. Green and behold a creeping multiply moveth likeness divide.</p>-->
<!--					<div class="client_review"> <img src="{{ asset('/front') }}/images/t3.png" class="rounded">-->
<!--						<div class="review__des">-->
<!--							<h6>Martin Krishnan</h6>-->
<!--							<p>Designation</p>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
<!--		</div>-->
<!--	</div>-->
<!--</div>-->
<!-- end slider -->

@endsection
