@extends('layouts.frontMaster')
@section('Schedule | '.config('app.name'))
@section('content')

@section('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('/front/css/multiform_2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('/front') }}/build/jquery.datetimepicker.css"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    {{-- <link rel="stylesheet" href="{{ asset('/front') }}/css/font-awesome.min.css"> --}}

    <style type="text/css">

        thead{
        background: #191970;
        color:#fff;
        }

        table th{
        border-top:none!important;
        }

        table tr:first-child th:first-child{
            /*border: 2px solid orange;*/
            border-top-left-radius: 10px!important;

        }

        table tr:last-child th:last-child {
            /*border: 2px solid green;*/
            border-top-right-radius: 10px!important;
        }

        .cross{
            text-decoration:none!important;
            cursor: pointer;
        }

        .sessions_select{
            border: 1.5px solid midnightblue;
            border-radius: 10px;
            height: 150%;

        }

        .setup_table{
            position: relative;
        }


        .setup_table {
            position: relative;
            /* left: 2.7rem; */
            margin: 0!important;
            padding: 0!important;
        }

        div#session .slots {
            margin: 6px;
            margin-right: 20px;
            width: fit-content;
            padding: 4px 8px;
            color: midnightblue;
            border-radius: 4px;
            cursor: pointer;
            /* background: midnightblue; */
            border: 2px solid midnightblue;
            font-size: 18px;
        }
        div#session{
            /* display: contents;
            margin: 40px */
            margin-top: 25px;
            padding-top: 13px;
            border-top: 1px solid #d2d0d0;
        }

        div#session .slots.active {
            background: midnightblue;
            color: white;
        }

        div#session .booked {
            margin: 6px;
            margin-right: 20px;
            width: fit-content;
            padding: 4px 8px;
            color: #8a8a8a;
            border-radius: 4px;
            cursor: not-allowed;
            background: #e8e8e8;
            border: 2px solid #a8a8b2;
            font-size: 18px;
        }

        div#card-number-element, div#card-expiry-element, div#card-cvc-element {
            padding: 8px 15px 8px 15px;
            border: 1px solid #ccc;
            border-radius: 0px;
            margin-bottom: 15px;
            margin-top: 2px;
            width: 100%;
            box-sizing: border-box;
            font-family: montserrat;
            color: #2C3E50;
            background: #fff;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .StripeElement--focus{
            border-color: #f05537 !important;
            box-shadow: 0 0 10px rgb(0 0 0 / 11%);
        }

        .fa-spinner:before{
            font-family: 'FontAwesome';
        }

    </style>
@endsection
<!-- banner -->
<div class="container-fluid main_banner">
	<div class="container">
		<div class="banner_info2">
			<h2>Home / Coaching WITH ZEF NEARY / Schedule</h2> </div>
	</div>
