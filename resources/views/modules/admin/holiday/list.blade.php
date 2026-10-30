@extends('layouts.master')
@section('title', 'Holidays | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">Public Holidays</h2>
            </div>
        </div>

        <div class="content-body">
            <div class="card hrms-card">
                <div class="card-header">
                    <form method="GET" class="d-flex align-items-center gap-1">
                        <label class="form-label mb-0 me-50" for="year">Year</label>
                        <select name="year" id="year" class="form-select" onchange="this.form.submit()" style="width:auto">
                            @foreach ($years as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </form>

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayForm" id="addHoliday">
                        <i data-feather="plus" class="me-25"></i> Add holiday
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Holiday</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Description</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($holidays as $i => $holiday)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="hrms-stat__icon hrms-tint-info" style="width:34px;height:34px;flex:0 0 34px">
                                                <i data-feather="gift"></i>
                                            </span>
                                            <span class="fw-bolder">{{ $holiday->title }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $holiday->date->format('d M Y') }}</td>
                                    <td>{{ $holiday->date->format('l') }}</td>
                                    <td>{{ $holiday->description ?: '—' }}</td>
                                    <td class="text-end">
                                        <a href="#!" class="item-edit me-50"
                                           data-id="{{ $holiday->id }}"
                                           data-title="{{ $holiday->title }}"
                                           data-date="{{ $holiday->date->format('Y-m-d') }}"
                                           data-description="{{ $holiday->description }}"
                                           data-recurring="{{ $holiday->is_recurring ? 1 : 0 }}"
                                           data-bs-toggle="modal" data-bs-target="#holidayForm" title="Edit">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <a href="#!" class="item-delete text-danger" data-id="{{ $holiday->id }}" title="Delete">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="hrms-empty">
                                            <i data-feather="calendar"></i>
                                            <p class="mb-0">No holidays recorded for {{ $year }}.</p>
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

{{-- --------------------------------------------------------------- modal --}}
<div class="modal fade" id="holidayForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="holidayFormEl">
            <div class="modal-header">
                <h5 class="modal-title" id="holidayFormTitle">Add holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="holiday_id">

                <div class="mb-1">
                    <label class="form-label" for="holiday_title">Title</label>
                    <input type="text" class="form-control" id="holiday_title" name="title" placeholder="e.g. Independence Day" required>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="holiday_date">Date</label>
                    <input type="date" class="form-control" id="holiday_date" name="date" required>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="holiday_description">Description</label>
                    <input type="text" class="form-control" id="holiday_description" name="description" placeholder="Optional">
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="holiday_recurring" name="is_recurring" value="1">
                    <label class="form-check-label" for="holiday_recurring">Repeats every year</label>
                </div>
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
        var storeUrl  = "{{ route('holiday-store') }}";
        var updateUrl = "{{ url('holiday-update') }}";

        $('#addHoliday').on('click', function () {
            $('#holidayFormTitle').text('Add holiday');
            $('#holidayFormEl')[0].reset();
            $('#holiday_id').val('');
        });

        $('.item-edit').on('click', function () {
            $('#holidayFormTitle').text('Edit holiday');
            $('#holiday_id').val($(this).data('id'));
            $('#holiday_title').val($(this).data('title'));
            $('#holiday_date').val($(this).data('date'));
            $('#holiday_description').val($(this).data('description'));
            $('#holiday_recurring').prop('checked', $(this).data('recurring') == 1);
        });

        $('#holidayFormEl').on('submit', function (e) {
            e.preventDefault();

            var id = $('#holiday_id').val();

            $.ajax({
                type: 'post',
                url: id ? (updateUrl + '/' + id) : storeUrl,
                data: {
                    title: $('#holiday_title').val(),
                    date: $('#holiday_date').val(),
                    description: $('#holiday_description').val(),
                    is_recurring: $('#holiday_recurring').is(':checked') ? 1 : 0
                },
                dataType: 'json',
                success: function (response) {
                    toastr.success(response.message, 'Success');
                    setTimeout(function () { location.reload(); }, 700);
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errors) {
                        Object.keys(errors).forEach(function (k) { toastr.error(errors[k][0], 'Error'); });
                    } else {
                        toastr.error('Could not save the holiday.', 'Error');
                    }
                }
            });
        });

        $('.item-delete').on('click', function () {
            if (!confirm('Delete this holiday?')) return false;

            var row = $(this).parents('tr');

            $.ajax({
                type: 'post',
                url: "{{ route('holiday-delete') }}",
                data: { id: $(this).data('id') },
                dataType: 'json',
                success: function (response) {
                    toastr.success(response.message, 'Success');
                    row.fadeOut(600);
                },
                error: function () { toastr.error('Could not delete the holiday.', 'Error'); }
            });

            return false;
        });
    });
</script>
@endsection
