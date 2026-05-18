
@extends('layouts.master')
@section('title','User List | '.config('app.name'))
@section('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/rowGroup.bootstrap5.min.css"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css"> --}}

    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/css/plugins/forms/pickers/form-flat-pickr.css">

    <style>
        .dt-buttons button {
            border: 1px solid #82868b !important;
            background-color: transparent;
            color: #82868b;
            padding: 0.386rem 1.2rem;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 0.358rem;
        }
        .dt-buttons button:hover {
            color: #fff;
            background-color: #7367f0;
            border-color: #7367f0;
        }
        button.dt-button.add-new.btn.btn-primary {
            padding: 10px;
        }
    </style>
@endsection

@section('content')

     <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>

            <!-- Modal -->
            <div class="modal fade text-start" id="inlineForm" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel33">Add New Shift Time</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="add_category" id="shiftForm">
                            @csrf
                            <input type="hidden" name="shift_id" id="shift_id">
                            <div class="modal-body row gy-1 gx-2 mt-75">

                                <div class="mb-1 col-12">
                                    <label class="form-label">Shift Name*</label>
                                    <input type="text" name="name" id="name" placeholder="morning 9:00 to 02:00" class="form-control name" />
                                </div>

                                <div class="mb-1 col-6">
                                    <label class="form-label" for="start_time">Start Time*</label>
                                    <input value="" name="start_time" type="text" id="start_time" class="form-control flatpickr-time text-start flatpickr-input" placeholder="HH:MM" >
                                </div>

                                <div class="mb-1 col-6">
                                    <label class="form-label" for="end_time">End Time*</label>
                                    <input value="" name="end_time" type="text" id="end_time" class="form-control flatpickr-time text-start flatpickr-input" placeholder="HH:MM" >
                                </div>

                                <div class="mb-1 col-12">
                                    <label class="form-label">Grace Period (min)</label>
                                    <input type="number" name="grace_period" id="grace_period" placeholder="30" class="form-control category" />
                                </div>

                                <div class="mb-1 col-12">
                                    <label class="form-label">Off Days</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                        <div class="form-check">
                                            <input class="form-check-input off_day_checkbox" type="checkbox" name="off_days[]" value="{{ $day }}" id="off_day_{{ $day }}">
                                            <label class="form-check-label" for="off_day_{{ $day }}">
                                                {{ $day }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>


                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <!-- users list start -->
                <section class="app-user-list">

                    <!-- list and filter start -->
                    <div class="card">
                        <div class="card-body border-bottom">
                            <h4 class="card-title">Shift Timing</h4>
                        </div>
                        <div class="card-datatable table-responsive pt-0">
                            <table class="datatables-basic table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Shift Name</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Grace Period</th>
                                        <th>Off Days</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($shiftTiming as $timing)
                                        <tr>
                                            <td><?= $timing->id ?></td>
                                            <td class="cat_name"><?= $timing->name ?></td>
                                            <td><?= $timing->start_time->format('h:i a') ?></td>
                                            <td><?= $timing->end_time->format('h:i a') ?></td>
                                            <td><?= $timing->grace_period ?> min</td>
                                            <td><?= $timing->off_days ? implode(', ', $timing->off_days) : 'None' ?></td>
                                            <td>

                                                <a href="#!" class="item-edit" data-id="<?= $timing->id ?>" data-name="<?= $timing->name ?>" data-start_time="<?= $timing->start_time->format('H:i') ?>" data-end_time="<?= $timing->end_time->format('H:i') ?>" data-grace_period="<?= $timing->grace_period ?>" data-off_days='<?= json_encode($timing->off_days ?? []) ?>' data-bs-toggle= "modal" data-bs-target= "#inlineForm" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Edit value Detail">
                                                    <i data-feather='edit'></i>
                                                </a>

                                                <a href="#!" class="delete-record" data-id="<?= $timing->id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete value">
                                                    <i data-feather='trash-2'></i>
                                                </a>
                                            </td>
                                        </tr>

                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- list and filter end -->
                </section>
                <!-- users list ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>


@endsection

@section('scripts')
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js"></script>
    {{-- <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script> --}}
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>

    <script src="{{ asset('') }}app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    {{-- <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    <script src="{{ asset('') }}app-assets/js/scripts/tables/table-datatables-basic.js"></script> --}}

    <script>

        var shiftTiming = <?= json_encode($shiftTiming) ?>;
        var basicPickr = $('.flatpickr-basic');
        var timePickr = $('.flatpickr-time');
        // Default
        if (basicPickr.length) {
            basicPickr.flatpickr({
                minDate: 'today'
            });
        }

        if (timePickr.length) {
            timePickr.flatpickr({
            enableTime: true,
            noCalendar: true
            });
        }

        // jQuery('#club').select2({
        //     placeholder: 'Select club'
        // });

        toastr.options = {
            "closeButton": true,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "2000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        var table = $('.datatables-basic').DataTable({

           // order: [[1, 'desc']],
            dom:
                '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"' +
                '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
                '>t' +
                '<"d-flex justify-content-between mx-2 row mb-1"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                '>',
            // language: {
            //     sLengthMenu: 'Show _MENU_',
            //     search: 'Search',
            //     searchPlaceholder: 'Search..'
            // },
            // Buttons with Dropdown

            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5',
                {
                    text: 'Add New shift',
                    className: 'add-new btn btn-primary',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#inlineForm'
                    },
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }

            ],
        });

        table.on('draw', function () {
            feather.replace({
                width: 14,
                height: 14
            });
        });

        $("#shiftForm").submit(function(e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = new FormData(this);
            $(this).find('button[type=submit]').append('<i class="fa fa-spinner fa-spin ms-1"></i>');
            $(this).find('button[type=submit]').prop('disabled', true);
            var thiss = $(this);

            var shift_id = $('#shift_id').val();
            var url = "{{ route('shift-store') }}";
            if (shift_id) {
                url = "/shift-timing-update/" + shift_id;
            }

            $.ajax({
                type: 'post',
                url: url,
                data: form,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.fa.fa-spinner.fa-spin').remove();
                    $(thiss).find('button[type=submit]').prop('disabled', false);
                    if (!response.status) {
                        toastr.error(response.message, "Error");
                    } else{
                        toastr.success(response.message, "Success");
                        setTimeout(function() {window.location.reload(); }, 2000);
                    }
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                    $('.fa.fa-spinner.fa-spin').remove();
                    $(thiss).find('button[type=submit]').prop('disabled', false);
                }
            });
        });

        $(document).on("click", ".delete-record", function(e) {
            if (confirm("Are you sure?")) {
                var id = $(this).attr('data-id');
                var thiss = jQuery(this);
                jQuery.ajax({
                    type: 'get',
                    url: "{{ route('shift-delete') }}",
                    data: {
                        id: id,
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.status) {
                            toastr.error(response.message, "Error");
                        }else {
                            toastr.success(response.message, "Success");
                            thiss.parents('tr').fadeOut(1000);
                        }
                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    }
                });
            }
            return false;
        });

        $(document).on("click", ".item-edit", function(e) {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var start_time = $(this).data('start_time');
            var end_time = $(this).data('end_time');
            var grace_period = $(this).data('grace_period');
            var off_days = $(this).data('off_days'); // array

            $('#shift_id').val(id);
            $('#name').val(name);
            $('#start_time').val(start_time);
            $('#end_time').val(end_time);
            $('#grace_period').val(grace_period);

            $('.off_day_checkbox').prop('checked', false);
            if(off_days && Array.isArray(off_days)) {
                off_days.forEach(function(day) {
                    $('#off_day_' + day).prop('checked', true);
                });
            }

            $("#shiftForm").find('button[type=submit]').text('Update');
        });

        $(document).on("click", ".btn-close, .add-new", function(e) {
            $("#shiftForm")[0].reset();
            $('#shift_id').val('');
            $('.off_day_checkbox').prop('checked', false);
            $("#shiftForm").find('button[type=submit]').text('Add');
        });
    </script>
@endsection