</div>

    <style>
        body{
            background-color: #F5F1E9;
        }
        #calendar{
            margin-left: auto;
            margin-right: auto;
            width: 320px;
            font-family: 'Lato', sans-serif;
        }
        #calendar_weekdays div{
            display:inline-block;
            vertical-align:top;
        }
        #calendar_content, #calendar_weekdays, #calendar_header{
            position: relative;
            width: 320px;
            overflow: hidden;
            float: left;
            z-index: 10;
        }
        #calendar_weekdays div, #calendar_content div{
            width:40px;
            height: 40px;
            overflow: hidden;
            text-align: center;
            background-color: #FFFFFF;
            color: #787878;
        }
        #calendar_content{
            -webkit-border-radius: 0px 0px 12px 12px;
            -moz-border-radius: 0px 0px 12px 12px;
            border-radius: 0px 0px 12px 12px;
        }
        #calendar_content div{
            float: left;
        }
        #calendar_content div:hover{
            background-color: #F8F8F8;
        }
        #calendar_content div.blank{
            background-color: #E8E8E8;
        }
        #calendar_header, #calendar_content div.active{
            zoom: 1;
            filter: alpha(opacity=70);
            /* opacity: 0.7; */
        }
        #calendar_content div.active{
            color: #FFFFFF;
            background-color: #191970;

        }
        #calendar_header{
            width: 100%;
            height: 37px;
            text-align: center;
            background-color: #191970;
            padding: 18px 0;
            -webkit-border-radius: 12px 12px 0px 0px;
            -moz-border-radius: 12px 12px 0px 0px;
            border-radius: 12px 12px 0px 0px;
        }
        #calendar_header h1{
            font-size: 1.5em;
            color: #FFFFFF;
            float:left;
            width:70%;
        }
        i[class^=icon-chevron]{
            color: #FFFFFF;
            float: left;
            width:15%;
            border-radius: 50%;
        }
        .fa-angle-left:before ,.fa-angle-right:before {
            font-family: 'FontAwesome';
            cursor: pointer;

        }
        .select-date {
            cursor: pointer;
            font-weight: bold;
        }

        #calendar .disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }
    </style>

    <!-- main section  -->

    <div class="container-fluid">
        <div class="row justify-content-center main_calender">
            <div class="col-lg-8 col-md-12 col-sm-12 text-center p-0 mt-3 mb-2">
                <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                    <form id="msform" class="msform {{ ($session->price_per_session==0) ? "free":"" }}" method="POST" action="{{ route('session-create', $session->slug) }}">
                        @csrf
                        <!-- <h2 id="heading"> Sign Up Form </h2>   -->
                        <ul id="progressbar">
                            <li class="active" id="account"><strong> Schedule </strong></li>
                            <li id="personal"><strong> Account </strong></li>
                            @if ($session->price_per_session != 0)
                                <li id="payment"><strong> Payment </strong></li>
                            @endif
                            <li id="confirm"><strong> Finish </strong></li>
                        </ul>
                        <fieldset class="selectSlots">
                            <div class="row">
                                {{-- <div class="col-12">
                                    <select class="form-control form-control-sm sessions_select">
                                        <option>select session</option>
                                    </select>
                                </div> --}}
                                <div class="col-sm-12 col-lg-6 col-md-6  mt-5">
                                    <table class="table setup_table  table-striped main__table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Schedule</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Time</th>
                                                <th scope="col"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="schedule-table">
                                            <tr class="demo"><td></td><td>No session selected</td><td></td><td></td></tr>
                                            {{-- <tr>
                                                <th>1</th>
                                                <td>Mark</td>
                                                <td>Otto</td>
                                                <td><span class="cross" >&#10060;</span></td>
                                            </tr> --}}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-12 col-lg-6 col-md-6 mt-5">

                                    <div id="calendar">
                                        <div id="calendar_header">
                                            <i class="icon-chevron-left fa fa-angle-left"></i>
                                            <h1></h1>
                                            <i class="icon-chevron-right fa fa-angle-right"></i>
                                        </div>
                                        <div id="calendar_weekdays"></div>
                                        <div id="calendar_content"></div>
                                    </div>

                                    {{-- <input type="text" id="datetimepicker3" name="">  --}}
                                </div>

                                <div class="col-12" >
                                    <div class="row" id="session">

                                    </div>
                                </div>
                            </div>

                            <input type="button" name="next" class="next action-button" value="Next" />
                        </fieldset>

                        <fieldset class="userinfo">
                            <div class="form-card">
                                <div class="row">
                                    <div class="col-7">
                                        <h2 class="fs-title"> Account Information </h2> </div>
                                    <div class="col-sm-6">
                                        <label class="fieldlabels"> Name </label>
                                        <input type="text" name="name" placeholder="Name" value="@auth {{ Auth::user()?->name }} @endauth" /> </div>
                                    <div class="col-sm-6">
                                        <label class="fieldlabels"> Email </label>
                                        <input type="email" name="email" placeholder="info@youremail.com" value="@auth {{ Auth::user()?->email }} @endauth" />
                                    </div>

                                    <div class="col-sm-12">
                                        <label class="fieldlabels"> Phone </label>
                                        <input type="phone" name="phone" placeholder="" value="@auth {{ Auth::user()?->phone }}  @endauth" />
                                    </div>

                                    @if (Auth::user()->user_role == "admin")
                                        <div class="col-sm-12">
                                            <label class="fieldlabels">Select User </label>
                                            <select name="user_id" required class="form-control">
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-sm-12 mt-3">
                                        <label class="fieldlabels">Objective</label>
                                        <input type="text" name="objective" placeholder="What do you want to accomplish with Life Coaching? " /> </div>
                                    </div>
                            </div>
                            @if ($session->price_per_session != 0)
                                <input type="button" name="next" class="next action-button" value="Next" />
                            @else
                                <button type="submit" class="action-button">Submit</button>
                            @endif

                            <input type="button" name="pre" class="pre action-button-pre" value="Back" />
                        </fieldset>

                        @if ($session->price_per_session != 0)
                            <fieldset class="paymentInfo">
                                <div class="accordion" id="accordionExample">
                                    <div class="card-header">
                                        <h2 class="mb-0">
                                            <button class="btn btn-block text-left visa__accord" type="button" aria-expanded="true" aria-controls="collapseOne">
                                                Price Per Session <span class="btn-amount">${{ $session->price_per_session }}</span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h2 class="mb-0">
                                                <button class="btn btn-block text-left visa__accord" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                    <div>
                                                        <img src="{{ asset('/front') }}/images/card-default.png" class="img-fluid">  Credit/Debit Card
                                                    </div>
                                                    <img src="{{ asset('/front') }}/images/visa.png" class="img-fluid">
                                                </button>
                                            </h2>
                                        </div>

                                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="form-card">
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <h2 class="fs-title"> Account Information </h2> </div>
                                                        <div class="col-sm-12">
                                                            <label class="fieldlabels"> Card Holder Name </label>
                                                            <input type="text" name="card_holder_name" placeholder="Name" />
                                                        </div>

                                                        <div class="col-sm-12">
                                                            <label class="fieldlabels">Card number</label>
                                                            <div  style="width: 100%;" id="card-number-element" class="field"></div>
                                                        </div>
                                                        <div class="col-sm-12">
                                                            <label class="fieldlabels">Expiry date</label>
                                                            <div style="width: 100%;" id="card-expiry-element" class="field"></div>
                                                        </div>
                                                        <div class="col-sm-12">
                                                            <label class="fieldlabels">CVC</label>
                                                            <div style="width: 100%;" id="card-cvc-element" class="field"></div>
                                                        </div>

                                                        <div class="outcome">
                                                            <input type="hidden" name="token" value="">
                                                        <div style="color: red" class="error"></div>
                                                        <div class="success" style="display: none;"> Success! Your Stripe token is <span class="token"></span></div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="card">
                                        <div class="card-header" id="headingTwo">
                                            <h2 class="mb-0">
                                <button class="btn  btn-block text-left visa__accord collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <div><img src="{{ asset('/front') }}/images/paypal.png" class="img-fluid"> Paypal</div>
                                </button>
                                    </h2> </div>
                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="form-card">
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <h2 class="fs-title"> Account Information </h2> </div>
                                                        <div class="col-sm-12">
                                                            <label class="fieldlabels"> Card Holder Name </label>
                                                            <input type="text" name="uname" placeholder="Name" /> </div>
                                                        <div class="col-sm-8">
                                                            <label class="fieldlabels"> Card Number </label>
                                                            <input type="number" name="" placeholder="" /> </div>
                                                        <div class="col-sm-4">
                                                            <label class="fieldlabels"> CVC </label>
                                                            <input type="email" name="email" placeholder="..." /> </div>
                                                        <div class="col-sm-6">
                                                            <label class="fieldlabels"> Expiry Date </label>
                                                            <select id="inputState" class="form-control">
                                                                <option selected>Month</option>
                                                                <option>...</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label class="fieldlabels"> </label>
                                                            <select id="inputState" class="form-control mt-2">
                                                                <option selected>Year</option>
                                                                <option>...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                                <p class="info_sec">You may cancel or reschedule up to 24 hrs in advance. Failure to cancel or reschedule within 24 hrs will incur a $50 fee. Failure to show up for the call will incur a $125 fee.</p>
                                <p class="info_sec">Please note that your card will be charged after each session</p>
                                <button type="submit" class="action-button">Submit</button>
                                {{-- <a href="thankyou.php" class="action-button">Submit</a> --}}
                                <input type="button" name="pre" class="pre action-button-pre" value="Back" />
                            </fieldset>
                        @endif
                        <!--  <fieldset>
                            <div class="form-card">
                                <div class="row">
                                    <div class="col-7">
                                        <h2 class="fs-title"> Finish: </h2>
                                    </div>
                                    <div class="col-5">
                                        <h2 class="steps"> Step 4 - 4 </h2>
                                    </div>
                                </div> <br> <br>
                                <h2 class="purple-text text-center"><strong> SUCCESS ! </strong></h2> <br>
                                <div class="row justify-content-center">
                                    <div class="col-3"> <img src="1.png" class="fit-image"> </div>
                                </div> <br><br>
                                <div class="row justify-content-center">
                                    <div class="col-7 text-center">
                                        <h5 class="purple-text text-center"> You Have Successfully Signed Up </h5>
                                    </div>
                                </div>
                            </div>
                        </fieldset>   -->
                    </form>
                </div>
            </div>
        </div>
    </div>


	<!-- end main section  -->

