@extends('layouts.frontMaster')
@section('title', 'Blog  | '.config('app.name'))
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
			<!--<h2>Blog</h2>-->
			<!--<h1>WITH ZEF NEARY</h1> </div>-->
				<h2>Home/Blog</h2>
		 </div>
	</div>
</div>

<!-- cards -->
<div class="container">
	<div class="row courses">

        @forelse ($blogs as $blog)
            <div class="col-lg-4 col-md-6 col-sm-12   mt-5">
                <div class="card"> <img src="{{ asset( ($blog?->image_url) ? $blog?->image_url : 'assets/images/no-preview.png' ) }}" class="card-img-top mycard" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">{{ $blog?->title }}</h5>
                        <p class="card-text">{{ $blog?->description }}</p>
                        <a href="{{ route('blog-detail', $blog?->slug) }}" id="btn__setup" class="btn btn btn-outline-primary">See more</a>
                    </div>
                </div>
            </div>
        @empty
            <h2>No blog available </h2>
        @endforelse

	</div>
</div>
<!-- end cards -->


@endsection

