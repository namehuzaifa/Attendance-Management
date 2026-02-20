@extends('layouts.master')
@section('title','User Monthly Report | '.config('app.name'))

@php
    /** Helper: format raw minutes → "Xh Ym" or "—" */
    function fmtMins(?int $mins): string {
        if ($mins === null || $mins < 0) return '—';
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        if ($h === 0) return "{$m}m";
        if ($m === 0) return "{$h}h";
        return "{$h}h {$m}m";
    }
@endphp

@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="{{ asset('/') }}app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css">
<style>
    /* ── Summary Cards ── */
    .summary-card {
        border-radius: 12px;
        padding: 1.1rem .9rem;
        text-align: center;
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .summary-card .num { font-size: 1.65rem; font-weight: 700; line-height: 1; }
    .summary-card .sub { font-size: .72rem; opacity:.85; margin-top:2px; }
    .summary-card .lbl { font-size: .75rem; margin-top:5px; opacity:.9; letter-spacing:.3px; }
    .bg-days    { background: linear-gradient(135deg,#3498db,#5dade2); }
    .bg-present { background: linear-gradient(135deg,#28c76f,#48da89); }
    .bg-absent  { background: linear-gradient(135deg,#ea5455,#f08182); }
    .bg-late    { background: linear-gradient(135deg,#ff9f43,#ffb976); }
    .bg-req     { background: linear-gradient(135deg,#7367f0,#9e95f5); }
    .bg-worked  { background: linear-gradient(135deg,#00cfe8,#1ce7ff); }
    .bg-short   { background: linear-gradient(135deg,#fd7e14,#f9a85d); }

    /* ── Row colours ── */
    tr.row-off     { background: #f8f9fa; color: #aaa; }
    tr.row-absent  { background: #fff5f5; }
    tr.row-late    { background: #fff8f0; }
    tr.row-totals  { background: #f0f4ff; font-weight: 700; border-top: 2px solid #7367f0; }

    /* ── Status badges ── */
    .badge-on-time  { background:#d4edda; color:#155724; }
    .badge-late     { background:#fff3cd; color:#856404; }
    .badge-absent   { background:#f8d7da; color:#721c24; }
    .badge-offday   { background:#e2e3e5; color:#383d41; }
    .badge-status   { padding:3px 10px; border-radius:20px; font-size:.78rem; font-weight:600; display:inline-block; }

    /* ── Hour pills ── */
    .pill-req    { background:#ede9fe; color:#5b21b6; padding:2px 9px; border-radius:12px; font-size:.78rem; font-weight:600; }
    .pill-worked { background:#d1fae5; color:#065f46; padding:2px 9px; border-radius:12px; font-size:.78rem; font-weight:600; }
    .pill-short  { background:#ffecd2; color:#c05e00; padding:2px 9px; border-radius:12px; font-size:.78rem; font-weight:600; }
    .pill-na     { color:#aaa; font-size:.82rem; }

    .filter-card { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    .page-title  { font-weight:700; color:#5e5873; }
    th { white-space:nowrap; }

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
                <h4 class="page-title"><i data-feather="bar-chart-2" class="me-1"></i> User Monthly Report</h4>
            </div>
        </div>

        {{-- ── Filter Card ── --}}
        <div class="card filter-card mb-2">
            <div class="card-body">
                <form method="GET" action="{{ route('monthly.user-report') }}" id="filterForm">
                    <div class="row g-2 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Employee</label>
                            <select name="user_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="">-- Select Employee --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $selectedUserId == $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Year</label>
                            <select name="year" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                @foreach(range(now()->year, max(now()->year - 3, 2023)) as $yr)
                                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($selectedUserId && count($availableMonths))
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Month</label>
                            <select name="month" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <option value="">-- Select Month --</option>
                                @foreach($availableMonths as $m)
                                    <option value="{{ $m['value'] }}" {{ $selectedMonth == $m['value'] ? 'selected' : '' }}>
                                        {{ $m['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-2">
                            <a href="{{ route('monthly.user-report') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedUserId && count($availableMonths) === 0)
        <div class="alert alert-warning">No attendance records found for <strong>{{ $selectedUser->full_name }}</strong> in {{ $selectedYear }}.</div>
        @endif

        {{-- ── Summary Cards ── --}}
        @if($summary)
        @php
            $dailyShiftMins  = $summary['daily_shift_mins'];
            $totalShiftMins  = $summary['total_shift_mins'];
            $totalWorkedMins = $summary['total_worked_mins'];
            $totalShortMins  = $summary['total_short_mins'];
        @endphp
        <div class="row g-2 mb-2">

            {{-- Working Days --}}
            <div class="col-6 col-md-2">
                <div class="summary-card bg-days">
                    <div class="num">{{ $summary['total_working_days'] }}</div>
                    @if($dailyShiftMins)
                    <div class="sub">{{ fmtMins($dailyShiftMins) }}/day</div>
                    @endif
                    <div class="lbl">Working Days</div>
                </div>
            </div>

            {{-- Present --}}
            <div class="col-6 col-md-2">
                <div class="summary-card bg-present">
                    <div class="num">{{ $summary['present_days'] }}</div>
                    <div class="lbl">Present Days</div>
                </div>
            </div>

            {{-- Absent --}}
            <div class="col-6 col-md-2">
                <div class="summary-card bg-absent">
                    <div class="num">{{ $summary['absent_days'] }}</div>
                    <div class="lbl">Absent Days</div>
                </div>
            </div>

            {{-- Late --}}
            <div class="col-6 col-md-2">
                <div class="summary-card bg-late">
                    <div class="num">{{ $summary['late_days'] }}</div>
                    <div class="lbl">Late Days</div>
                </div>
            </div>

            {{-- Required Hours --}}
            @if($totalShiftMins > 0)
            <div class="col-6 col-md-2">
                <div class="summary-card bg-req">
                    <div class="num" style="font-size:1.35rem;">{{ fmtMins($totalShiftMins) }}</div>
                    <div class="lbl">Required Hours</div>
                </div>
            </div>
            @endif

            {{-- Worked Hours --}}
            @if($totalWorkedMins > 0)
            <div class="col-6 col-md-2">
                <div class="summary-card bg-worked">
                    <div class="num" style="font-size:1.35rem;">{{ fmtMins($totalWorkedMins) }}</div>
                    <div class="lbl">Worked Hours</div>
                </div>
            </div>
            @endif

            {{-- Short Hours --}}
            @if($totalShortMins > 0)
            <div class="col-6 col-md-2">
                <div class="summary-card bg-short">
                    <div class="num" style="font-size:1.35rem;">{{ fmtMins($totalShortMins) }}</div>
                    <div class="lbl">Total Short</div>
                </div>
            </div>
            @endif

        </div>
        @endif

        {{-- ── Day-by-Day Table ── --}}
        @if(count($report))
        @php
            // Running totals for footer row
            $ftReq    = 0;
            $ftWorked = 0;
            $ftShort  = 0;
            foreach($report as $row) {
                if(!$row['is_off']) {
                    $ftReq    += $row['daily_shift_mins'] ?? 0;
                    $ftWorked += $row['worked_mins']      ?? 0;
                    $ftShort  += $row['short_mins']       ?? 0;
                }
            }
        @endphp
        <div class="card" style="border-radius:12px;border:none;box-shadow:0 2px 10px rgba(0,0,0,.08);">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-75">
                <h5 class="mb-0" style="color:#5e5873;">
                    <i data-feather="calendar" class="me-1 text-primary"></i>
                    {{ $selectedUser->full_name }}
                    &nbsp;&middot;&nbsp;
                    {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Req. Hours</th>
                            <th>Worked Hours</th>
                            <th>Short Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $i => $row)
                        @php
                            $trClass = '';
                            if ($row['is_off'])                              $trClass = 'row-off';
                            elseif ($row['status'] === 'Absent')             $trClass = 'row-absent';
                            elseif (str_contains($row['status'] ?? '', 'late')) $trClass = 'row-late';

                            $badgeClass = 'badge-on-time';
                            if ($row['is_off'])                              $badgeClass = 'badge-offday';
                            elseif ($row['status'] === 'Absent')             $badgeClass = 'badge-absent';
                            elseif (str_contains($row['status'] ?? '', 'late')) $badgeClass = 'badge-late';
                        @endphp
                        <tr class="{{ $trClass }}">
                            <td class="text-muted" style="font-size:.83rem;">{{ $i+1 }}</td>
                            <td><strong>{{ $row['date'] }}</strong></td>
                            <td>{{ $row['check_in']  ?? '—' }}</td>
                            <td>{{ $row['check_out'] ?? '—' }}</td>

                            {{-- Required Hours (per day) --}}
                            <td>
                                @if(!$row['is_off'] && isset($row['daily_shift_mins']) && $row['daily_shift_mins'])
                                    <span class="pill-req">{{ fmtMins($row['daily_shift_mins']) }}</span>
                                @else
                                    <span class="pill-na">—</span>
                                @endif
                            </td>

                            {{-- Worked Hours --}}
                            <td>
                                @if(isset($row['worked_mins']) && $row['worked_mins'] !== null)
                                    <span class="pill-worked">{{ fmtMins($row['worked_mins']) }}</span>
                                @else
                                    <span class="pill-na">—</span>
                                @endif
                            </td>

                            {{-- Short Hours --}}
                            <td>
                                @if(isset($row['short_mins']) && $row['short_mins'] > 0)
                                    <span class="pill-short">−{{ fmtMins($row['short_mins']) }}</span>
                                @else
                                    <span class="pill-na">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge-status {{ $badgeClass }}">
                                    {{ ucwords(str_replace('|', '·', $row['status'] ?? '')) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="row-totals">
                            <td colspan="4" class="text-end text-primary" style="font-size:.85rem; letter-spacing:.5px;">MONTHLY TOTAL</td>
                            <td><span class="pill-req">{{ $ftReq ? fmtMins($ftReq) : '—' }}</span></td>
                            <td><span class="pill-worked">{{ $ftWorked ? fmtMins($ftWorked) : '—' }}</span></td>
                            <td><span class="pill-short">{{ $ftShort ? '−'.fmtMins($ftShort) : '—' }}</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

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

        $('#reportTable').DataTable({
            paging: false,
            info: false,
            dom: '<"d-flex justify-content-between align-items-center mb-1"<"d-flex"f>B>t',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-outline-success btn-sm',
                    title: 'Attendance Report - {{ $selectedUser->full_name ?? "" }} - {{ $selectedMonth ?? "" }}'
                },
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    className: 'btn btn-outline-primary btn-sm',
                    title: 'Attendance Report - {{ $selectedUser->full_name ?? "" }} - {{ $selectedMonth ?? "" }}'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Export PDF',
                    className: 'btn btn-outline-danger btn-sm',
                    title: 'Attendance Report - {{ $selectedUser->full_name ?? "" }} - {{ $selectedMonth ?? "" }}',
                    orientation: 'landscape'
                }
            ],
            language: { search: '', searchPlaceholder: 'Quick Filter...' }
        });
    });
</script>
@endsection
