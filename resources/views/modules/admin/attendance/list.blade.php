@extends('layouts.master')
@section('title', 'My Attendance | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">My Attendance</h2>
            </div>
        </div>

        <div class="content-body">

            {{-- ------------------------------------------------- month filter --}}
            <div class="card hrms-card mb-2">
                <div class="card-body">
                    <form method="GET" class="row align-items-end g-1">
                        <div class="col-md-4 col-12">
                            <label class="form-label" for="month">Month</label>
                            <input type="month" id="month" name="month" value="{{ $month }}" class="form-control">
                        </div>
                        <div class="col-md-3 col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-feather="search" class="me-25"></i> Show
                            </button>
                        </div>
                        <div class="col-md-5 col-12 text-md-end">
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ------------------------------------------------------ summary --}}
            <div class="row match-height">
                @php
                    $tiles = [
                        ['label' => 'Present',     'value' => $summary['present'],     'icon' => 'check-circle', 'tint' => 'success'],
                        ['label' => 'Absent',      'value' => $summary['absent'],      'icon' => 'x-circle',     'tint' => 'danger'],
                        ['label' => 'Late',        'value' => $summary['late'],        'icon' => 'alert-circle', 'tint' => 'warning'],
                        ['label' => 'On leave',    'value' => $summary['leave'],       'icon' => 'coffee',       'tint' => 'primary'],
                        ['label' => 'Weekly offs', 'value' => $summary['weekly_off'],  'icon' => 'sun',          'tint' => 'secondary'],
                        ['label' => 'Holidays',    'value' => $summary['holiday'],     'icon' => 'gift',         'tint' => 'info'],
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

            <div class="row match-height mb-1">
                <div class="col-md-4 col-12 mb-2">
                    <div class="card hrms-card h-100 mb-0">
                        <div class="card-body text-center">
                            <div class="hrms-ring" style="--value: {{ $summary['attendance_pct'] }}">
                                <div class="hrms-ring__inner">
                                    <div>
                                        <div class="hrms-ring__value">{{ $summary['attendance_pct'] }}%</div>
                                        <div class="hrms-ring__label">Attendance</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted mt-1 mb-0">
                                {{ $summary['present'] }} of {{ $summary['working_days'] }} working days
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 col-12 mb-2">
                    <div class="card hrms-card h-100 mb-0">
                        <div class="card-header pb-50">
                            <h4 class="card-title">Hours</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4 col-12 mb-1">
                                    <small class="text-muted d-block">Worked</small>
                                    <span class="h4 fw-bolder">{{ $service->humanMinutes($summary['worked_minutes']) }}</span>
                                </div>
                                <div class="col-sm-4 col-12 mb-1">
                                    <small class="text-muted d-block">Required</small>
                                    <span class="h4 fw-bolder">{{ $service->humanMinutes($summary['required_minutes']) }}</span>
                                </div>
                                <div class="col-sm-4 col-12 mb-1">
                                    <small class="text-muted d-block">Balance</small>
                                    @php $balance = $summary['worked_minutes'] - $summary['required_minutes']; @endphp
                                    <span class="h4 fw-bolder text-{{ $balance >= 0 ? 'success' : 'danger' }}">
                                        {{ $balance >= 0 ? '+' : '−' }}{{ $service->humanMinutes(abs($balance)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap mt-50">
                                <span class="hrms-chip hrms-tint-warning">{{ $summary['early_out'] }} early out</span>
                                <span class="hrms-chip hrms-tint-danger">{{ $summary['short_hour'] }} short hour</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------------------------------------------------- day table --}}
            <div class="card hrms-card">
                <div class="card-header pb-50">
                    <h4 class="card-title">Daily record</h4>
                </div>
                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check in</th>
                                <th>Check out</th>
                                <th>Worked</th>
                                <th>Flags</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (array_reverse($days) as $i => $day)
                                <tr class="{{ $day['is_today'] ? 'table-active' : '' }}">
                                    <td>{{ count($days) - $i }}</td>
                                    <td>
                                        <span class="fw-bolder">{{ $day['label'] }}</span>
                                        @if ($day['is_today'])
                                            <span class="hrms-chip hrms-tint-primary ms-50">Today</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $day['tone'] }}">{{ $day['status'] }}</span>
                                    </td>
                                    <td>{{ $day['check_in'] ?? '—' }}</td>
                                    <td>{{ $day['check_out'] ?? '—' }}</td>
                                    <td>{{ $day['worked'] ?? '—' }}</td>
                                    <td>
                                        @if ($day['late'])
                                            <span class="hrms-chip hrms-tint-warning">Late</span>
                                        @endif
                                        @if ($day['early_out'])
                                            <span class="hrms-chip hrms-tint-info">Early out</span>
                                        @endif
                                        @if ($day['short_hour'])
                                            <span class="hrms-chip hrms-tint-danger">Short</span>
                                        @endif
                                        @if (!$day['late'] && !$day['early_out'] && !$day['short_hour'])
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="hrms-empty">
                                            <i data-feather="calendar"></i>
                                            <p class="mb-0">Nothing recorded for this month.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
