@extends('layouts.master')
@section('title', 'User Monthly Report | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">Employee Monthly Report</h2>
            </div>
        </div>

        <div class="content-body">

            <div class="card hrms-card mb-2">
                <div class="card-body">
                    <form method="GET" class="row g-1 align-items-end">
                        <div class="col-md-5 col-12">
                            <label class="form-label" for="user_id">Employee</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Choose an employee…</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ $selectedUser && $selectedUser->id === $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }} @if ($u->employee_code) ({{ $u->employee_code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label" for="month">Month</label>
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                        </div>
                        <div class="col-md-3 col-12">
                            <button type="submit" class="btn btn-primary w-100">Generate</button>
                        </div>
                    </form>
                </div>
            </div>

            @if (!$selectedUser)
                <div class="card hrms-card">
                    <div class="card-body">
                        <div class="hrms-empty">
                            <i data-feather="user"></i>
                            <p class="mb-0">Pick an employee to see their month.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="card hrms-card mb-2">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                            <div>
                                <h4 class="mb-0">{{ $selectedUser->full_name }}</h4>
                                <small class="text-muted">
                                    {{ optional(optional($selectedUser->relation)->designation)->name ?? '—' }}
                                    · {{ optional(optional($selectedUser->relation)->department)->name ?? '—' }}
                                    · {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="h3 mb-0 fw-bolder">{{ $summary['attendance_pct'] }}%</div>
                                <small class="text-muted">attendance</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row match-height">
                    @php
                        $tiles = [
                            ['label' => 'Working days', 'value' => $summary['working_days'], 'icon' => 'calendar',     'tint' => 'primary'],
                            ['label' => 'Present',      'value' => $summary['present'],      'icon' => 'check-circle', 'tint' => 'success'],
                            ['label' => 'Absent',       'value' => $summary['absent'],       'icon' => 'x-circle',     'tint' => 'danger'],
                            ['label' => 'Late',         'value' => $summary['late'],         'icon' => 'alert-circle', 'tint' => 'warning'],
                            ['label' => 'On leave',     'value' => $summary['leave'],        'icon' => 'coffee',       'tint' => 'info'],
                            ['label' => 'Short hour',   'value' => $summary['short_hour'],   'icon' => 'trending-down','tint' => 'secondary'],
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

                <div class="card hrms-card mb-2">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 col-12 mb-1 mb-md-0">
                                <small class="text-muted d-block">Hours worked</small>
                                <span class="h4 fw-bolder">{{ $service->humanMinutes($summary['worked_minutes']) }}</span>
                            </div>
                            <div class="col-md-4 col-12 mb-1 mb-md-0">
                                <small class="text-muted d-block">Hours required</small>
                                <span class="h4 fw-bolder">{{ $service->humanMinutes($summary['required_minutes']) }}</span>
                            </div>
                            <div class="col-md-4 col-12">
                                @php $balance = $summary['worked_minutes'] - $summary['required_minutes']; @endphp
                                <small class="text-muted d-block">Balance</small>
                                <span class="h4 fw-bolder text-{{ $balance >= 0 ? 'success' : 'danger' }}">
                                    {{ $balance >= 0 ? '+' : '−' }}{{ $service->humanMinutes(abs($balance)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card hrms-card">
                    <div class="card-header pb-50">
                        <h4 class="card-title">Day by day</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table hrms-table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check in</th>
                                    <th>Check out</th>
                                    <th>Worked</th>
                                    <th>Flags</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($days as $day)
                                    <tr>
                                        <td class="fw-bolder">{{ $day['label'] }}</td>
                                        <td><span class="hrms-chip hrms-tint-{{ $day['tone'] }}">{{ $day['status'] }}</span></td>
                                        <td>{{ $day['check_in'] ?? '—' }}</td>
                                        <td>{{ $day['check_out'] ?? '—' }}</td>
                                        <td>{{ $day['worked'] ?? '—' }}</td>
                                        <td>
                                            @if ($day['late'])<span class="hrms-chip hrms-tint-warning">Late</span>@endif
                                            @if ($day['early_out'])<span class="hrms-chip hrms-tint-info">Early out</span>@endif
                                            @if ($day['short_hour'])<span class="hrms-chip hrms-tint-danger">Short</span>@endif
                                            @if (!$day['late'] && !$day['early_out'] && !$day['short_hour'])
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="hrms-empty">
                                                <p class="mb-0">Nothing recorded for this month.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
