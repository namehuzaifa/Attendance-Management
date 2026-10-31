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
                                <th>Location</th>
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
                                    <td>
                                        @if ($record->check_in_lat || $record->check_out_lat)
                                            <button type="button" class="btn btn-sm btn-outline-primary item-location"
                                                    data-name="{{ $record->user?->full_name }}"
                                                    data-date="{{ \Carbon\Carbon::parse($record->date)->format('D, d M Y') }}"
                                                    data-in_time="{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('h:i A') : '' }}"
                                                    data-out_time="{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : '' }}"
                                                    data-in_lat="{{ $record->check_in_lat }}"
                                                    data-in_lng="{{ $record->check_in_lng }}"
                                                    data-out_lat="{{ $record->check_out_lat }}"
                                                    data-out_lng="{{ $record->check_out_lng }}">
                                                <i data-feather="map-pin"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
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
                                    <td colspan="9">
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

{{-- -------------------------------------------------------- location modal --}}
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Punch locations</h5>
                    <small class="text-muted" id="loc_subtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary d-none" id="loc_distance" role="alert"></div>

                <div class="row">
                    <div class="col-md-6 col-12 mb-2">
                        <h6 class="mb-50" id="loc_in_title">Checked in</h6>
                        <div id="loc_in_wrap"></div>
                    </div>
                    <div class="col-md-6 col-12 mb-2">
                        <h6 class="mb-50" id="loc_out_title">Checked out</h6>
                        <div id="loc_out_wrap"></div>
                    </div>
                </div>
            </div>
        </div>
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

        // ------------------------------------------------------- locations
        // Google Maps without an API key: `output=embed` is the documented
        // iframe endpoint and `?api=1` hands off to the Maps app.
        function mapBlock(lat, lng, time) {
            if (!lat || !lng) {
                return '<p class="text-muted mb-0">No location was shared for this punch.</p>';
            }

            var embed = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=17&output=embed';
            var open  = 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng;

            return '<iframe src="' + embed + '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" ' +
                   'style="width:100%;height:260px;border:1px solid rgba(0,0,0,.1);border-radius:.5rem;display:block"></iframe>' +
                   '<div class="d-flex justify-content-between align-items-center mt-50">' +
                   '<small class="text-muted">' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) +
                   (time ? ' · ' + time : '') + '</small>' +
                   '<a href="' + open + '" target="_blank" rel="noreferrer" class="btn btn-sm btn-outline-primary">Open in Maps</a>' +
                   '</div>';
        }

        function metresBetween(a, b) {
            var R = 6371000, toRad = function (d) { return d * Math.PI / 180; };
            var dLat = toRad(b.lat - a.lat), dLng = toRad(b.lng - a.lng);
            var h = Math.pow(Math.sin(dLat / 2), 2) +
                    Math.cos(toRad(a.lat)) * Math.cos(toRad(b.lat)) * Math.pow(Math.sin(dLng / 2), 2);
            return Math.round(2 * R * Math.asin(Math.sqrt(h)));
        }

        $('.item-location').on('click', function () {
            var d = $(this).data();

            $('#loc_subtitle').text((d.name || '—') + ' · ' + d.date);
            $('#loc_in_title').text('Checked in' + (d.in_time ? ' · ' + d.in_time : ''));
            $('#loc_out_title').text('Checked out' + (d.out_time ? ' · ' + d.out_time : ''));

            $('#loc_in_wrap').html(mapBlock(d.in_lat, d.in_lng, d.in_time));
            $('#loc_out_wrap').html(mapBlock(d.out_lat, d.out_lng, d.out_time));

            var gap = $('#loc_distance').addClass('d-none');

            if (d.in_lat && d.in_lng && d.out_lat && d.out_lng) {
                var m = metresBetween(
                    { lat: Number(d.in_lat), lng: Number(d.in_lng) },
                    { lat: Number(d.out_lat), lng: Number(d.out_lng) }
                );

                gap.removeClass('d-none alert-primary alert-danger')
                   .addClass(m > 500 ? 'alert-danger' : 'alert-primary')
                   .text('Check-in and check-out were ' +
                         (m < 1000 ? m + ' m' : (m / 1000).toFixed(2) + ' km') + ' apart.' +
                         (m > 500 ? ' That is a long way for one shift — worth a look.' : ''));
            }

            new bootstrap.Modal(document.getElementById('locationModal')).show();
        });
    });
</script>
@endsection
