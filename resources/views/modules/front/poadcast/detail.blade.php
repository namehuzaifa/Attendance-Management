@extends('layouts.frontMaster')
@section('Join  | '.config('app.name'))
@section('content')
<!-- banner -->

<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info2">
			<h2>Home / PODCAST WITH ZEF NEARY / Join Courageous</h2> </div>
	</div>
</div>
<!-- section join -->
<section class="container mt-5">
	<div class="row">
		<div class="col-sm-6"> <img src="{{ asset( ($poadcast?->image_url) ? $poadcast?->image_url : 'assets/images/no-preview.png' ) }}" class="img-fluid"> </div>
		<div class="col-sm-6 mydescription">
			<h2>{{ $poadcast?->title }}</h2>
            <h5>Description</h5>
            <pre style="white-space: pre-wrap;">{{ $poadcast?->description }}</pre>
            <audio controls>
                <source src="{{ asset($poadcast?->audio_url) }}" type="audio/ogg">
                <source src="{{ asset($poadcast?->audio_url) }}" type="audio/mpeg">
              Your browser does not support the audio element.
            </audio>

            @if (!empty($poadcast?->links))
                <div class="mt-3">
                    Subscribe in
                    {{-- @dd($poadcast?->links) --}}
                    @foreach ($poadcast?->links as $link)
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
@forelse ($poadcasts as $poadcast1)
    <div class="row unit">
        <div class="col-sm-2"><img src="{{ asset( ($poadcast1?->image_url) ? $poadcast1?->image_url : 'assets/images/no-preview.png' ) }}" class="img-fluid set__img"></div>
        <div class="col-sm-8 projects">
            <a href="{{ route('poadcast-detail', $poadcast1?->slug) }}" style="color: black; text-decoration: none;">
                <div class="new_section">
                    <h2>{{ $poadcast1?->title }}</h2>
                </div>
            </a>
            <p>{{ $poadcast1?->description }}</p>
            <a href="{{ route('poadcast-detail', $poadcast1?->slug) }}" id="btn__setup2" class="btn btn-outline-primary">See more</a>
        </div>
    </div>
@empty
    <h2>No other Podcast available </h2>
@endforelse

	</div>
</div>
<!-- end section -->

@endsection
