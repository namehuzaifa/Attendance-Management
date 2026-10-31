@php
    $user      = auth()->user();
    $firstName = explode(' ', trim($user->full_name))[0] ?? $user->full_name;
    $hour      = (int) now()->format('H');
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

{{-- ------------------------------------------------------------ punch card --}}
<div class="row">
    <div class="col-12">
        <div class="card hrms-hero mb-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-12 mb-2 mb-lg-0">
                        <p class="hrms-hero__greeting">{{ $greeting }}, {{ $firstName }} 👋</p>
                        <p class="hrms-hero__sub" id="hrmsDate">{{ now()->format('l, j F Y') }}</p>

                        <div class="hrms-clock mt-2" id="hrmsClock">{{ now()->format('h:i:s') }} <small>{{ now()->format('A') }}</small></div>

                        @if ($window)
                            <p class="hrms-hero__sub mt-1 mb-0">
                                <i data-feather="clock" class="me-25" style="width:14px;height:14px"></i>
                                Shift {{ $window['start']->format('h:i A') }} – {{ $window['end']->format('h:i A') }}
                                @if ($window['grace'] > 0)
                                    · {{ $window['grace'] }} min grace
                                @endif
                            </p>
                        @else
                            <p class="hrms-hero__sub mt-1 mb-0">
                                <i data-feather="alert-circle" class="me-25" style="width:14px;height:14px"></i>
                                No shift assigned — ask your administrator to set one.
                            </p>
                        @endif
                    </div>

                    <div class="col-lg-4 col-12 mb-2 mb-lg-0">
                        <div class="hrms-punch-meta">
                            <div class="hrms-punch-meta__item">
                                <div class="label">Check in</div>
                                <div class="value">
                                    {{ $todayRecord?->check_in ? \Carbon\Carbon::parse($todayRecord->check_in)->format('h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="hrms-punch-meta__item">
                                <div class="label">Check out</div>
                                <div class="value">
                                    {{ $todayRecord?->check_out ? \Carbon\Carbon::parse($todayRecord->check_out)->format('h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="hrms-punch-meta__item">
                                <div class="label">Worked</div>
                                <div class="value">{{ $todayRecord?->worked_hours ?? '—' }}</div>
                            </div>
                        </div>

                        @if ($todayRecord && $todayRecord->check_in)
                            <div class="mt-1">
                                <span class="hrms-chip" style="background:rgba(255,255,255,.2);color:#fff">
                                    {{ $todayRecord->status_label }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-3 col-12 text-lg-end">
                        @if ($canCheckIn)
                            <form method="POST" action="{{ route('attendance.checkin') }}" data-geo-punch>
                                @csrf
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <button type="submit" class="hrms-punch-btn">
                                    <i data-feather="log-in" class="me-50"></i> Check In
                                </button>
                            </form>
                        @elseif ($canCheckOut)
                            <form method="POST" action="{{ route('attendance.checkout') }}" data-geo-punch
                                  onsubmit="return confirm('Check out now?')">
                                @csrf
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <button type="submit" class="hrms-punch-btn hrms-punch-btn--out">
                                    <i data-feather="log-out" class="me-50"></i> Check Out
                                </button>
                            </form>
                        @else
                            <button class="hrms-punch-btn" disabled>
                                <i data-feather="check-circle" class="me-50"></i> Day complete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ----------------------------------------------------------- stat tiles --}}
<div class="row match-height">
    @php
        $tiles = [
            ['label' => 'Present',    'value' => $summary['present'],    'icon' => 'check-circle', 'tint' => 'success'],
            ['label' => 'Absent',     'value' => $summary['absent'],     'icon' => 'x-circle',     'tint' => 'danger'],
            ['label' => 'Late',       'value' => $summary['late'],       'icon' => 'alert-circle', 'tint' => 'warning'],
            ['label' => 'On leave',   'value' => $summary['leave'],      'icon' => 'coffee',       'tint' => 'primary'],
        ];
    @endphp

    @foreach ($tiles as $tile)
        <div class="col-lg-3 col-sm-6 col-6 mb-2">
            <div class="hrms-stat">
                <div class="hrms-stat__body">
                    <div class="hrms-stat__icon hrms-tint-{{ $tile['tint'] }}">
                        <i data-feather="{{ $tile['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="hrms-stat__value">{{ $tile['value'] }}</div>
                        <p class="hrms-stat__label">{{ $tile['label'] }} this month</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row match-height">
    {{-- ------------------------------------------------------ month gauge --}}
    <div class="col-lg-4 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-0">
                <h4 class="card-title">{{ now()->format('F Y') }}</h4>
            </div>
            <div class="card-body text-center">
                <div class="hrms-ring" style="--value: {{ $summary['attendance_pct'] }}">
                    <div class="hrms-ring__inner">
                        <div>
                            <div class="hrms-ring__value">{{ $summary['attendance_pct'] }}%</div>
                            <div class="hrms-ring__label">Attendance</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2 text-start">
                    <div class="col-6 mb-1">
                        <small class="text-muted d-block">Working days</small>
                        <strong>{{ $summary['working_days'] }}</strong>
                    </div>
                    <div class="col-6 mb-1">
                        <small class="text-muted d-block">Hours worked</small>
                        <strong>{{ $service->humanMinutes($summary['worked_minutes']) }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Early out</small>
                        <strong>{{ $summary['early_out'] }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Short hours</small>
                        <strong>{{ $summary['short_hour'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------- recent activity --}}
    <div class="col-lg-8 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">Recent activity</h4>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body">
                @forelse ($recent as $day)
                    <div class="hrms-timeline__row">
                        <span class="hrms-timeline__dot bg-{{ $day['tone'] }}"></span>
                        <span class="hrms-timeline__date">
                            {{ $day['is_today'] ? 'Today' : \Carbon\Carbon::parse($day['date'])->format('D, d M') }}
                        </span>
                        <span class="hrms-chip hrms-tint-{{ $day['tone'] }}">{{ $day['status'] }}</span>
                        <span class="hrms-timeline__times">
                            @if ($day['check_in'])
                                {{ $day['check_in'] }} → {{ $day['check_out'] ?? '—' }}
                                <span class="text-muted ms-50">{{ $day['worked'] ?? '' }}</span>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="hrms-empty">
                        <i data-feather="calendar"></i>
                        <p class="mb-0">No attendance recorded yet this month.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row match-height">
    {{-- --------------------------------------------------------- next week --}}
    <div class="col-lg-8 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">Next 7 days</h4>
            </div>
            <div class="card-body">
                <div class="hrms-schedule">
                    @foreach ($schedule as $day)
                        <div class="hrms-schedule__day {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_off'] ? 'is-off' : '' }}">
                            <div class="hrms-schedule__dow">{{ \Illuminate\Support\Str::substr($day['day'], 0, 3) }}</div>
                            <div class="mb-50">{{ \Illuminate\Support\Str::after($day['date'], ', ') }}</div>
                            @if ($day['is_off'])
                                <div>{{ $day['label'] }}</div>
                            @else
                                <div>{{ $day['start'] }}</div>
                                <div>{{ $day['end'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------- leave + holidays --}}
    <div class="col-lg-4 col-12 mb-2">
        <div class="card hrms-card mb-2">
            <div class="card-header pb-50">
                <h4 class="card-title">Leave balance</h4>
                <a href="{{ route('leave.index') }}" class="btn btn-sm btn-primary">Apply</a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-end mb-50">
                    <div>
                        <span class="h2 fw-bolder">{{ $leaveBalance['remaining'] }}</span>
                        <span class="text-muted">/ {{ $leaveBalance['quota'] }} days left</span>
                    </div>
                    @if ($pendingLeaves > 0)
                        <span class="hrms-chip hrms-tint-warning">{{ $pendingLeaves }} pending</span>
                    @endif
                </div>
                <div class="hrms-progress">
                    <div class="hrms-progress__bar"
                         style="width: {{ $leaveBalance['quota'] > 0 ? min(100, round(($leaveBalance['used'] / $leaveBalance['quota']) * 100)) : 0 }}%"></div>
                </div>
                <small class="text-muted d-block mt-50">{{ $leaveBalance['used'] }} day(s) used this year</small>
            </div>
        </div>

        <div class="card hrms-card mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">Upcoming holidays</h4>
            </div>
            <div class="card-body">
                @forelse ($upcomingHolidays as $holiday)
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <div class="hrms-stat__icon hrms-tint-info" style="width:38px;height:38px;flex:0 0 38px">
                            <i data-feather="gift"></i>
                        </div>
                        <div>
                            <div class="fw-bolder">{{ $holiday->title }}</div>
                            <small class="text-muted">{{ $holiday->date->format('D, d M Y') }}</small>
                        </div>
                    </div>
                @empty
                    <div class="hrms-empty py-1">
                        <p class="mb-0">No holidays scheduled.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
