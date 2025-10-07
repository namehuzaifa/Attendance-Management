@extends('layouts.frontMaster')
@section('Home | '.config('app.name'))

@section('content')
<!-- banner -->

<style>
.contact-form {
  padding: 30px 40px;
  background-color: #ffffff;
  border-radius: 12px;
  max-width: 400px;
  border: 1px solid var(--green);
}

.contact-form textarea {
  resize: none;
}

.contact-form .form-input,
.form-text-area {
  background-color: #f0f4f5;
  height: 50px;
  padding-left: 16px;
}

.contact-form .form-text-area {
  background-color: #f0f4f5;
  height: auto;
  padding-left: 16px;
}

.contact-form .form-control::placeholder {
  color: #aeb4b9;
  font-weight: 500;
  opacity: 1;
}

.contact-form .form-control:-ms-input-placeholder {
  color: #aeb4b9;
  font-weight: 500;
}

.contact-form .form-control::-ms-input-placeholder {
  color: #aeb4b9;
  font-weight: 500;
}

.contact-form .form-control:focus {
  border-color: #f33fb0;
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.07), 0 0 8px var(--green);
}

.contact-form .title {
  text-align: center;
  font-size: 24px;
  font-weight: 500;
}

.contact-form .description {
  color: #aeb4b9;
  font-size: 14px;
  text-align: center;
}

.contact-form .submit-button-wrapper {
  text-align: center;
}

.contact-form .submit-button-wrapper input {
  border: none;
  border-radius: 4px;
  background-color: var(--green);
  color: white;
  text-transform: uppercase;
  padding: 10px 60px;
  font-weight: 500;
  letter-spacing: 2px;
}

.contact-form .submit-button-wrapper input:hover {
  background-color: #191970;
}



.side_form {
position: fixed;
right: -400px;
z-index: 999;
transition: 1s;


}


.set_form{
/*display:none;    */

}

/*end form */

.slide_form{
 right:0;
 transition: 1s;
}


.opn_form {
    position: absolute;
    transform: rotate(90deg);
    left: -127px;
    top: 13rem;
    border: none;
    border-radius: 4px;
    background-color: var(--green);
    color: white;
    text-transform: uppercase;
    padding: 10px 60px;
    font-weight: 500;
    letter-spacing: 2px;
}


.opn_form:hover{
 background-color: var(--green)!important;
}


.opn_form:focus {
    color: #fff;
    background-color:  var(--green)!important;
    border-color:  var(--green)!important;
    box-shadow: 0 0 0 0.2rem var(--green)!important;
    outline:0;
}


/*.opn_form:not(:disabled):not(.disabled):active, .show>.btn-success.dropdown-toggle {*/
/*    color: #fff;*/
/*    background-color: var(--green)!important;;*/
/*    border-color:var(--green)!important;;*/
/*}*/
/*.cap_email {*/
/*    width: 454px!important;*/
/*     font-weight: 600;*/
/*}*/

.cap_email span {
    font-size: 12px;
    font-weight: 100;
}


 .side_arrow {
    font-weight: bolder!important;
    font-size: 1.4rem!important;
}

</style>

<!--<script src="https://code.jquery.com/jquery-3.6.3.slim.min.js" integrity="sha256-ZwqZIVdD3iXNyGHbSYdsmWP//UBokj2FHAxKuSBKDSo=" crossorigin="anonymous"></script>-->


