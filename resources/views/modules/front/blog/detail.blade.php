@extends('layouts.frontMaster')
@section('Join  | '.config('app.name'))
@section('content')

<!-- banner -->

<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info2">
			<h2>Home / Blog</h2> </div>
	</div>
</div>
<!-- section join -->
<section class="container mt-5">
	<div class="row">
		<div class="col-sm-12 text-center"> <img width="500" src="{{ asset( ($blog?->image_url) ? $blog?->image_url : 'assets/images/no-preview.png' ) }}" class="img-fluid"> </div>
		<div class="col-sm-12 mydescription mt-5">
			<h2>{{ $blog?->title }}</h2>
            <!--<h5>Description</h5>-->
            <pre style="white-space: break-spaces; font-size: 17px;">{{ $blog?->description }}</pre>
        </div>
	</div>
</section>

<div class="container mt-5">
	<div class="box3">
		<!-- first -->
        @forelse ($blogs as $blog1)
            <div class="row unit">
                <div class="col-sm-2"><img src="{{ asset( ($blog1?->image_url) ? $blog1?->image_url : 'assets/images/no-preview.png' ) }}" class="img-fluid set__img"></div>
                <div class="col-sm-8 projects">
                    <a href="{{ route('blog-detail', $blog1?->slug) }}" style="color: black; text-decoration: none;">
                        <div class="new_section">
                            <h2>{{ $blog1?->title }}</h2>
                        </div>
                    </a>
                    <p>{{ $blog1?->description }}</p>
                    <a href="{{ route('blog-detail', $blog1?->slug) }}" id="btn__setup2" class="btn btn-outline-primary">See more</a> </div>
            </div>
        @empty
            <h2>No other Blog available </h2>
        @endforelse

	</div>
</div>
<!-- end section -->

@endsection
