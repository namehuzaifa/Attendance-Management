@extends('layouts.master')
@section('title', 'Attendance Reports | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">Attendance Records</h2>
            </div>
        </div>

        <div class="content-body">

            <div class="card hrms-card mb-2">
                <div class="card-body">
                    <form method="GET" class="row g-1 align-items-end">
                        <div class="col-md-3 col-12">
                            <label class="form-label" for="user_id">Employee</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">All employees</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ (int) $userId === $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12">
                            <label class="form-label" for="department_id">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">All departments</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}" {{ (int) $departmentId === $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-12">
                            <label class="form-label" for="date">Date</label>
                            <input type="date" name="date" id="date" value="{{ $date }}" class="form-control">
                        </div>

                        <div class="col-md-2 col-12">
                            <label class="form-label" for="month">or Month</label>
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                        </div>

                        <div class="col-md-2 col-12 d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                            <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card hrms-card">
                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Check in</th>
                                <th>Check out</th>
                                <th>Worked</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th class="text-end">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $record)
                                <tr>
                                    <td>
                                        <div class="fw-bolder">{{ $record->user?->full_name ?? '—' }}</div>
                                        <small class="text-muted">
                                            {{ optional(optional($record->user?->relation)->department)->name ?? '' }}
                                        </small>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($record->date)->format('D, d M Y') }}</td>
                                    <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                    <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                    <td>{{ $record->worked_hours ?? '—' }}</td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $record->is_late ? 'warning' : 'success' }}">
                                            {{ $record->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-secondary">{{ ucfirst($record->source ?? 'web') }}</span>
                                        @if ($record->edited_by)
                                            <span class="hrms-chip hrms-tint-info">Edited</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="#!" class="item-edit"
                                           data-id="{{ $record->id }}"
                                           data-name="{{ $record->user?->full_name }}"
                                           data-date="{{ \Carbon\Carbon::parse($record->date)->format('D, d M Y') }}"
                                           data-check_in="{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '' }}"
                                           data-check_out="{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '' }}"
                                           data-notes="{{ $record->notes }}"
                                           title="Correct this record">
                                            <i data-feather="edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="hrms-empty">
                                            <i data-feather="clipboard"></i>
                                            <p class="mb-0">No attendance records for this filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($attendances->hasPages())
                    <div class="card-body">{{ $attendances->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ------------------------------------------------------------ edit modal --}}
<div class="modal fade" id="editAttendance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="editAttendanceForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Correct attendance</h5>
                    <small class="text-muted" id="edit_subtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        <label class="form-label" for="edit_check_in">Check in</label>
                        <input type="time" class="form-control" id="edit_check_in" name="check_in">
                    </div>
                    <div class="col-6 mb-1">
                        <label class="form-label" for="edit_check_out">Check out</label>
                        <input type="time" class="form-control" id="edit_check_out" name="check_out">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="edit_notes">Note</label>
                        <input type="text" class="form-control" id="edit_notes" name="notes"
                               placeholder="Why was this corrected?">
                    </div>
                </div>
                <small class="text-muted d-block mt-1">
                    Late, early-out and short-hour flags are recalculated from the shift.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        var base = "{{ url('attendance') }}";

        $('.item-edit').on('click', function () {
            $('#editAttendanceForm').attr('action', base + '/' + $(this).data('id'));
            $('#edit_subtitle').text($(this).data('name') + ' · ' + $(this).data('date'));
            $('#edit_check_in').val($(this).data('check_in'));
            $('#edit_check_out').val($(this).data('check_out'));
            $('#edit_notes').val($(this).data('notes'));

            new bootstrap.Modal(document.getElementById('editAttendance')).show();
        });
    });
</script>
@endsection
