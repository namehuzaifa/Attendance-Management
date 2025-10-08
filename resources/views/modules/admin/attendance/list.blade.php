
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
                        <form class="add_category">
                            @csrf
                            <div class="modal-body row gy-1 gx-2 mt-75">

                                <div class="mb-1 col-12">
                                    <label class="form-label">Shift Name*</label>
                                    <input type="text" name="name" placeholder="morning 9:00 to 02:00" class="form-control name" />
                                </div>

                                <div class="mb-1 col-6">
                                    <label class="form-label" for="start_time">Start Time*</label>
                                    <input value="" name="start_time" type="text" id="st-time" class="form-control flatpickr-time text-start flatpickr-input" placeholder="HH:MM" >
                                </div>

                                <div class="mb-1 col-6">
                                    <label class="form-label" for="end_time">Start Time*</label>
                                    <input value="" name="end_time" type="text" id="end-time" class="form-control flatpickr-time text-start flatpickr-input" placeholder="HH:MM" >
                                </div>

                                <div class="mb-1 col-12">
                                    <label class="form-label">Grace Period min</label>
                                    <input type="number" name="grace_period" placeholder="30 min" class="form-control category" />
                                </div>


                            {{-- <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label" for="match_date">Match date*</label>
                                        <input value="" name="match_date" type="text" id="fp-default" class="form-control flatpickr-basic flatpickr-input active" placeholder="YYYY-MM-DD">
                                </div>
                            </div> --}}


                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Add</button>
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
                                        <th>Sr. No</th>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        {{-- <th>Actions</th> --}}
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($attendances as $timing)
                                        <tr>
                                            <td>{{ $timing->id }}</td>
                                            <td class="cat_name">{{ $timing->date }}</td>
                                            <td>{{ $timing?->check_in ?? '- - - ' }}</td>
                                            <td>{{ $timing?->check_out ?? '- - - ' }}</td>
                                            <td>{{ $timing->status }}</td>
                                            {{-- <td> --}}

                                                {{-- <a href="#!" class="item-edit" data-id="{{ $timing->id }}"  data-bs-toggle= "modal" data-bs-target= "#inlineForm" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Edit value Detail">
                                                    <i data-feather='edit'></i>
                                                </a> --}}

                                                {{-- <a href="#!" class="delete-record" data-id="{{ $timing->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete value">
                                                    <i data-feather='trash-2'></i>
                                                </a> --}}
                                            {{-- </td> --}}
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

           order: [[0, 'asc']],
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
                // {
                //     text: 'Add New shift',
                //     className: 'add-new btn btn-primary',
                //     attr: {
                //         'data-bs-toggle': 'modal',
                //         'data-bs-target': '#inlineForm'
                //     },
                //     init: function(api, node, config) {
                //         $(node).removeClass('btn-secondary');
                //     }
                // }

            ],
        });

        table.on('draw', function () {
            feather.replace({
                width: 14,
                height: 14
            });
        });

        $(".add_category").submit(function(e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = new FormData(this);
            // console.log('form', form);
            $(this).find('button[type=submit]').append('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
            $(this).find('button[type=submit]').prop('disabled', true);
            var thiss = $(this);
            // $('body').waitMe({
            //     effect: 'bounce',
            //     text: '',
            //     bg: 'rgba(255,255,255,0.7)',
            //     color: '#000',
            //     maxSize: '',
            //     waitTime: -1,
            //     textPos: 'vertical',
            //     fontSize: '',
            //     source: '',
            // });
            $.ajax({
                type: 'post',
                url: "{{ route('shift-store') }}",
                data: form,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.fa.fa-spinner.fa-spin').remove();
                    // $('body').waitMe('hide');
                    $(thiss).find('button[type=submit]').prop('disabled', false);
                    //  console.log(response);
                    if (!response.status) {
                        toastr.error(response.message, "Error");


                    } else{
                            // if (response.auto_redirect) {window.location.href = response.redirect_url;}
                            // else{
                                toastr.success(response.message, response.title);
                                setTimeout(function() {window.location.reload(); }, 2000);
                            // }
                        }
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                    $('body').waitMe('hide');
                }
            });
        });

        $(document).on("click", ".delete-record", function(e) {
            if (confirm("Are you sure?")) {
                var id = $(this).attr('data-id');
                var thiss = jQuery(this);
                // jQuery('body').waitMe({
                //     effect: 'bounce',
                //     text: '',
                //     bg: 'rgba(255,255,255,0.7)',
                //     color: '#000',
                //     maxSize: '',
                //     waitTime: -1,
                //     textPos: 'vertical',
                //     fontSize: '',
                //     source: '',
                // });
                jQuery.ajax({
                    type: 'get',
                    url: "{{ route('shift-delete') }}",
                    data: {
                        id: id,
                    },
                    dataType: 'json',
                    success: function(response) {
                        // jQuery('body').waitMe('hide');
                        if (!response.status) {
                            toastr.error(response.message, "Error");
                        }else {
                            toastr.success(response.message, "Success");
                            thiss.parents('tr').fadeOut(1000);
                        }
                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                        jQuery('body').waitMe('hide');
                    }
                });
            }
            return false;
        });

        $(document).on("click", ".item-edit", function(e) {
            var cat_name = $(this).parents('tr').find('.cat_name').text();
            $('.category, .old_cat').val(cat_name);
            $(".add_category").find('button[type=submit]').text('Update');
            var id = $(this).data('id');
            let selected = clubArr[id];
            $('#club').val(selected);

            // jQuery('#club').select2({
            //     placeholder: 'Select club'
            // });
        });

        $(document).on("click", ".btn-close", function(e) {
            $(".add_category").find('button[type=submit]').text('Add');
             $('#club').val('');

            jQuery('#club').select2({
                placeholder: 'Select club'
            });
        });
    </script>
@endsection
