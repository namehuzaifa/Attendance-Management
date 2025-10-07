@extends('layouts.frontMaster')
@section('Join  | '.config('app.name'))
@section('content')
<!-- banner -->

<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info2">
			<h2>Home / Course WITH ZEF NEARY / Join Courageous</h2> </div>
	</div>
</div>
<!-- section join -->
<section class="container mt-5">
	<div class="row">
		<div class="col-sm-6"> <img src="{{ asset( ($course?->image_id) ? $course?->image_id : 'assets/images/no-preview.png' ) }}" class="img-fluid"> </div>
		<div class="col-sm-6 mydescription">
			<h2>{{ $course?->title }}</h2>

            <h5>Description</h5>
            <pre style="white-space: pre-wrap;">{{ $course?->description }}</pre>
            @isset($course?->courses_code)
                <h5>Courses Code</h5>
                <p>{{ $course?->courses_code }}</p>
            @endisset

            @if (isset($course->pdf_url) && $course->pdf_url)
                <a href="{{ asset($course?->pdf_url) }}"  download id="btn__setup2" class="btn btn-outline-primary">Download PDF</a>
            @endif

            @if (!empty($course?->links))
                <div class="mt-3">
                    Subscribe in
                    {{-- @dd($course?->links) --}}
                    @foreach ($course?->links as $link)
                        @if (isset($link?->link) && $link?->link != "")
                            <a href="{{ (isset($link?->link)) ? $link?->link : '' }}"> {{ (isset($link?->name)) ? $link?->name : '' }} |  </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
	</div>
</section>

<div class="container mt-5">
	<div class="box3">
		<!-- first -->
@forelse ($courses as $course1)
    <div class="row unit">
        <div class="col-sm-2"><img src="{{ asset( ($course1?->image_id) ? $course1?->image_id : 'assets/images/no-preview.png' ) }}" class="img-fluid set__img"></div>
        <div class="col-sm-8 projects">
            <a href="{{ route('course-detail', $course1?->slug) }}" style="color: black; text-decoration: none;">
                <div class="new_section">
                    <h2>{{ $course1?->title }}</h2>
                </div>
            </a>
            <p>{{ $course1?->description }}</p>
            <a href="{{ route('course-detail', $course1?->slug) }}" id="btn__setup2" class="btn btn-outline-primary">See more</a>
        </div>
    </div>
@empty
    <h2>No other Course available </h2>
@endforelse

	</div>
</div>
<!-- end section -->

@endsection
