<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>


    <style type="text/css">
        /*
        .testimonial_review {
            display: flex;
            align-items: center;
            flex-direction: column;
        }*/

        .testimonial_review1 {
            display: flex;
            align-items: center;
            flex-direction: column;
            /*position: absolute;
            top: 18rem;
            left: 8rem;*/
            /*    margin-top: 32%;*/
        }

        .testimonial_review2 {
            display: flex;
            align-items: center;
            flex-direction: column;
            /* position: absolute;
            top: 18rem;
            left:32rem;*/
            /*    margin-top: 32%;*/
        }

        .testimonial_review3 {
            display: flex;
            align-items: center;
            flex-direction: column;
            /*    position: absolute;*/
            /*top: 18rem;
            left:55rem;*/
        }

        .box1 {
            height: 340px;
    /* border-radius: 12px; */
    margin: 0 1rem;
    padding: 1rem 1rem 1rem 2rem;
    background: #fff;
    color: #000;
    width: 30%;
    /* display: inline-grid; */
    border: 1px solid #707070;
        }

        .review__des1 {
            text-align: center;
        }

        .review__des1 h6 {
            font-size: 22px;
            color: #191970;
        }
        .quotation {
    width: 100%;
    height: fit-content;
    /* padding: 20px 0; */
    background-color: #fff;
    text-align: center;
}

        .review__des1 p {
            font-size: 15px;
        }

        .slider_info {
            position: relative;
            width: 100%;
            overflow: hidden;
            height: 500px;
        }

        .multi__reviews {
            background: #006d5b59!important;
        }

        .review__para {
            overflow-y: scroll;
    margin: 0;
    padding-bottom: 1rem;
    height: 190px;
    padding-right: 20px;
    margin-bottom: 20px;
        }
        .review__para::-webkit-scrollbar {
      width: 3px;

}

.review__para::-webkit-scrollbar-track {
  box-shadow: inset 0 0 6px rgb(255, 255, 255);
}

.review__para::-webkit-scrollbar-thumb {
  background-color: #006d5b;
border-radius:10px;
}
.slick-arrow {
    position: absolute;
    z-index: 9;
    top: 0;
    bottom: 90px;
    margin: auto;
    height: fit-content;
    font-size:0;
    width:50px;
    height:50px;
    border:none;
    color:#000;
    line-height:1em;
    border-radius:40px;
}

.slick-prev.slick-arrow {
    left: -79px;
    right: unset;
}
.slick-prev.slick-arrow:before {
    content: "\2192";
    font-size:24px;
    height: 0.14em;
    transform: rotate(180deg);
    display: block;
}
.slick-next.slick-arrow {
    right: -60px;
    left: unset;
}
.slick-next.slick-arrow:before {
    content: "\2192";
    font-size:24px;
    height: 0.14em;
    display: block;
}

        .img_test {
            width: fit-content;
    margin: auto;
    padding-bottom: 25px;
    padding-top: 15px;
        }

        .main_sec span {
            font-size: 22px;
            font-family: 'Montserrat';
            color: #191970;
        }

        .main_sec h1 {
            font-size: 46px;
            font-family: 'Montserrat';
        }

        img.rounded {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 100px !important;
        }


.review {
    display: flex;
    align-items: center;
    justify-content: space-around;
    margin-top: 1rem;
    width: 95%;
}


.mytest{
display:none;

}
.slick-list.draggable {
    height: 455px;
}
.slick-track{
    display: flex;
}

@media (max-width: 1300px){

 .review {
    display: flex;
    align-items: center;
    justify-content:space-around;
    margin-top:9rem;
}

}

@media (max-width: 1199.98px){

.review__para {
    overflow-y: scroll;
    margin: 0;
    padding-bottom: 1rem;
    height: 207px;
    padding-right: 20px;
}

.set__but {
    position: relative;
    left: 58%;
    top: -5.5rem;
    background: #006D5B;
    border-color: #006D5B;
    width: 180px;
    height: 48px;
    font-size: 16px;
    font-family: Montserrat;
}


}