<div class="container-fluid h_banner">

    <div class="side_form">
        <button class="btn btn-success set_btn opn_form">Click me</button>
        <div class="set_form">
            <div class="contact-form-wrapper d-flex justify-content-center h_contact">
                <form id="query" action="{{ route('store-query', "contact") }}" method="post" class="contact-form">
                    @csrf
                     <button type="button" class="close">
                    <span aria-hidden="true">×</span>
                    </button>

                    <h5 class="title">Contact us</h5>
                    <p class="description">Feel free to contact us if you need any assistance, any help or another question. </p>
                    <div>
                        <input type="text" class="form-control rounded border-white mb-3 form-input" id="name" placeholder="Name" name="name" required> </div>
                    <div>
                        <input type="email" class="form-control rounded border-white mb-3 form-input" placeholder="Email" required name="email"> </div>
                    <div>
                        <textarea id="message" class="form-control rounded border-white mb-3 form-text-area" rows="5" cols="30" placeholder="Message" name="message" required></textarea>
                    </div>
                    <div class="submit-button-wrapper">
                        <input type="submit" value="Send"> </div>
                </form>
            </div>
        </div>
    </div>


	<!--<div class="container">-->
		<div class="row">
			<!--<div class="col-sm-8">-->
			<!--	<div class="banner_home_des"> <span>Change Your Thoughts, Change Your Life!</span>-->
			<!--		<h1>Feeling Angry, Frustrated, Anxious, or Unsatisfied?</h1> <span class="h__banner">Figure it out with Neary Coaching. Everyone needs a Life Coach.</span>-->
			<!--		<button id="banner__btn" class="btn btn-primary mt-4 banner_but">Free Session</button>-->
			<!--	</div>-->
			<!--</div>-->
			<div class="col-lg-6 col-md-12 col-sm-12">

                {{-- <video controls>
                <source src="{{asset('assets/test.mp4')}}" type="video/mp4">
                </video> --}}

            @if (getMediaType(getPageContent('home', 'header_video'),"img"))
                <img src="{{ asset('').getPageContent('home', 'header_video') }}" class="image">
            @endif
            @if (getMediaType(getPageContent('home', 'header_video'),"video"))
                <video controls> <source src="{{ asset('').getPageContent('home', 'header_video') }}"> </video>
            @endif

			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 content">
			    <div class="banner_home_des">
					<h1>{{ getPageContent('home','header_heading') }}</h1>
					<p>{{ getPageContent('home','header_text')  }}</p>
					<button id="banner__btn" class="btn btn-primary mt-4 banner_but">Take Your First Step</button>

				</div>
			</div>
			<!--<div class="col-lg-2 col-sm-12" ></div>-->
			<!-- banner images -->
			{{-- <div class="col-sm-12 home_icon">
                <img src="{{ asset('/front') }}/images/b2.png" class="img-fluid">
                <img src="{{ asset('/front') }}/images/b3.png" class="img-fluid">
                <img src="{{ asset('/front') }}/images/b4.png" class="img-fluid">
                <img src="{{ asset('/front') }}/images/b5.png" class="img-fluid">
                <img src="{{ asset('/front') }}/images/b6.png" class="img-fluid">
                <img src="{{ asset('/front') }}/images/b7.png" class="img-fluid">
            </div> --}}
		</div>
	<!--</div>-->
</div>
<!-- cards -->
<div class="container">
	<div class="row courses2">
		<div class="col-sm-12">
			<div class="description text-center">
				<h1>{{ getPageContent('home','after_head_heading')  }}</h1>
				<p>{{ getPageContent('home','after_head_text')  }} </p>
			</div>
		</div>
        @forelse ($sessions as $session)
            <div class="col-lg-4 col-sm-12 mt-5">
                <div class="card formward-mn"> <img src="{{ asset($session->image_id) }}" class="card-img-top mycard" alt="...">
                    <div class="card-body ">
                        <h5 class="card-title">{{ $session?->title }}</h5>
                        <p class="card-text">{{ $session?->description }}</p>
                        <a href="{{ route('session-detail', $session?->slug) }}" id="btn__setup_home" class="btn btn btn-outline-primary">join now</a>
                    </div>
                </div>
            </div>
        @empty
            <h2>No Session available </h2>
        @endforelse

	</div>
</div>
<!-- end cards -->

<!--======= new section start===== -->
<div class="container banner3">
<div class="row">
<div class="col-lg-12">

 <div class="life__coach">
<h1>How Can a Life Coach Help Me?</h1>

{!! getPageContent('home','coach_help_me') !!}
{{-- <ul>
<li>Your body and brain want you to survive.</li>
<li>They want you to conserve energy, avoid pain, and seek pleasure.</li>
<li>This means you naturally want to do what is easy, do what is comfortable, and do what feels good.</li>
<li>So it's easy to do what we have always done.</li>
<li>But what you have always done has brought you here.</li>
<li>Because you are without energy, in pain, and seeking relief.</li>
<li>The key to thriving is, ironically, locked in your brain.</li>
<li>It's hidden behind the negative stories your brain is telling you.</li>
<li>It's hidden in the 60K thoughts you have each day.</li>
<li>When you start coaching, you begin to see these thoughts for the first time.</li>
<li>You will see how: Your thoughts are built upon your values, beliefs, and assumptions.</li>
<li>Your thoughts determine how you feel.</li>
<li>Your emotions motivate your actions.</li>
<li>Your actions are creating your outcomes.</li>
<li>If you aren't showing up as the business and family leader you want to, it's because of the automatic, habitual thoughts you have in your brain.</li>
<li>This programming has gone undetected for most of your life. Not any more.</li>
<li>A change in just a single one of your thoughts can change the outcome your business, your relationships, your life, and lives of your loved ones and employees.</li>
<li>When you start coaching you will begin to evolve in new ways..</li>

<li>You will experience less dissonance between who you are and who you want to be. </li>

<li>You will experience lower conflict in yourself and with others.</li>

<li>Your intentional alignment with your values will make you more confident and purposeful.</li>

<li>You'll procrastinate less and have less disappointment.</li>

<li>You will have more energy to create value and abundance.</li>

<li>Your life will begin to change dramatically in just one session per week.</li>

<li>You will experience light bulb popping, mind blowing, life changing realizations each week.</li>

<li>With The First Step, you can sign up for your first consultation immediately.</li>

</ul> --}}