@endsection


@section('scripts')
<!-- <script src="build/jquery.js"></script> -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="{{ asset('/front') }}/build/jquery.datetimepicker.full.js"></script>
<script type="text/javascript">

    var days = {!! $session?->getslots->pluck('days')->toJson() !!};
    var blackOutDate = {!! json_encode(explode(",",str_replace(" ", "",$session?->blackout_dates))) !!};
    var sectionLimit = {{ $session?->session_limit }};
    var monthLimit = {{ $session?->month_limit }}*30;

    // date time code
    $('#datetimepicker3').datetimepicker({ inline: true });

    $(document).ready(function() {

        var current_fs, next_fs, pre_fs;
        var opacity;
        var current = 1;
        var steps = $("fieldset").length;
        setProgressBar(current);


        $(".next").click(function() {
            current_fs = $(this).parent();

            var length = $('.schedule-row').length;
            if(current_fs.hasClass('selectSlots') && sectionLimit > length){
                var count = sectionLimit-length;
                alert('Select '+count+' more session');
                return false
            } else if (current_fs.hasClass('userinfo')){
                var status = false;
                 $('.userinfo').find('input').each(function(i,obj) {
                       if($(this).val() == "") {
                            status = true;
                            return false;
                        }
                    })

                    if (status) {
                        alert('Please fill Account Information')
                        return false
                    }
            }

            next_fs = $(this).parent().next();
            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
            next_fs.show();
            current_fs.animate({
                opacity: 0
            }, {
                step: function(now) {
                    opacity = 1 - now;
                    current_fs.css({
                        'display': 'none',
                        'position': 'relative'
                    });
                    next_fs.css({
                        'opacity': opacity
                    });
                },
                duration: 500
            });
            setProgressBar(++current);
        });

        $(".pre").click(function() {
            current_fs = $(this).parent();
            pre_fs = $(this).parent().prev();
            $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
            pre_fs.show();
            current_fs.animate({
                opacity: 0
            }, {
                step: function(now) {
                    opacity = 1 - now;
                    current_fs.css({
                        'display': 'none',
                        'position': 'relative'
                    });
                    pre_fs.css({
                        'opacity': opacity
                    });
                },
                duration: 500
            });
            setProgressBar(--current);
        });

        function setProgressBar(curStep) {
            var percent = parseFloat(100 / steps) * curStep;
            percentpercent = percent.toFixed();
            $(".pbar").css("width", percent + "%")
        }


        $(".submit").click(function() {
            return false;
        })

    });
