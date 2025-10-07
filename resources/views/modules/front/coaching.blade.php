@extends('layouts.frontMaster')
@section('title', 'Coaching  | '.config('app.name'))
@section('content')

<!-- banner -->
<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info">
			<h2>Coaching</h2>
			<h1>WITH ZEF NEARY</h1> </div>
	</div>
</div>

<!-- cards -->
<div class="container">
	<div class="row courses">

        @forelse ($sessions as $session)
            <div class="col-sm-12 col-lg-4 col-md-6  mt-5">
                <div class="card"> <img src="{{ asset($session->image_id) }}" class="card-img-top mycard" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">{{ $session?->title }}</h5>
                        {{-- <pre style="white-space: pre-wrap;">{{ $session?->description }}</pre> --}}
                        <p class="card-text">{{ $session?->description }}</p>
                        <a href="{{ route('session-detail', $session?->slug) }}" id="btn__setup" class="btn btn btn-outline-primary">join now</a>
                    </div>
                </div>
            </div>
        @empty
            <h2>No Session available </h2>
        @endforelse

	</div>
</div>
<!-- end cards -->
@endsection