<button id="banner__btn" class="btn btn-primary mt-4 banner_but">Schedule My Consultation
</button>
</div>

</div>
</div>





</div>




<!-- ============ who we are========== -->
<div class="container">
	<div class="row">
		<!--<div class="col-sm-12 col-md-12 col-lg-2 sec9"></div>-->
		<div class="col-sm-12 col-md-12 col-lg-6">
		    <div class="who_img">
            <img src="{{ asset('').getPageContent('home', 'about_image') }}" class="img-fluid main_img">
            </div>
        </div>

		<div class="col-sm-12 col-md-12 col-lg-6">
			<div class="who_we_are">

                {{-- <span class="sec__who">About Zef Neary</span> --}}
				<h1 class="sec__who">About Zef Neary</h1>
                <pre style="white-space: break-spaces;" class="sec_para3">{{ getPageContent('home','about_text')  }}</pre>
				<div class="coaching mt-4 mb-4" >
                    <a href="{{ route('coaching') }}" id="banner__btn2" class="btn btn-primary">Meet with Zef 1-on-1</a>
                    {{-- <span><b>01</b> 1 On 1 Coaching</span> --}}
                    {{-- <span><b>02</b> Group Coaching </span> --}}
                </div>
                <div class="max_width">
				{{-- <p class="sec_para2">At Neary Coaching, we believe the first step to feeling better and getting unstuck is learning how to manage your mind and harness your emotions. We work with our clients to rewrite how they approach the challenges they face in their companies and in life. We have helped small business owners and managers just like you break through their barriers and reach new heights.</p> --}}
				<div class="coaching">
					<a href="https://drive.google.com/file/d/1rBvy7DWVSHHBgMy_4NeGnjsknuTpPG1h/view?usp=share_link"  data-toggle="modal" data-target="#email__modal"  style="width: 100%;" id="banner__btn2" class="btn btn-primary">Download the Business Owner’s Guide to Managing Anxiety</a>
					{{-- <a class="" href="index">
                        <img class='img-fluid phone_icon' src="{{ asset('/front') }}/images/phone.png">
						<div class="mobile_sec">
                            <span class="ques">Have any Question?</span>
                            <span class="mob__number">8016362608</span>
                        </div>
					</a> --}}
				</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 mywho_sec">
			<div class="who_we_are">

				<p class="sec_para2">At Neary Coaching, we believe the first step to feeling better and getting unstuck is learning how to manage your mind and harness your emotions. We work with our clients to rewrite how they approach the challenges they face in their companies and in life. We have helped small business owners and managers just like you break through their barriers and reach new heights.</p>
				<div class="coaching">
					<a href="https://drive.google.com/file/d/1rBvy7DWVSHHBgMy_4NeGnjsknuTpPG1h/view?usp=share_link"  data-toggle="modal" data-target="#email__modal"  style="width: 100%;" id="banner__btn2" class="btn btn-primary">Download the Business Owner’s Guide to Managing Anxiety</a>
					{{-- <a class="" href="index">
                        <img class='img-fluid phone_icon' src="{{ asset('/front') }}/images/phone.png">
						<div class="mobile_sec">
                            <span class="ques">Have any Question?</span>
                            <span class="mob__number">8016362608</span>
                        </div>
					</a> --}}
				</div>
			</div>
		</div>
	</div>
