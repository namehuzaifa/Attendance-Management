@extends('layouts.master')
@section('title','All Users Monthly Report | '.config('app.name'))

@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css">
<style>
    .filter-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    .report-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    .page-title  { font-weight:700; color:#5e5873; }

    .badge-pct {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .pct-high   { background:#d4edda; color:#155724; }
    .pct-mid    { background:#fff3cd; color:#856404; }
    .pct-low    { background:#f8d7da; color:#721c24; }

    .pct-bar-wrap { background:#e9ecef; border-radius:10px; height:8px; width:80px; display:inline-block; vertical-align:middle; }
    .pct-bar      { border-radius:10px; height:8px; display:block; }

    th { white-space: nowrap; }
    td.num-cell { text-align:center; }
    .stat-absent { color:#ea5455; font-weight:600; }
    .stat-late   { color:#ff9f43; font-weight:600; }
    .stat-present { color:#28c76f; font-weight:600; }

    /* ── DataTables Fixes ── */
    .dt-buttons .btn {
        padding: 5px 15px;
        font-size: 0.85rem;
        margin-left: 5px;
    }
</style>
@endsection

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row mb-1 mt-1">
            <div class="col-12">
                <h4 class="page-title"><i data-feather="users" class="me-1"></i> All Employees Monthly Report</h4>
            </div>
        </div>

        {{-- ── Filters ── --}}
        <div class="card filter-card mb-2">
            <div class="card-body">
                <form method="GET" action="{{ route('monthly.all-users-report') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Year</label>
                            <select name="year" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Month</label>
                            <select name="month" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $val = $selectedYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                        $lbl = \Carbon\Carbon::createFromDate($selectedYear, $m, 1)->format('F Y');
                                    @endphp
                                    <option value="{{ $val }}" {{ $selectedMonth == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-feather="refresh-cw" style="width:14px"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Month Header Info ── --}}
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary fs-6 px-1 py-50" style="padding:6px 14px;border-radius:20px;">
                {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
            </span>
            <span class="text-muted" style="font-size:.9rem;">
                Total Working Days: <strong>{{ $totalWorkingDays }}</strong>
            </span>
        </div>

        {{-- ── Table ── --}}
        <div class="card report-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="allUsersTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Working Days</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Required Hrs</th>
                            <th class="text-center">Worked Hrs</th>
                            <th class="text-center">Attendance %</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usersReport as $i => $row)
                        @php
                            $pct = $row['attendance_pct'];
                            $pctClass = $pct >= 90 ? 'pct-high' : ($pct >= 70 ? 'pct-mid' : 'pct-low');
                            $barColor = $pct >= 90 ? '#28c76f' : ($pct >= 70 ? '#ff9f43' : '#ea5455');
                        @endphp
                        <tr>
                            <td class="text-muted" style="font-size:.85rem;">{{ $i+1 }}</td>
                            <td>
                                <strong>{{ $row['name'] }}</strong><br>
                                <small class="text-muted">{{ $row['email'] }}</small>
                            </td>
                            <td>{{ $row['department'] }}</td>
                            <td class="num-cell">{{ $row['total_working_days'] }}</td>
                            <td class="num-cell stat-present">{{ $row['present_days'] }}</td>
                            <td class="num-cell stat-absent">{{ $row['absent_days'] }}</td>
                            <td class="num-cell stat-late">{{ $row['late_days'] }}</td>
                            <td class="num-cell">
                                {{ $row['total_shift_hours'] ? $row['total_shift_hours'].'h' : '—' }}
                            </td>
                            <td class="num-cell">
                                {{ $row['total_worked_hours'] ? $row['total_worked_hours'].'h' : '—' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span class="badge-pct {{ $pctClass }}">{{ $pct }}%</span>
                                    <div class="pct-bar-wrap">
                                        <span class="pct-bar" style="width:{{ $pct }}%; background:{{ $barColor }};"></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('monthly.user-report', ['user_id' => $row['id'], 'month' => $selectedMonth]) }}"
                                   class="btn btn-sm btn-outline-primary" title="View Full Report">
                                    <i data-feather="eye" style="width:13px"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="{{ asset('/') }}app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace({ width:16, height:16 });

        $('#allUsersTable').DataTable({
            order: [[4, 'desc']],
            pageLength: 25,
            dom: '<"d-flex justify-content-between align-items-center mb-1"<"d-flex"f>B>t<"d-flex justify-content-between mt-1"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-outline-success btn-sm',
                    title: 'Summary Report - {{ $selectedMonth ?? "" }}'
                },
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    className: 'btn btn-outline-primary btn-sm',
                    title: 'Summary Report - {{ $selectedMonth ?? "" }}'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Export PDF',
                    className: 'btn btn-outline-danger btn-sm',
                    title: 'Summary Report - {{ $selectedMonth ?? "" }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ]
                    }
                }
            ],
            language: { search: '', searchPlaceholder: 'Search employee...' }
        });
    });
</script>
@endsection
