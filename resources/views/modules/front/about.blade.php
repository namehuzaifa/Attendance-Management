@extends('layouts.frontMaster')
@section('title', 'About  | '.config('app.name'))
@section('content')

<style type="text/css">

.about__txt h1 {
 text-align:center;
 font-size:30px;
}

.des_txt p {
    padding-top: 2rem;
    font-size: 1.3rem;
    font-weight: 500;
}

.des_txt h1 {
margin-top:2rem;
}


#banner__btn3 {
    background: #191970;
    border: #191970;
    font-size: 16px;
    padding: 1rem 8rem;
}

.des_txt2 h1{
text-align:center;
}




.banner3 {
    background: #006d5b8f;
    background-repeat: no-repeat;
    background-size: cover;
    color: #fff;
    border-radius: 18px;
    /* text-align: center; */
    padding: 6rem 6rem;
    box-shadow: #006d5b8f 0px 3px 8px;
    margin-bottom: 2rem;
}


ul {
    margin: 0;
    padding: 0;
    list-style: none;
}


 ul li {
    /*line-height: 3rem;*/
    /*font-size: 1.2rem;*/
}


.des_txt2 h6 {
    font-size: 1.6rem;
    font-weight: 300;
    padding-top: 2rem;
    text-decoration: underline;

}

.des_txt2 p {
    font-size: 1.2rem;
    padding-top: 1rem;
}

</style>

<!-- banner -->
<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info">
			<h2>Coaching</h2>
			<h1>WITH ZEF NEARY</h1> </div>
	</div>
</div>


<div class="container mt-5 mb-5">
<div class="row">

	<div class="col-lg-6">
	<img src="{{ asset('').getPageContent('about', 'about_image') }}" class="img-fluid">
	</div>

	<div class="col-lg-6">
	<div class="des_txt">
	<h1>{{ getPageContent('about','heading')  }}</h1>
    <pre style="white-space: break-spaces; font-size: 21px;">{{ getPageContent('about','text')  }}</pre>
	{{-- <p>Neary Coaching was founded by Zef and Mel with the aim to help small business owners (and their employees and families!) identify and change the negative stories they tell themselves.</p>
	<p>If you are striving to rekindle your passion for life, discover greater meaning and purpose, or improve your health and relationships, then we can definitely help you.</p>
	<p>We are experts at helping others, and ourselves, managing their minds and balancing your lives.</p> --}}

	</div>
	</div>



	</div>


</div>


<div class="container banner3">
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12">

    <h1 class="text-center">Our history and milestones</h1>
	<div class="des_txt2">
        {!! getPageContent('about','our_history') !!}
	{{-- <h6>Education</h6>
	<ul>
	<li>Executive MBA</li>
	<li>Masters in Education</li>
	</ul>

    <h6>Work History</h6>
	<ul>
	<li>USAF Nuclear Launch Officer</li>
    <li>COO at CN Resource</li>
    <li>Neary Education Research</li>
    <li>Real Estate</li>
    <li>Foster Parents</li>

	</ul>

<p>We have experience coaching teens, homemakers, VPs, entrepreneurs and business owners, military members, working professionals, and our obstinate selves. </p>
<h6>Other Info</h6>
<p>Mel and Zef have 9 kids, which gives them plenty of experience dealing with chaos! They enjoy making bean to bar chocolate and playing board games with each other and their family.</p>
<p>They have learned through hard experience how to keep their lives balanced while juggling competing challenges such as:</p>

<ul>
<li>postpartum depression</li>
<li>autoimmune disease</li>
<li>anxiety</li>
<li>faith crises</li>
<li>infertility</li>
<li>graduate education</li>
<li>demanding work positions</li>
<li>nurturing new relationships with adopted and foster family</li>
<li>starting and managing their own company</li></ul> --}}
</div>
</div>
</div>
</div>







@endsection