@media (max-width: 991.98px){

.review{
display:none;
}

.mytest{
display:block;

}


.set__but {
    position: relative;
    left: 72%;
    top: -5.5rem;
    background: #006D5B;
    border-color: #006D5B;
    width: 180px;
    height: 48px;
    font-size: 16px;
    font-family: Montserrat;
}


}


    </style>

    <!-- slider  -->
    <div class="container-fluid multi__reviews">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="main_sec text-center mt-5 mb-5"> <span>TESTIMNONIALS</span>
                        <h1>Sweet Words From Clients</h1> </div>
                </div>
            </div>

                        <div class="row testimonial__slider">
                            @php
                                use App\Models\Testimnonials;
                                $testimnonials = Testimnonials::all();
                            @endphp
                            @foreach ($testimnonials as $testimnonial)
                                <div class="box1"> <div class="quotation">
                                <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test"></div>
                                    <p class="review__para">{{ $testimnonial?->description }}</p>
                                    <div class="testimonial_review1"> <img src="{{ asset( (isset($testimnonial?->image)) ? $testimnonial?->image : 'assets/images/no-preview.png' ) }}" class="rounded">
                                        <div class="review__des1">
                                            <h6>{{ $testimnonial?->name }}</h6>
                                            <p>{{ $testimnonial?->designation }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>


                    {{-- <div class="carousel-item active">
                        <div class="row">
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">Before working with Neary Coaching, I found myself angry at colleagues and frustrated by a lack of progress at work. After just two sessions, I was able to resolve a lot of the work conflict and make progress on my goals that I wouldn’t have considered possible.</p>
                                <div class="testimonial_review1 mytest"> <img src="{{ asset('/front') }}/images/testimonal-1.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Robert Jager</h6>
                                        <p>D.C</p>
                                    </div>
                                </div>
                            </div>
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">I found coaching clarifying on where I stood and helped me to determine a clear action plan to get where I wanted to go. Work Neary coaching a few times. It is the 2nd and 3rd time that i saw momentum build and my understanding grow.</p>
                                <div class="testimonial_review2 mytest"> <img src="{{ asset('/front') }}/images/testimonal-2.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Marshall Saunders</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                            </div>
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">Joseph at Neary Coaching helped me realize that there were unnecessary expectations I had for myself. Letting go of those expectations has helped me feel less overwhelmed. It's worth it, even a few sessions can make a big difference.</p>
                                <div class="testimonial_review3 mytest"> <img src="{{ asset('/front') }}/images/testimonal-3.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Sarah Best</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="review">
                         <div class="testimonial_review1"> <img src="{{ asset('/front') }}/images/testimonal-1.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Robert Jager</h6>
                                        <p>D.C</p>
                                    </div>
                                </div>
                         <div class="testimonial_review2"> <img src="{{ asset('/front') }}/images/testimonal-2.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Marshall Saunders</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                         <div class="testimonial_review3"> <img src="{{ asset('/front') }}/images/testimonal-3.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Sarah Best</h6>
                                        <p>UT</p>
                                    </div>
                                </div>

                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="row">
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">Before working with Neary Coaching, I found myself angry at colleagues and frustrated by a lack of progress at work. After just two sessions, I was able to resolve a lot of the work conflict and make progress on my goals that I wouldn’t have considered possible.</p>
                                <div class="testimonial_review1 mytest"> <img src="{{ asset('/front') }}/images/testimonal-1.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Robert Jager</h6>
                                        <p>D.C</p>
                                    </div>
                                </div>
                            </div>
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">I found coaching clarifying on where I stood and helped me to determine a clear action plan to get where I wanted to go. Work Neary coaching a few times. It is the 2nd and 3rd time that i saw momentum build and my understanding grow.</p>
                                <div class="testimonial_review2 mytest"> <img src="{{ asset('/front') }}/images/testimonal-2.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Marshall Saunders</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                            </div>
                            <div class="box1"> <img src="{{ asset('/front') }}/images/b1.png" class="img-fluid img_test">
                                <p class="review__para">Joseph at Neary Coaching helped me realize that there were unnecessary expectations I had for myself. Letting go of those expectations has helped me feel less overwhelmed. It's worth it, even a few sessions can make a big difference.</p>
                                <div class="testimonial_review3 mytest"> <img src="{{ asset('/front') }}/images/testimonal-3.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Sarah Best</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="review">
                         <div class="testimonial_review1"> <img src="{{ asset('/front') }}/images/testimonal-1.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Robert Jager</h6>
                                        <p>D.C</p>
                                    </div>
                                </div>
                         <div class="testimonial_review2"> <img src="{{ asset('/front') }}/images/testimonal-2.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Marshall Saunders</h6>
                                        <p>UT</p>
                                    </div>
                                </div>
                         <div class="testimonial_review3"> <img src="{{ asset('/front') }}/images/testimonal-3.png" class="rounded">
                                    <div class="review__des1">
                                        <h6>Sarah Best</h6>
                                        <p>UT</p>
                                    </div>
                                </div>

                        </div>

                    </div>
                </div>
            </div> --}}
            <!-- <button class="carousel-control-prev" type="button" data-target="#carouselExampleControls" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </button> -->
            <!-- <button class="carousel-control-next" type="button" data-target="#carouselExampleControls" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </button> -->
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script>
        $('.testimonial__slider').slick({
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1
        });

    </script>


    <!-- end slider -->

