@extends('layouts.master')
@section('title', 'Leave Requests | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">Leave Requests</h2>
            </div>
        </div>

        <div class="content-body">

            <div class="row match-height">
                @php
                    $tiles = [
                        ['label' => 'Pending',  'value' => $counts['pending'],  'icon' => 'clock',        'tint' => 'warning'],
                        ['label' => 'Approved', 'value' => $counts['approved'], 'icon' => 'check-circle', 'tint' => 'success'],
                        ['label' => 'Rejected', 'value' => $counts['rejected'], 'icon' => 'x-circle',     'tint' => 'danger'],
                    ];
                @endphp
                @foreach ($tiles as $tile)
                    <div class="col-md-4 col-12 mb-2">
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

            <div class="card hrms-card">
                <div class="card-header">
                    <form method="GET" class="row g-1 align-items-end w-100">
                        <div class="col-md-3 col-12">
                            <label class="form-label" for="status">Status</label>
                            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                                @foreach (['pending', 'approved', 'rejected', 'cancelled', 'all'] as $s)
                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label" for="user_id">Employee</label>
                            <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All employees</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ (int) $userId === $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leaves as $leave)
                                <tr>
                                    <td>
                                        <div class="fw-bolder">{{ $leave->user?->full_name ?? '—' }}</div>
                                        <small class="text-muted">{{ $leave->user?->email }}</small>
                                    </td>
                                    <td>{{ ucfirst($leave->type) }}</td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }}
                                        @if (!$leave->start_date->isSameDay($leave->end_date))
                                            <br><small class="text-muted">to {{ $leave->end_date->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $leave->days }}</td>
                                    <td>
                                        <span title="{{ $leave->reason }}">
                                            {{ \Illuminate\Support\Str::limit($leave->reason, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $leave->status_color }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                        @if ($leave->approver)
                                            <br><small class="text-muted">by {{ $leave->approver->full_name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($leave->status === 'pending')
                                            <button class="btn btn-sm btn-success decide"
                                                    data-id="{{ $leave->id }}" data-decision="approved"
                                                    data-name="{{ $leave->user?->full_name }}">Approve</button>
                                            <button class="btn btn-sm btn-outline-danger decide"
                                                    data-id="{{ $leave->id }}" data-decision="rejected"
                                                    data-name="{{ $leave->user?->full_name }}">Reject</button>
                                        @else
                                            <span class="text-muted">{{ optional($leave->acted_at)->format('d M Y') ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="hrms-empty">
                                            <i data-feather="inbox"></i>
                                            <p class="mb-0">No {{ $status === 'all' ? '' : $status }} leave requests.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($leaves->hasPages())
                    <div class="card-body">{{ $leaves->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- -------------------------------------------------------- decision modal --}}
<div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="decisionForm">
            @csrf
            <input type="hidden" name="status" id="decision_status">
            <div class="modal-header">
                <h5 class="modal-title" id="decisionTitle">Decision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="admin_notes">Note for the employee (optional)</label>
                <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control"
                          placeholder="e.g. Approved, enjoy your break"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="decisionSubmit">Confirm</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        var base = "{{ url('admin/leave') }}";

        $('.decide').on('click', function () {
            var decision = $(this).data('decision');
            var name = $(this).data('name');

            $('#decision_status').val(decision);
            $('#decisionForm').attr('action', base + '/' + $(this).data('id') + '/status');
            $('#decisionTitle').text((decision === 'approved' ? 'Approve' : 'Reject') + ' leave for ' + name);
            $('#decisionSubmit')
                .removeClass('btn-primary btn-success btn-danger')
                .addClass(decision === 'approved' ? 'btn-success' : 'btn-danger')
                .text(decision === 'approved' ? 'Approve' : 'Reject');

            new bootstrap.Modal(document.getElementById('decisionModal')).show();
        });
    });
</script>
@endsection