</script>


<script>

    var date = '';

    $(document).on( 'click', '.slots', function(e){
        var $this = $(this);
        var time = $this.text();
        if($this.hasClass('active')){
            $this.removeClass('active');

        }else{
            $('.schedule-table .demo').hide();
            var length = $('.schedule-row').length;
            var count = length+1;
            var dateTime = date+`=`+time;
            var ifSetAlrady = jQuery(".sl_dt[value='"+dateTime+"']").length;

            if (ifSetAlrady) {
                alert('This Date Time select already');
                $this.addClass('active');

            } else if (sectionLimit <= length) {
                alert('Allow only '+sectionLimit+' session selected on this package')

            } else {

                $this.addClass('active');
                $('.schedule-table').append(`<tr class="schedule-row">
                    <input class="sl_dt" type="hidden" value="`+date+`=`+time+`" name="booking_date_time[]" />
                    <th class="length">`+count+`</th>
                    <td>`+date+`</td> <td>`+time+`</td>
                    <td><span class="cross remove-slots" >&#10060;</span></td> </tr>`);
            }
        }

    })

    $(document).on( 'click', '.remove-slots', function(e){
        $(this).parents('.schedule-row').remove();
        $('.schedule-table').children('.schedule-row').each(function(i, obj) {
            $(this).find('.length').text(i+1)
        });
        var length = $('.schedule-row').length;

        if (!length) {
            $('.schedule-table .demo').show();
        }
    })

    $(document).on( 'click', '.select-date', function(e){
        $('#calendar').find('.active').removeClass('active');
        $(this).addClass('active');

            date        =   $(this).attr('date');
        var sessionId   =   "{{ $session?->id }}"
        var weekday     =   $(this).attr('weekday');

        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        url = "{{ route('get-slots') }}"

        $.ajax({
            url: url,
            type: "POST",
            data: {date : date, id : sessionId, weekday : weekday},
        }).done(function (data) {

            var html = '';
            $.each(data.slots, function(key,val){
                if(jQuery.inArray(val, data.booked) != -1) {
                    html += '<div class="booked col-2">'+val+'</div>';
                } else {
                    html += '<div class="slots col-2">'+val+'</div>';
                }
            });
            $('#session').html(html);
        });

    })

    $(function () {
        function c() {
            p();
            var e = h();
            var r = 0;
            var u = false;
            l.empty();
            while (!u) {
            if (s[r] == e[0].weekday) {
                u = true;
            } else {
                l.append('<div class="blank"></div>');
                r++;
            }
            }
            for (var c = 0; c < 42 - r; c++) {
            if (c >= e.length) {
                l.append('<div class="blank"></div>');
            } else {
                var v = e[c].day;
                var  date1 = t+"/"+n+"/"+v;
                var limit3month = twoDateDiffr(new Date(t, n - 1, v));
                var attr = 'weekday="'+e[c].weekday+'" day="'+v+'" month="'+ n +'" year="'+t+'" date="'+date1+'"';
                // console.log(jQuery.inArray( e[c].weekday, days) !== -1);
                var blockDate = jQuery.inArray( date1, blackOutDate) == -1;
                var allowDate = jQuery.inArray( e[c].weekday, days) !== -1;
                var ifActive = (gNew(new Date(t, n - 1, v)) && allowDate && limit3month && blockDate) ? "select-date" : "disabled";
                var m = g(new Date(t, n - 1, v)) ? '<div '+attr+' class="active '+ifActive+'">' : '<div '+attr+' class="'+ifActive+'">';
                l.append(m + "" + v + "</div>");
            }
            }
            var y = o[n - 1];
            a.css("background-color", y)
            .find("h1")
            .text(i[n - 1] + " " + t);
            f.find("div").css("color", y);
            // l.find(".active").css("background-color", y);
            // l.find(".active").css("background-color", '#191970');
            d();
        }
        function twoDateDiffr(start){
            var end= new Date();
            tempDays = (start - end) / (1000 * 60 * 60 * 24);
            return (monthLimit) ? Math.round(tempDays) < monthLimit : true;
            // if (sectionLimit == 1) {
            //     return Math.round(tempDays) < 31;
            // } else if (sectionLimit == 4) {
            //     return Math.round(tempDays) < 121;
            // } else if (sectionLimit == 12) {
            //     return Math.round(tempDays) < 181;
            // } else if (sectionLimit == 24) {
            //     return Math.round(tempDays) < 365;
            // } else{
            //     return Math.round(tempDays) < 91;
            // }
        }
        function h() {
            var e = [];
            for (var r = 1; r < v(t, n) + 1; r++) {
            e.push({ day: r, weekday: s[m(t, n, r)] });
            }
            return e;
        }
        function p() {
            f.empty();
            for (var e = 0; e < 7; e++) {
            f.append("<div>" + s[e].substring(0, 3) + "</div>");
            }
        }
        function d() {
            var t;
            var n = $("#calendar").css("width", e + "px");
            n.find((t = "#calendar_weekdays, #calendar_content"))
            .css("width", e + "px")
            .find("div")
            .css({
                width: e / 7 + "px",
                height: e / 7 + "px",
                "line-height": e / 7 + "px"
            });
            n.find("#calendar_header")
            .css({ height: e * (1 / 5) + "px" })
            .find('i[class^="icon-chevron"]')
            .css("line-height", e * (1 / 7) + "px");
        }
        function v(e, t) {
            return new Date(e, t, 0).getDate();
        }
        function m(e, t, n) {
            return new Date(e, t - 1, n).getDay();
        }
        function g(e) {
            return y(new Date()) == y(e);
        }
        function gNew(e) {
            // console.log(y(new Date()), y(e))
            // console.log(    new Date(y(new Date())).getTime() , e.getTime() )
            return new Date(y(new Date())).getTime() < e.getTime();
            // return y(new Date()) > y(e);
        }
        function y(e) {
            return e.getFullYear() + "/" + (e.getMonth() + 1) + "/" + e.getDate();
        }
        function b() {
            var e = new Date();
            t = e.getFullYear();
            n = e.getMonth() + 1;
        }
        var e = 480;
        var e = 350;
        var t = 2013;
        var n = 9;
        var r = [];
        var i = [
            "JANUARY",
            "FEBRUARY",
            "MARCH",
            "APRIL",
            "MAY",
            "JUNE",
            "JULY",
            "AUGUST",
            "SEPTEMBER",
            "OCTOBER",
            "NOVEMBER",
            "DECEMBER"
        ];
        var s = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
        ];
        var o = [
            "#191970",
            "#006d5b",
            // "#16a085",
            // "#1abc9c",
            // "#c0392b",
            // "#27ae60",
            // "#FF6860",
            // "#f39c12",
            // "#f1c40f",
            // "#e67e22",
            // "#2ecc71",
            // "#e74c3c",
            // "#d35400",
            // "#2c3e50"
        ];
        var u = $("#calendar");
        var a = u.find("#calendar_header");
        var f = u.find("#calendar_weekdays");
        var l = u.find("#calendar_content");
        b();
        c();
        a.find('i[class^="icon-chevron"]').on("click", function () {
            var e = $(this);
            var r = function (e) {
            n = e == "next" ? n + 1 : n - 1;
            if (n < 1) {
                n = 12;
                t--;
            } else if (n > 12) {
                n = 1;
                t++;
            }
            c();
            };
            if (e.attr("class").indexOf("left") != -1) {
            r("previous");
            } else {
            r("next");
            }
        });
    });

