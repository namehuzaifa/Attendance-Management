@extends('layouts.frontMaster')
@section('title', $content->title.' | '.config('app.name'))
@section('style')
<style>
    p.card-text {
        max-height: 70px;
        min-height: 70px;
        overflow: hidden;
   }

   pre {
    font-size: 17px;
    white-space: pre-wrap;
   }

    .ql-editor > * {
        cursor: default !important;
    }
</style>
@endsection
@section('content')

<!-- banner -->
<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info">
			<h2>{{ $content->title }}</h2>
			<h1>WITH ZEF NEARY</h1> </div>
	</div>
</div>

<!-- cards -->

@foreach ($content->sectionsFront ?? [] as $section)
    <section class="ql-editor" style="background:{{ $section->section_colour }} ">
        <div class="container" >

            {!! $section->content !!}

        </div>
    </section>
@endforeach

<!-- end cards -->
<!-- banner 2 -->
@endsection