</div>
<!-- =========== end who we are ========= -->
<!-- banner 2 -->
<div class="container-fluid main_banner3">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="banner_info2 banner_new text-center">
					<h1>It's Time for Change</h1>
					<p>Free Coaching through the end of February</p>
					<button id="banner__btn" class="btn btn-primary cap_email"> <span class="side_arrow"> >> </span> CLICK HERE To schedule a Free Coaching Session <br><span> No Contracts, No Commitments </span></button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- ============ work========== -->
<div class="container mt-5 mb-5">
	<div class="row hw_work">
		<div class="col-sm-12 col-md-12 col-lg-6 mt-3">
            {{-- <span class="sec__who">HOW WE WORK</span> --}}
			<h1>Join Courageous!</h1>
			{{-- <p class="sec_para3">You feel like you are the only one who is struggling. Your isolation and overwhelm make it difficult to move forward.</p> --}}
			<pre style="white-space: break-spaces;" class="sec_para3">{{ getPageContent('home','courageous_text')  }}</pre>
                {{-- <ul class="work_setup">
                    <li><div class="work__info">
                        <div class="circle"></div>
                        <div class="work_des">

                            <p class="sec_para3">You feel like you are the only one who is struggling. Your isolation and overwhelm make it difficult to move forward.</p>
                        </div>
                    </div></li>
                    <li><div class="work__info">
                        <div class="circle"></div>
                        <div class="work_des">
                            <p class="sec_para3">When you join Courageous Group Coaching, you will be connected with business leaders just like you. People who understand what you are going through and want to help you move past it.</p>
                        </div>
                    </div></li>
                    <li><div class="work__info">
                        <div class="circle"></div>
                        <div class="work_des">
                            <p class="sec_para3">In Courageous, you will get the support and coaching that you need to overcome your overwhelm and move forward in your business.</p>
                        </div>
                    </div></li>
                </ul> --}}
			<div class="coaching">
				<button id="banner__btn2" class="btn btn-primary">Find Out What We Do</button>
				{{-- <a class="" href="index"> <img class='img-fluid phone_icon' src="{{ asset('/front') }}/images/phone.png">
					<div class="mobile_sec"> <span class="ques">Have any Question?</span> <span class="mob__number">8016362608</span> </div>
				</a> --}}
			</div>
		</div>
		<div class="col-sm-12 col-md-12 col-lg-6"> <img src="{{ asset('').getPageContent('home', 'courageous_image') }}" class="img-fluid set__images"> </div>
	</div>
</div>
<!-- =========== end work ========= -->

<!--<div class="container-fluid main_banner3" style="height: 250px;">-->
<!--	<div class="container">-->
<!--		<div class="row">-->
<!--			{{-- <div class="col-sm-12">-->
<!--				<div class="banner_info2 banner_new text-center">-->
<!--					<h1>Get Started Now With Life Coaching</h1>-->
<!--					<p>Quisque porta nibh quis nibh scelerisque auctor. Vestibulum ante ipsum primis in faucibus orci luctus et-->
<!--						<br>ultrices posuere cubilia curae; Aenean sagittis eget neque ac consequat.</p>-->
<!--					<button id="banner__btn" class="btn btn-primary">Free Consulting</button>-->
<!--				</div>-->
<!--			</div> --}}-->
<!--		</div>-->
<!--	</div>-->
<!--</div>-->

<!-- ============ work========== -->
<div class="container mt-5 mb-5 ">
	<div class="row reserve">
		<div class="col-sm-12 col-md-12 col-lg-6">
            <img style="border-radius: 10px;" src="{{ asset('').getPageContent('home', 'podcast_image') }}" class="img-fluid set__images"> </div>
		<div class="col-sm-12 col-md-12 col-lg-6 mt-3">
			<h1>Listen to The Emotional Man Weekly Podcast</h1>
			<p class="sec_para3">{{ getPageContent('home','podcast_text')  }}</p>

			<div class="coaching">
				<button id="banner__btn2" class="btn btn-primary">Subscribe to The Emotional Man Weekly Podcast</button>
			</div>
		</div>
	</div>
</div>

</div>
<!-- testimonial slider -->

@include('modules.front.slider')

<!-- ========== end slider =========== -->


@endsection
@section('scripts')
<script>





      $(document).ready(function(){
           $('.opn_form').click(function(){
            //$(".set_form").slideToggle("slow");
            $(".side_form").toggleClass("slide_form");

            //alert("hello");
        });

    });



     $(document).ready(function(){
           $('.close').click(function(){
            //$(".set_form").slideToggle("slow");
            $(".side_form").toggleClass("slide_form");
        });

    });

</script>

@endsection
