@php
    $firstName = explode(' ', trim(auth()->user()->full_name))[0] ?? auth()->user()->full_name;
    $hour      = (int) now()->format('H');
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        return strtoupper(substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
    };
@endphp

{{-- ------------------------------------------------------------- overview --}}
<div class="row">
    <div class="col-12">
        <div class="card hrms-hero mb-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-12 mb-2 mb-lg-0">
                        <p class="hrms-hero__greeting">{{ $greeting }}, {{ $firstName }} 👋</p>
                        <p class="hrms-hero__sub" id="hrmsDate">{{ now()->format('l, j F Y') }}</p>
                        <div class="hrms-clock mt-2" id="hrmsClock">{{ now()->format('h:i:s') }} <small>{{ now()->format('A') }}</small></div>

                        @if ($isHoliday)
                            <p class="hrms-hero__sub mt-1 mb-0">
                                <i data-feather="gift" class="me-25" style="width:14px;height:14px"></i>
                                Today is a holiday — {{ $isHoliday->title }}
                            </p>
                        @endif
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="hrms-clock" style="font-size:2rem">{{ $stats['present'] }}</div>
                                <div class="hrms-hero__sub">Checked in</div>
                            </div>
                            <div class="col-4">
                                <div class="hrms-clock" style="font-size:2rem">{{ $stats['on_leave'] }}</div>
                                <div class="hrms-hero__sub">On leave</div>
                            </div>
                            <div class="col-4">
                                <div class="hrms-clock" style="font-size:2rem">{{ $stats['rate'] }}%</div>
                                <div class="hrms-hero__sub">Turnout</div>
                            </div>
                        </div>
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
            ['label' => 'Active employees', 'value' => $stats['employees'],   'icon' => 'users',        'tint' => 'primary'],
            ['label' => 'Present today',    'value' => $stats['present'],     'icon' => 'check-circle', 'tint' => 'success'],
            ['label' => 'Late today',       'value' => $stats['late'],        'icon' => 'alert-circle', 'tint' => 'warning'],
            ['label' => 'Not checked in',   'value' => $stats['missing'],     'icon' => 'user-x',       'tint' => 'danger'],
            ['label' => 'On leave',         'value' => $stats['on_leave'],    'icon' => 'coffee',       'tint' => 'info'],
            ['label' => 'Checked out',      'value' => $stats['checked_out'], 'icon' => 'log-out',      'tint' => 'secondary'],
        ];
    @endphp

    @foreach ($tiles as $tile)
        <div class="col-xl-2 col-md-4 col-6 mb-2">
            <div class="hrms-stat">
                <div class="hrms-stat__body">
                    <div class="hrms-stat__icon hrms-tint-{{ $tile['tint'] }}">
                        <i data-feather="{{ $tile['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="hrms-stat__value">{{ $tile['value'] }}</div>
                        <p class="hrms-stat__label">{{ $tile['label'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row match-height">
    {{-- ------------------------------------------------------------ trend --}}
    <div class="col-lg-8 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-0">
                <div>
                    <h4 class="card-title">Attendance trend</h4>
                    <small class="text-muted">Last 14 days</small>
                </div>
                <a href="{{ route('monthly.all-users-report') }}" class="btn btn-sm btn-outline-primary">Monthly report</a>
            </div>
            <div class="card-body">
                <div id="hrmsTrendChart"></div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------ departments --}}
    <div class="col-lg-4 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">By department</h4>
            </div>
            <div class="card-body">
                @forelse ($departments as $department)
                    @php
                        $pct = $department['staff'] > 0
                            ? (int) round(($department['present'] / $department['staff']) * 100)
                            : 0;
                    @endphp
                    <div class="mb-1">
                        <div class="d-flex justify-content-between mb-25">
                            <span class="fw-bolder">{{ $department['name'] }}</span>
                            <small class="text-muted">{{ $department['present'] }}/{{ $department['staff'] }}</small>
                        </div>
                        <div class="hrms-progress">
                            <div class="hrms-progress__bar" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="hrms-empty py-1">
                        <p class="mb-0">No departments configured yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row match-height">
    {{-- --------------------------------------------------- pending leaves --}}
    <div class="col-lg-6 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <div>
                    <h4 class="card-title">Leave approvals</h4>
                    <small class="text-muted">{{ $pendingCount }} awaiting your decision</small>
                </div>
                <a href="{{ route('admin.leave') }}" class="btn btn-sm btn-primary">Review</a>
            </div>
            <div class="card-body">
                @forelse ($pendingLeaves as $leave)
                    <div class="hrms-timeline__row">
                        <span class="hrms-avatar-initials hrms-tint-primary">{{ $initials($leave->user?->full_name) }}</span>
                        <div>
                            <div class="fw-bolder">{{ $leave->user?->full_name ?? 'Unknown' }}</div>
                            <small class="text-muted">
                                {{ ucfirst($leave->type) }} ·
                                {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M') }}
                            </small>
                        </div>
                        <span class="hrms-timeline__times">
                            <span class="hrms-chip hrms-tint-warning">{{ $leave->days }}d</span>
                        </span>
                    </div>
                @empty
                    <div class="hrms-empty">
                        <i data-feather="check-circle"></i>
                        <p class="mb-0">No leave requests waiting.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- -------------------------------------------------------- not in yet --}}
    <div class="col-lg-6 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <div>
                    <h4 class="card-title">Not checked in</h4>
                    <small class="text-muted">Expected today</small>
                </div>
                <a href="{{ route('admin.reports') }}" class="btn btn-sm btn-outline-primary">All records</a>
            </div>
            <div class="card-body">
                @forelse ($missing as $employee)
                    <div class="hrms-timeline__row">
                        <span class="hrms-avatar-initials hrms-tint-danger">{{ $initials($employee->full_name) }}</span>
                        <div>
                            <div class="fw-bolder">{{ $employee->full_name }}</div>
                            <small class="text-muted">
                                {{ optional(optional($employee->relation)->department)->name ?? 'No department' }}
                            </small>
                        </div>
                        <span class="hrms-timeline__times">
                            <span class="hrms-chip hrms-tint-danger">Absent</span>
                        </span>
                    </div>
                @empty
                    <div class="hrms-empty">
                        <i data-feather="thumbs-up"></i>
                        <p class="mb-0">Everyone expected today has checked in.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row match-height">
    {{-- ------------------------------------------------------ live punches --}}
    <div class="col-lg-7 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">Today's punches</h4>
            </div>
            <div class="table-responsive">
                <table class="table hrms-table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPunches as $punch)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="hrms-avatar-initials hrms-tint-primary">{{ $initials($punch->user?->full_name) }}</span>
                                        <span class="fw-bolder">{{ $punch->user?->full_name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ $punch->check_in ? \Carbon\Carbon::parse($punch->check_in)->format('h:i A') : '—' }}</td>
                                <td>{{ $punch->check_out ? \Carbon\Carbon::parse($punch->check_out)->format('h:i A') : '—' }}</td>
                                <td>
                                    <span class="hrms-chip hrms-tint-{{ $punch->is_late ? 'warning' : 'success' }}">
                                        {{ $punch->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="hrms-empty">
                                        <i data-feather="clock"></i>
                                        <p class="mb-0">Nobody has punched in yet today.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------- on leave --}}
    <div class="col-lg-5 col-12 mb-2">
        <div class="card hrms-card h-100 mb-0">
            <div class="card-header pb-50">
                <h4 class="card-title">On leave today</h4>
            </div>
            <div class="card-body">
                @forelse ($onLeaveToday as $leave)
                    <div class="hrms-timeline__row">
                        <span class="hrms-avatar-initials hrms-tint-info">{{ $initials($leave->user?->full_name) }}</span>
                        <div>
                            <div class="fw-bolder">{{ $leave->user?->full_name ?? 'Unknown' }}</div>
                            <small class="text-muted">{{ ucfirst($leave->type) }} leave</small>
                        </div>
                        <span class="hrms-timeline__times">
                            until {{ $leave->end_date->format('d M') }}
                        </span>
                    </div>
                @empty
                    <div class="hrms-empty">
                        <i data-feather="users"></i>
                        <p class="mb-0">Nobody is on leave today.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ------------------------------------------------------------ shortcuts --}}
<div class="row">
    <div class="col-12 mb-2">
        <div class="card hrms-card mb-0">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('user-create') }}" class="btn btn-outline-primary btn-sm">
                        <i data-feather="user-plus" class="me-25"></i> Add employee
                    </a>
                    <a href="{{ route('holiday-list') }}" class="btn btn-outline-primary btn-sm">
                        <i data-feather="gift" class="me-25"></i> Holidays
                    </a>
                    <a href="{{ route('shift-list') }}" class="btn btn-outline-primary btn-sm">
                        <i data-feather="clock" class="me-25"></i> Shifts
                    </a>
                    <a href="{{ route('ip-list') }}" class="btn btn-outline-primary btn-sm">
                        <i data-feather="shield" class="me-25"></i> IP restriction
                    </a>
                    <a href="{{ route('monthly.export', ['month' => now()->format('Y-m')]) }}" class="btn btn-outline-primary btn-sm">
                        <i data-feather="download" class="me-25"></i> Export this month
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
