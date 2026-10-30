@extends('layouts.master')
@section('title', 'All Employees Monthly Report | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">All Employees — Monthly</h2>
            </div>
        </div>

        <div class="content-body">

            <div class="card hrms-card mb-2">
                <div class="card-body">
                    <form method="GET" class="row g-1 align-items-end">
                        <div class="col-md-4 col-12">
                            <label class="form-label" for="month">Month</label>
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                        </div>
                        <div class="col-md-3 col-12">
                            <button type="submit" class="btn btn-primary w-100">Generate</button>
                        </div>
                        <div class="col-md-5 col-12 text-md-end">
                            <a href="{{ route('monthly.export', ['month' => $month]) }}" class="btn btn-outline-primary">
                                <i data-feather="download" class="me-25"></i> Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $totals = [
                    'present' => array_sum(array_column($usersReport, 'present')),
                    'absent'  => array_sum(array_column($usersReport, 'absent')),
                    'late'    => array_sum(array_column($usersReport, 'late')),
                    'leave'   => array_sum(array_column($usersReport, 'leave')),
                ];
            @endphp

            <div class="row match-height">
                @php
                    $tiles = [
                        ['label' => 'Employees', 'value' => count($usersReport), 'icon' => 'users',        'tint' => 'primary'],
                        ['label' => 'Present',   'value' => $totals['present'],  'icon' => 'check-circle', 'tint' => 'success'],
                        ['label' => 'Absent',    'value' => $totals['absent'],   'icon' => 'x-circle',     'tint' => 'danger'],
                        ['label' => 'Late',      'value' => $totals['late'],     'icon' => 'alert-circle', 'tint' => 'warning'],
                    ];
                @endphp
                @foreach ($tiles as $tile)
                    <div class="col-lg-3 col-6 mb-2">
                        <div class="hrms-stat">
                            <div class="hrms-stat__body">
                                <div class="hrms-stat__icon hrms-tint-{{ $tile['tint'] }}">
                                    <i data-feather="{{ $tile['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="hrms-stat__value">{{ $tile['value'] }}</div>
                                    <p class="hrms-stat__label">{{ $tile['label'] }} ({{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card hrms-card">
                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Working</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Leave</th>
                                <th>Late</th>
                                <th>Worked</th>
                                <th>Required</th>
                                <th style="min-width:130px">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usersReport as $row)
                                <tr>
                                    <td>
                                        <div class="fw-bolder">{{ $row['name'] }}</div>
                                        <small class="text-muted">{{ $row['code'] ?? $row['email'] }}</small>
                                    </td>
                                    <td>{{ $row['department'] }}</td>
                                    <td>{{ $row['working_days'] }}</td>
                                    <td class="text-success fw-bolder">{{ $row['present'] }}</td>
                                    <td class="text-danger fw-bolder">{{ $row['absent'] }}</td>
                                    <td>{{ $row['leave'] }}</td>
                                    <td>{{ $row['late'] }}</td>
                                    <td>{{ $service->humanMinutes($row['worked_minutes']) }}</td>
                                    <td>{{ $service->humanMinutes($row['required_minutes']) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="hrms-progress flex-grow-1">
                                                <div class="hrms-progress__bar" style="width: {{ $row['attendance_pct'] }}%"></div>
                                            </div>
                                            <small class="fw-bolder">{{ $row['attendance_pct'] }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="hrms-empty">
                                            <i data-feather="users"></i>
                                            <p class="mb-0">No employees to report on yet.</p>
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
