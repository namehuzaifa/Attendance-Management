@extends('layouts.master')
@section('title',($isEdit) ? "Edit Session" : 'Add Session'.' | '.config('app.name'))
@section('style')
<link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    {{-- <link rel="stylesheet" href="{{ asset('/front') }}/css/font-awesome.min.css"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    <style>

        div#card-number-element, div#card-expiry-element, div#card-cvc-element {
            display: block;
            width: 100%;
            padding: 0.571rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.45;
            color: #6e6b7b;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d8d6de;
            appearance: none;
            border-radius: 0.357rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        div#card-number-element:focus, div#card-expiry-element:focus, div#card-cvc-element:focus {
            color: #6e6b7b;
            background-color: #fff;
            border-color: #7367f0;
            outline: 0;
            box-shadow: 0 3px 10px 0 rgb(34 41 47 / 10%);
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
            /* border-top: 1px solid #d2d0d0; */
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
    </style>
@endsection
@section('content')

    <!-- BEGIN: Content-->

    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0"> {{ ($isEdit) ? "Edit Session" : 'Add Session' }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <!-- Basic Vertical form layout section start -->

                <section id="basic-vertical-layouts">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <form id="msform" class="form form-vertical {{ ($sessions?->first()->price_per_session) ? '':'free' }}" method="POST" action="{{ ($isEdit) ?  route('session-update', $id) : route('session-create',"") }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $id }}">
                                        <div class="row">
                                            <div class="row col-md-4  col-12">
                                                <div class="col-md-12" id="calendar">
                                                    <div id="calendar_header">
                                                        <i class="icon-chevron-left fa fa-angle-left"></i>
                                                        <h1></h1>
                                                        <i class="icon-chevron-right fa fa-angle-right"></i>
                                                    </div>
                                                    <div id="calendar_weekdays"></div>
                                                    <div id="calendar_content"></div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="row" id="session">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row col-md-8  col-12" style="height: fit-content;">
                                                <div class="col-md-6  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="first-name-vertical" >Select Session</label>
                                                        <select required  id="select-session" class="form-control">
                                                            @foreach ($sessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->title }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="first-name-vertical" >Name</label>
                                                        <input type="text" id="first-name-vertical" class="form-control" value="{{ ($isEdit) ? $session?->name : old('name')  }}" name="name" placeholder="First Name" />
                                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>


                                                <div class="col-md-6  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="email" >Email</label>
                                                        <input type="email" id="email" class="form-control" value="{{ ($isEdit) ? $session?->email : old('email')  }}" name="email" placeholder="Email" />
                                                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="phone" >Phone</label>
                                                        <input type="tel" id="phone" class="form-control" value="{{ ($isEdit) ? $session?->phone : old('phone')  }}" name="phone" placeholder="Phone" />
                                                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="objective" >Objective</label>
                                                        <input type="tel" id="objective" class="form-control" value="{{ ($isEdit) ? $session?->objective : old('objective')  }}" name="objective" placeholder="Objective" />
                                                        @error('objective')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="row col-12 {{ ($sessions?->first()->price_per_session) ? '':'d-none' }}" id="account-detail">

                                                    <div class="col-12">
                                                        <h2 class="fs-title"> Account Information </h2>
                                                    </div>
                                                    <div class="col-sm-12 mb-2">
                                                        <label class="form-label"> Card Holder Name </label>
                                                        <input type="text" name="card_holder_name" placeholder="Name" class="form-control" />
                                                    </div>

                                                    <div class="col-md-6  col-12">
                                                        <label class="form-label">Card number</label>
                                                        <div  style="width: 100%;" id="card-number-element" class="field"></div>
                                                    </div>
                                                    <div class="col-md-3  col-12">
                                                        <label class="form-label">Expiry date</label>
                                                        <div style="width: 100%;" id="card-expiry-element" class="field"></div>
                                                    </div>
                                                    <div class="col-md-3  col-12">
                                                        <label class="form-label">CVC</label>
                                                        <div style="width: 100%;" id="card-cvc-element" class="field"></div>
                                                    </div>

                                                    <div class="outcome">
                                                        <input type="hidden" name="token" value="">
                                                        <div style="color: red" class="error"></div>
                                                        <div class="success" style="display: none;"> Success! Your Stripe token is <span class="token"></span></div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12  col-12 mt-5">
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
                                            </div>

                                            {{-- <div class="col-8" >
                                                <div class="row" id="session">
                                                    @foreach ($slots as $slot)

                                                        @if (in_array($slot, $booked) && $session->start_end_time != $slot)
                                                            <label class="booked col-2" for="{{ $slot }}">{{ $slot }}</label>
                                                            @continue
                                                        @endif

                                                        <label class="slots col-2 {{ ($session->start_end_time == $slot) ? 'active' : '' }}" for="{{ $slot }}">{{ $slot }}
                                                            <input id="{{ $slot }}" type="radio" {{ ($session->start_end_time == $slot) ? 'checked' : '' }} style="display:none;" name="slot" value="{{ $slot }}">
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @error('date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<br/>
                                                @error('slot')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div> --}}

                                            <div class="col-12 mt-5">
                                                <button type="submit" class="btn btn-primary me-1">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- END: Content-->
@endsection

@section('scripts')

    {{-- @if ($session->price_per_session != 0) --}}
        <script src="https://js.stripe.com/v3/"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    {{-- @endif --}}

    <script>

        var days = {!! $sessions?->first()->getslots->pluck('days')->toJson() !!};
        var days = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
        var blackOutDate = {!! json_encode(explode(",",str_replace(" ", "",$sessions?->first()?->blackout_dates))) !!};
        var sectionLimit = {{ $sessions?->first()?->session_limit }};
        var monthLimit = {{ $sessions?->first()?->month_limit }}*30;
        var sessionId   =   "{{ $sessions?->first()->id }}"
        var slug   =   "{{ $sessions?->first()->slug }}"
        var date = "";


        // $(document).on( 'click', '.slots', function(e){
        //     var $this = $(this);
        //     var time = $this.text();
        //     $('.slots').removeClass('active');
        //     $this.addClass('active');
        //     $('input[name=date]').val(date);
        //     $('#displayDate').text(date);
        // })

        // $(document).on( 'click', '.select-date', function(e){
        //     $('#calendar').find('.active').removeClass('active');
        //     $(this).addClass('active');

        //         date        =   $(this).attr('date');
        //     var weekday     =   $(this).attr('weekday');

        //     $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        //     url = "{{ route('get-slots') }}"

        //     $.ajax({
        //         url: url,
        //         type: "POST",
        //         data: {date : date, id : sessionId, weekday : weekday},
        //     }).done(function (data) {

        //         var html = '';
        //         $.each(data.slots, function(key,val){
        //             if(jQuery.inArray(val, data.booked) != -1) {
        //                 html += `<label class="booked col-2" for="`+val+`">`+val+`</label>`;
        //             } else {
        //                 html += `<label class="slots col-2" for="`+val+`">`+val+`
        //                             <input id="`+val+`" type="radio" style="display:none;" name="slot" value="`+val+`">
        //                         </label>`;
        //             }
        //         });
        //         // console.log(data);
        //         $('#session').html(html);
        //     });
        // })

        $(document).on( 'change', '#select-session', function(e){
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            url = "{{ route('coaching-detail') }}"
            var id = $(this).val();
            $.ajax({
                url: url,
                type: "POST",
                data: {id : id},
            }).done(function (data) {
                console.log(data);
                days         = data.days;;
                blackOutDate = data.block_date;
                sectionLimit = data.session.session_limit;
                monthLimit   = data.session.month_limit*30;
                sessionId    =   id;
                slug            =    data.session.slug;

                if(data.session.price_per_session == 0){
                    $('#msform').addClass('free');
                    $('#account-detail').addClass('d-none');
                } else {
                    $('#msform').removeClass('free')
                    $('#account-detail').removeClass('d-none')
                }
                // calender_set()
                $('#session').html('')
            });
        })

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
            var sessionId   =   $('#select-session').val();
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



        $(document).ready(function(){


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
            let url = '{{ route('session-create') }}/'+slug;
            console.log(url);
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
    </script>



@endsection