</script>





@if ($session->price_per_session != 0)
    <script src="https://js.stripe.com/v3/"></script>
@endif
<script>

    (function($){
        $(document).ready(function(){

            @if ($session->price_per_session != 0)

                try {

                    var stripe = Stripe('pk_test_51MAZe4HnBjRuAp0ig9aBTPdENBP8OcVtiSIOHXv9EYDFVg2fuyq8nYs15fHWsQ3TWTnJ9sYvdHp65n8m6ZzvckIK00LDa3LnBE');
                    var elements = stripe.elements();

                    var style = {
                    base: {
                        color: '#32325d',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                        color: '#757575'
                        },
                        ':-webkit-autofill': {
                        color: '#32325d',
                        },
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a',
                        ':-webkit-autofill': {
                        color: '#fa755a',
                        },
                    }
                    };

                    var cardNumberElement = elements.create('cardNumber', {
                        style: style,
                        placeholder: 'Enter card number',
                    });
                    cardNumberElement.mount('#card-number-element');


                    var cardExpiryElement = elements.create('cardExpiry', {
                        style: style,
                        placeholder: 'Expiry date',
                    });
                    cardExpiryElement.mount('#card-expiry-element');


                    var cardCvcElement = elements.create('cardCvc', {
                        style: style,
                        placeholder: 'CVC number',
                    });
                    cardCvcElement.mount('#card-cvc-element');


                } catch (error) {
                    // console.log(error);
                }

                function setOutcome(result) {
                    var successElement = document.querySelector('.success');
                    var errorElement = document.querySelector('.error');
                    successElement.classList.remove('visible');
                    errorElement.classList.remove('visible');

                    if (result.token) {
                        // In this example, we're simply displaying the token
                        successElement.querySelector('.token').textContent = result.token.id;
                        successElement.classList.add('visible');

                        $('input[name=token]').val(result.token.id);
                        success_init();

                        // In a real integration, you'd submit the form with the token to your backend server
                        //var form = document.querySelector('form');
                        //form.querySelector('input[name="token"]').setAttribute('value', result.token.id);
                        //form.submit();

                    } else if (result.error) {
                        errorElement.textContent = result.error.message;
                        errorElement.classList.add('visible');
                    }
                }

                cardNumberElement.on('change', function(event) {
                    setOutcome(event);
                });

            @endif

            $('#msform').submit(function(e){
                e.preventDefault();

                if($(this).hasClass('free')){
                    success_init();
                    return false;
                }

                var postal_code_value = $('#postal-code').val();

                var options = {
                    address_zip: postal_code_value,
                };

                stripe.createToken(cardNumberElement, options).then(setOutcome);
            });

            function success_init(){

                var stripeForm = $('#msform').serialize();
                // let stripeForm = new FormData($('form.msform')[0]);
                let url = $('#msform').attr('action');
                $('#msform').find('button[type="submit"]').append('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
                $('#msform').find('button[type="submit"]').prop('disabled',true);

                $.ajax({
                    type: 'post',
                    url: url,
                    data: stripeForm,
                    dataType : 'json',
                    success: function (response) {
                        $('#msform').find('button[type="submit"]').prop('disabled',false);
                        $(".fa-spinner").remove();
                        console.log(response);

                        if(!response.status){

                            swal({
                                title: "Error!",
                                text: response.message,
                                icon: "warning",
                                button: "Close",
                            });
                        }
                        else{
                            if (response.auto_redirect) {window.location.href = response.redirect_url;}
                            else{
                                swal({
                                    //title: "Good job!",
                                    text: response.message,
                                    icon: "success",
                                    button: "Close",
                                }).then((willDelete) => {
                                    window.location.href = "{{ route('session-list') }}";
                                });
                            }
                        }
                    },
                    error : function(errorThrown){
                    console.log(errorThrown);
                    }
                });
            }
        })

    })($);
</script>

@endsection
