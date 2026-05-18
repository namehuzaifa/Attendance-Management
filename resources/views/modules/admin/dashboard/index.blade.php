@extends('layouts.master')
@section('title', 'Dashboard')
@section('content')


    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Dashboard Ecommerce Starts -->

                @if (auth()->user()->user_role == "admin")

                    <section id="dashboard-ecommerce">
                        <div class="row match-height">
                            <!-- Medal Card -->
                            <div class="col-xl-4 col-md-6 col-12">
                                <div class="card card-congratulation-medal">
                                    <div class="card-body">
                                        <h5>Congratulations 🎉 {{ auth()->user()->name }}</h5>
                                        <p class="card-text font-small-3">Your Total Earnings</p>
                                        <h3 class="mb-75 mt-2 pt-50">
                                            <a href="#">${{ $totalearning ?? '' }}</a>
                                        </h3>
                                        {{-- <button type="button" class="btn btn-primary">View Sales</button> --}}
                                        <img src="{{ asset('/') }}app-assets/images/illustration/badge.svg" class="congratulation-medal" alt="Medal Pic" />
                                    </div>
                                </div>
                            </div>
                            <!--/ Medal Card -->

                            <!-- Statistics Card -->
                            <div class="col-xl-8 col-md-6 col-12">
                                <div class="card card-statistics">
                                    <div class="card-header">
                                        <h4 class="card-title">Statistics</h4>
                                        {{-- <div class="d-flex align-items-center">
                                            <p class="card-text font-small-2 me-25 mb-0">Updated 1 month ago</p>
                                        </div> --}}
                                    </div>
                                    <div class="card-body statistics-body">
                                        <div class="row">
                                            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
                                                <div class="d-flex flex-row">
                                                    <div class="avatar bg-light-primary me-2">
                                                        <div class="avatar-content">
                                                            <i data-feather="trending-up" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="my-auto">
                                                        <h4 class="fw-bolder mb-0">{{ $user ?? '' }}</h4>
                                                        <p class="card-text font-small-3 mb-0">Users</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
                                                <div class="d-flex flex-row">
                                                    <div class="avatar bg-light-info me-2">
                                                        <div class="avatar-content">
                                                            <i data-feather="airplay" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="my-auto">
                                                        <h4 class="fw-bolder mb-0">{{ $courses ?? '' }}</h4>
                                                        <p class="card-text font-small-3 mb-0">Courses</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-sm-0">
                                                <div class="d-flex flex-row">
                                                    <div class="avatar bg-light-danger me-2">
                                                        <div class="avatar-content">
                                                            <i data-feather="message-circle" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="my-auto">
                                                        <h4 class="fw-bolder mb-0">{{ $blog  ?? '' }}</h4>
                                                        <p class="card-text font-small-3 mb-0">Blogs</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-sm-6 col-12">
                                                <div class="d-flex flex-row">
                                                    <div class="avatar bg-light-success me-2">
                                                        <div class="avatar-content">
                                                            <i data-feather="mic" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="my-auto">
                                                        <h4 class="fw-bolder mb-0">{{ $poadcast  ?? '' }}</h4>
                                                        <p class="card-text font-small-3 mb-0">Podcasts</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--/ Statistics Card -->
                        </div>

                        <div class="row match-height">
                            <div class="col-lg-4 col-12">
                                <div class="row match-height">
                                    <!-- Bar Chart - Orders -->
                                    <div class="col-lg-6 col-md-3 col-6">
                                        <div class="card">
                                            <div class="card-body pb-50">
                                                <h6>Total Sessions</h6>
                                                <h2 class="fw-bolder mb-1">{{ $session  ?? '' }}</h2>
                                                <div id="statistics-order-chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ Bar Chart - Orders -->

                                    <!-- Line Chart - Profit -->
                                    <div class="col-lg-6 col-md-3 col-6">
                                        <div class="card card-tiny-line-stats">
                                            <div class="card-body pb-50">
                                                <h6>Booking Sessions</h6>
                                                <h2 class="fw-bolder mb-1">{{ $sessionBooking  ?? '' }}</h2>
                                                <div id="statistics-booking-chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ Line Chart - Profit -->

                                    <!-- Earnings Card -->
                                    <div class="col-lg-12 col-md-6 col-12">
                                        <div class="card earnings-card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h4 class="card-title mb-1">Earnings</h4>
                                                        <div class="font-small-2">This Month</div>
                                                        <h5 class="mb-1">${{ $curentMonthEarning  ?? '' }}</h5>
                                                        <p class="card-text text-muted font-small-2">
                                                            <span class="fw-bolder">${{ $lastMonthEarning   ?? ''}}</span><span> Earnings last month.</span>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <div id="earnings-chart"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ Earnings Card -->
                                </div>
                            </div>

                            <!-- Revenue Report Card -->
                            <div class="col-lg-8 col-12">
                                <div class="card card-revenue-budget">
                                    <div class="row mx-0">
                                        <div class="col-md-12 col-12 revenue-report-wrapper">
                                            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                                                <h4 class="card-title mb-50 mb-sm-0">Bookings Report</h4>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex align-items-center me-2">
                                                        <span class="bullet bullet-primary font-small-3 me-50 cursor-pointer"></span>
                                                        <span>Bookings</span>
                                                    </div>
                                                    <div class="d-flex align-items-center ms-75">
                                                        <span class="bullet bullet-warning font-small-3 me-50 cursor-pointer"></span>
                                                        <span>Earnings</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="booking-chart"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!--/ Revenue Report Card -->
                        </div>
                    </section>

                @endif

                @if (auth()->user()->user_role == "user")

                <style>
                    .transaction-item {
                        width: 33%;
                    }

                    table.table.table-bordered td {
                        padding: 12px;
                        text-align: center;
                        vertical-align: middle;
                    }

                    .card-transaction .transaction-item {
                        margin-bottom: 1.5rem;
                    }

                    .badge-on-time { background-color: #28c76f; color: #fff; }
                    .badge-late { background-color: #ff9f43; color: #fff; }
                    .badge-absent { background-color: #ea5455; color: #fff; }
                    .badge-off-day { background-color: #00cfe8; color: #fff; }
                    .badge-early-out { background-color: #ff6f61; color: #fff; }
                    .badge-short-hour { background-color: #9c27b0; color: #fff; }

                    .status-badge {
                        padding: 4px 10px;
                        border-radius: 4px;
                        font-size: 0.75rem;
                        font-weight: 600;
                        text-transform: capitalize;
                        display: inline-block;
                    }

                    .stats-value {
                        font-size: 1.3rem;
                        font-weight: 700;
                    }

                    /* Mobile responsiveness */
                    @media (max-width: 768px) {
                        .transaction-item {
                            width: 50% !important;
                        }
                        .col-lg-4.col-4,
                        .col-lg-5.col-5,
                        .col-lg-3.col-3 {
                            width: 100% !important;
                            flex: 0 0 100% !important;
                            max-width: 100% !important;
                        }
                        .col-xl-6.col-md-6.col-6 {
                            width: 100% !important;
                            flex: 0 0 100% !important;
                            max-width: 100% !important;
                        }
                        table.table td, table.table th {
                            padding: 8px 6px;
                            font-size: 0.8rem;
                        }
                    }
                    @media (max-width: 480px) {
                        .transaction-item {
                            width: 100% !important;
                        }
                    }
                </style>
                    <section id="dashboard-ecommerce">
                        <div class="row match-height">
                            <!-- Info Card -->
                            <div class="col-xl-6 col-md-6 col-12">
                                <div class="card card-transaction">
                                   <div class="card-body" style="display: flex; flex-wrap: wrap; margin-top: 10px;">
                                        <div class="transaction-item">
                                            <div class="d-flex">
                                                <div class="avatar bg-light-primary rounded float-start">
                                                    <div class="avatar-content">
                                                        <i data-feather='clipboard' class="font-medium-3"></i>
                                                    </div>
                                                </div>
                                                <div class="transaction-percentage">
                                                    <h6 class="transaction-title">Employee Code</h6>
                                                    <small>{{ auth()->user()->id }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="transaction-item">
                                            <div class="d-flex">
                                                <div class="avatar bg-light-success rounded float-start">
                                                    <div class="avatar-content">
                                                        <i data-feather='user' class="font-medium-3"></i>
                                                    </div>
                                                </div>
                                                <div class="transaction-percentage">
                                                    <h6 class="transaction-title">Name</h6>
                                                    <small>{{ auth()->user()->full_name }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="transaction-item">
                                            <div class="d-flex">
                                                <div class="avatar bg-light-danger rounded float-start">
                                                    <div class="avatar-content">
                                                        <i data-feather='credit-card' class="font-medium-3"></i>
                                                    </div>
                                                </div>
                                                <div class="transaction-percentage">
                                                    <h6 class="transaction-title">Designation</h6>
                                                    <small>{{ auth()->user()?->relation?->designation?->name }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="transaction-item">
                                            <div class="d-flex">
                                                <div class="avatar bg-light-warning rounded float-start">
                                                    <div class="avatar-content">
                                                        <i data-feather='home' class="font-medium-3"></i>
                                                    </div>
                                                </div>
                                                <div class="transaction-percentage">
                                                    <h6 class="transaction-title">Department</h6>
                                                    <small>{{ auth()->user()?->relation?->department?->name }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="transaction-item">
                                            <div class="d-flex">
                                                <div class="avatar bg-light-info rounded float-start">
                                                    <div class="avatar-content">
                                                        <i data-feather='mail' class="font-medium-3"></i>
                                                    </div>
                                                </div>
                                                <div class="transaction-percentage">
                                                    <h6 class="transaction-title">Email</h6>
                                                    <small>{{ auth()->user()->email }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-6 col-12">
                                <div class="card earnings-card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <h4 class="card-title mb-1">Your Shift timing</h4>
                                                <h5 class="mb-1">
                                                    {{ Auth::user()->relation->shiftTiming->start_time->format('h:i a') }} -
                                                    {{ Auth::user()->relation->shiftTiming->end_time->format('h:i a') }}
                                                </h5>
                                            </div>
                                            <div class="col-6">
                                               @if($checkInAvailable && !$attendanceToday)
                                                    <form action="{{ route('attendance.checkin') }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-primary waves-effect waves-float waves-light">Check In</button>
                                                    </form>

                                                @elseif($attendanceToday?->check_in)
                                                    <div class="font-small-4">Check In today</div>
                                                    <h5 class="mb-1"> {{ $attendanceToday?->check_in?->format('h:i:s a') }}</h5>
                                                @endif

                                                @if($checkOutAvailable && $attendanceToday && !$attendanceToday->check_out)
                                                    <form action="{{ route('attendance.checkout') }}" method="POST">
                                                            @csrf
                                                        <button class="btn btn-primary waves-effect waves-float waves-light">Check Out</button>
                                                    </form>

                                                @elseif($attendanceToday?->check_out)
                                                    <div class="font-small-4">Check Out today</div>
                                                    <h5 class="mb-1"> {{ $attendanceToday?->check_out?->format('h:i:s a') }}</h5>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row match-height">
                            <!-- Shift Information: Next 7 days -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Shift Information <small class="text-muted">(Next 7 Days)</small></h4>
                                    </div>
                                    <div class="card-content collapse show">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Timing</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($nextWeek as $day)
                                                        <tr>
                                                            <td>{{ $day['date'] }}</td>
                                                            <td>
                                                                @if($day['is_off'])
                                                                    <span class="status-badge badge-off-day">Off Day</span>
                                                                @else
                                                                    {{ $day['start_time'] }} - {{ $day['end_time'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- In Out Timing: Current Week -->
                            <div class="col-lg-5 col-md-6 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">In Out Timing <small class="text-muted">(Last 7 Days)</small></h4>
                                    </div>
                                    <div class="card-content collapse show">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>In</th>
                                                            <th>Out</th>
                                                            <th>Worked</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($weekReport as $entry)
                                                        <tr>
                                                            <td>{{ $entry['date'] }}</td>
                                                            <td>
                                                                @php
                                                                    $statusLower = strtolower($entry['status']);
                                                                    $badgeClass = 'badge-on-time';
                                                                    if(str_contains($statusLower, 'late')) $badgeClass = 'badge-late';
                                                                    elseif(str_contains($statusLower, 'absent')) $badgeClass = 'badge-absent';
                                                                    elseif(str_contains($statusLower, 'off day')) $badgeClass = 'badge-off-day';
                                                                    elseif(str_contains($statusLower, 'early out')) $badgeClass = 'badge-early-out';
                                                                    elseif(str_contains($statusLower, 'short hour')) $badgeClass = 'badge-short-hour';
                                                                @endphp
                                                                <span class="status-badge {{ $badgeClass }}">{{ $entry['status'] }}</span>
                                                            </td>
                                                            <td>{{ $entry['check_in'] ?? '-' }}</td>
                                                            <td>{{ $entry['check_out'] ?? '-' }}</td>
                                                            <td>{{ $entry['worked_hours'] ?? '-' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Employee Stats: Current Month -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Employee Stats <small class="text-muted">({{ now()->format('F Y') }})</small></h4>
                                    </div>
                                    <div class="card-content collapse show">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Details</th>
                                                            <th>Count</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Total Working Days</td>
                                                            <td><span class="stats-value">{{ $employeeStats['total_working_days'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="text-success fw-bold">Presents</span></td>
                                                            <td><span class="stats-value text-success">{{ $employeeStats['present'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="text-info fw-bold">Off Days</span></td>
                                                            <td><span class="stats-value text-info">{{ $employeeStats['off_days'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="text-danger fw-bold">Absent</span></td>
                                                            <td><span class="stats-value text-danger">{{ $employeeStats['absent'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span style="color: #ff9f43; font-weight: bold;">Late In</span></td>
                                                            <td><span class="stats-value" style="color: #ff9f43;">{{ $employeeStats['late'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span style="color: #ff6f61; font-weight: bold;">Early Out</span></td>
                                                            <td><span class="stats-value" style="color: #ff6f61;">{{ $employeeStats['early_out'] }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span style="color: #9c27b0; font-weight: bold;">Short Hour</span></td>
                                                            <td><span class="stats-value" style="color: #9c27b0;">{{ $employeeStats['short_hour'] }}</span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                @endif


                <!-- Dashboard Ecommerce ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
@endsection

@section('scripts')
    <script src="{{ asset('/') }}app-assets/vendors/js/charts/apexcharts.min.js"></script>
    {{-- <script src="{{ asset('/') }}app-assets/js/scripts/pages/dashboard-ecommerce.js"></script> --}}

    <script>


        var bookingChartData = {};
        var revenueChatData = {};

        var $bookingChart = document.querySelector('#booking-chart');
        var bookingChart;

        var $textMutedColor = '#b9b9c3';

       function graphData(booking, earning) {
            return {
                chart: {
                height: 230,
                stacked: true,
                type: 'bar',
                toolbar: { show: true }
                },
                plotOptions: {
                bar: {
                    columnWidth: '17%',
                    endingShape: 'rounded'
                },
                distributed: true
                },
                colors: [window.colors.solid.primary, window.colors.solid.warning],
                series: [
                {
                    name: 'Bookings',
                    data: booking
                },
                {
                    name: 'Earnings',
                    data:  earning
                    // data: [-2, 3, -8, -10, 2, 5, 3, -10, 6, 2, 4, 7]
                }
                ],
                dataLabels: {
                enabled: false
                },
                legend: {
                show: false
                },
                grid: {
                padding: {
                    top: -20,
                    bottom: -10
                },
                yaxis: {
                    lines: { show: true }
                }
                },
                xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: {
                    style: {
                    colors: $textMutedColor,
                    fontSize: '0.86rem'
                    }
                },
                axisTicks: {
                    show: false
                },
                axisBorder: {
                    show: false
                }
                },
                yaxis: {
                labels: {
                    style: {
                    colors: $textMutedColor,
                    fontSize: '0.86rem'
                    }
                }
                }
            };
       }

        bookingChart = new ApexCharts($bookingChart, graphData(bookingChartData,revenueChatData));
        bookingChart.render();

    </script>

    <script>


        var $statisticsProfitChart = document.querySelector('#statistics-booking-chart');
        var statisticsProfitChartOptions;
        var statisticsProfitChart;
        var $trackBgColor = '#EBEBEB';


        statisticsProfitChartOptions = {
            chart: {
            height: 70,
            type: 'line',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
            },
            grid: {
            borderColor: $trackBgColor,
            strokeDashArray: 5,
            xaxis: {
                lines: {
                show: true
                }
            },
            yaxis: {
                lines: {
                show: false
                }
            },
            padding: {
                top: -30,
                bottom: -10
            }
            },
            stroke: {
            width: 3
            },
            colors: [window.colors.solid.info],
            series: [
            {
                data: bookingChartData
                // data: [200, 100, 20, 5, 30, 15, 45]
            }
            ],
            markers: {
            size: 2,
            colors: window.colors.solid.info,
            strokeColors: window.colors.solid.info,
            strokeWidth: 2,
            strokeOpacity: 1,
            strokeDashArray: 0,
            fillOpacity: 1,
            discrete: [
                {
                seriesIndex: 0,
                dataPointIndex: 5,
                fillColor: '#ffffff',
                strokeColor: window.colors.solid.info,
                size: 5
                }
            ],
            shape: 'circle',
            radius: 2,
            hover: {
                size: 3
            }
            },
            xaxis: {
            labels: {
                show: true,
                style: {
                fontSize: '0px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
            },
            yaxis: {
            show: false
            },
            tooltip: {
            x: {
                show: false
            }
            }
        };
        statisticsProfitChart = new ApexCharts($statisticsProfitChart, statisticsProfitChartOptions);
        statisticsProfitChart.render();
    </script>

    <script>
          var $statisticsOrderChart = document.querySelector('#statistics-order-chart');
          var statisticsOrderChartOptions;
          var statisticsOrderChart;
          var $barColor = '#f3f3f3';

                statisticsOrderChartOptions = {
            chart: {
            height: 70,
            type: 'bar',
            stacked: true,
            toolbar: {
                show: false
            }
            },
            grid: {
            show: false,
            padding: {
                left: 0,
                right: 0,
                top: -15,
                bottom: -15
            }
            },
            plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '20%',
                startingShape: 'rounded',
                colors: {
                backgroundBarColors: [$barColor, $barColor, $barColor, $barColor, $barColor],
                backgroundBarRadius: 5
                }
            }
            },
            legend: {
            show: false
            },
            dataLabels: {
            enabled: false
            },
            colors: [window.colors.solid.warning],
            series: [
            {
                name: '2020',
                data: [45, 85, 65, 45, 65]
            }
            ],
            xaxis: {
            labels: {
                show: false
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
            },
            yaxis: {
            show: false
            },
            tooltip: {
            x: {
                show: false
            }
            }
        };
        statisticsOrderChart = new ApexCharts($statisticsOrderChart, statisticsOrderChartOptions);
        statisticsOrderChart.render();


    </script>

    <script>

        var $earningsChart = document.querySelector('#earnings-chart');
        var $earningsStrokeColor2 = '#28c76f66';
        var $earningsStrokeColor3 = '#28c76f33';

                    //--------------- Earnings Chart ---------------
        //----------------------------------------------
        earningsChartOptions = {
            chart: {
            type: 'donut',
            height: 120,
            toolbar: {
                show: false
            }
            },
            dataLabels: {
            enabled: false
            },
            series: [53, 16, 31],
            legend: { show: false },
            comparedResult: [2, -3, 8],
            labels: ['App', 'Service', 'Product'],
            stroke: { width: 0 },
            colors: [$earningsStrokeColor2, $earningsStrokeColor3, window.colors.solid.success],
            grid: {
            padding: {
                right: -20,
                bottom: -8,
                left: -20
            }
            },
            plotOptions: {
            pie: {
                startAngle: -10,
                donut: {
                labels: {
                    show: true,
                    name: {
                    offsetY: 15
                    },
                    value: {
                    offsetY: -15,
                    formatter: function (val) {
                        return parseInt(val) + '%';
                    }
                    },
                    total: {
                    show: true,
                    offsetY: 15,
                    label: 'Booking',
                    formatter: function (w) {
                        return bookedSession;
                    }
                    }
                }
                }
            }
            },
            responsive: [
            {
                breakpoint: 1325,
                options: {
                chart: {
                    height: 100
                }
                }
            },
            {
                breakpoint: 1200,
                options: {
                chart: {
                    height: 120
                }
                }
            },
            {
                breakpoint: 1045,
                options: {
                chart: {
                    height: 100
                }
                }
            },
            {
                breakpoint: 992,
                options: {
                chart: {
                    height: 120
                }
                }
            }
            ]
        };
        earningsChart = new ApexCharts($earningsChart, earningsChartOptions);
        earningsChart.render();

    </script>
@endsection

