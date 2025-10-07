@extends('layouts.frontMaster')
@section('title', 'Podcast  | '.config('app.name'))
@section('style')
<style>
    p.card-text {
        max-height: 70px;
        min-height: 70px;
        overflow: hidden;
   }
</style>
@endsection
@section('content')

<!-- banner -->
<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info">
			<h2>Course</h2>
			<h1>WITH ZEF NEARY</h1> </div>
	</div>
</div>

<!-- cards -->
<div class="container">
	<div class="row courses">

        @forelse ($courses as $course)
            <div class="col-lg-4 col-md-6 mt-5">
                <div class="card"> <img src="{{ asset( ($course?->image_id) ? $course?->image_id : 'assets/images/no-preview.png' ) }}" class="card-img-top mycard" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">{{ $course?->title }}</h5>
                        <p class="card-text">{{ $course?->description }}</p>
                        <a href="{{ route('course-detail', $course?->slug) }}" id="btn__setup" class="btn btn btn-outline-primary">See more</a>
                    </div>
                </div>
            </div>
        @empty
            <h2>No Podcast available </h2>
        @endforelse

	</div>
</div>
<!-- end cards -->

@endsection

