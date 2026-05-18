@extends('layouts.master')
@section('title','IP Restriction List | '.config('app.name'))
@section('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css">

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
                            <h4 class="modal-title" id="myModalLabel33">Add New IP Address</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="add_category" id="ipForm">
                            @csrf
                            <input type="hidden" name="ip_id" id="ip_id">
                            <div class="modal-body row gy-1 gx-2 mt-75">

                                <div class="mb-1 col-12">
                                    <label class="form-label">IP Address* (IPv4 or IPv6)</label>
                                    <input type="text" name="ip_address" id="ip_address" placeholder="192.168.1.1 or 2001:0db8:..." class="form-control" required />
                                </div>

                                <div class="mb-1 col-12">
                                    <label class="form-label">Location / Description</label>
                                    <input type="text" name="description" id="description" placeholder="Main Office, Branch 1, etc." class="form-control" />
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
                        <div class="card-body border-bottom d-flex justify-content-between align-items-center">
                            <h4 class="card-title">IP Restriction Settings</h4>
                            <div class="d-flex align-items-center">
                                <label class="form-check-label me-1 fw-bold" for="ipRestrictionToggle">IP Restriction is {{ $isIpRestrictionOn ? 'ON' : 'OFF' }}</label>
                                <div class="form-check form-switch form-check-primary">
                                    <input type="checkbox" class="form-check-input" id="ipRestrictionToggle" {{ $isIpRestrictionOn ? 'checked' : '' }} />
                                    <label class="form-check-label" for="ipRestrictionToggle">
                                        <span class="switch-icon-left"><i data-feather="check"></i></span>
                                        <span class="switch-icon-right"><i data-feather="x"></i></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-datatable table-responsive pt-0">
                            <table class="datatables-basic table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>IP Address</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($ipAddresses as $ip)
                                        <tr>
                                            <td>{{ $ip->id }}</td>
                                            <td>{{ $ip->ip_address }}</td>
                                            <td>{{ $ip->description }}</td>
                                            <td>

                                                <a href="#!" class="item-edit" data-id="{{ $ip->id }}" data-ip_address="{{ $ip->ip_address }}" data-description="{{ $ip->description }}" data-bs-toggle= "modal" data-bs-target= "#inlineForm" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Edit IP">
                                                    <i data-feather='edit'></i>
                                                </a>

                                                <a href="#!" class="delete-record" data-id="{{ $ip->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete value">
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
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>

    <script>

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
            dom:
                '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"' +
                '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
                '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
                '>t' +
                '<"d-flex justify-content-between mx-2 row mb-1"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                '>',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5',
                {
                    text: 'Add New IP',
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

        $("#ipForm").submit(function(e) {
            e.preventDefault(); 
            var form = new FormData(this);
            $(this).find('button[type=submit]').append('<i class="fa fa-spinner fa-spin ms-1"></i>');
            $(this).find('button[type=submit]').prop('disabled', true);
            var thiss = $(this);

            var ip_id = $('#ip_id').val();
            var url = "{{ route('ip-store') }}";
            if (ip_id) {
                url = "/ip-address-update/" + ip_id;
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
                    $('.fa.fa-spinner.fa-spin').remove();
                    $(thiss).find('button[type=submit]').prop('disabled', false);
                    let errMsg = "Something went wrong";
                    if(errorThrown.responseJSON && errorThrown.responseJSON.message) {
                        errMsg = errorThrown.responseJSON.message;
                    }
                    toastr.error(errMsg, "Error");
                }
            });
        });

        $(document).on("click", ".delete-record", function(e) {
            if (confirm("Are you sure?")) {
                var id = $(this).attr('data-id');
                var thiss = jQuery(this);
                jQuery.ajax({
                    type: 'get',
                    url: "{{ route('ip-delete') }}",
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
            var ip_address = $(this).data('ip_address');
            var description = $(this).data('description');

            $('#ip_id').val(id);
            $('#ip_address').val(ip_address);
            $('#description').val(description);

            $("#ipForm").find('button[type=submit]').text('Update');
        });

        $(document).on("click", ".btn-close, .add-new", function(e) {
            $("#ipForm")[0].reset();
            $('#ip_id').val('');
            $("#ipForm").find('button[type=submit]').text('Add');
        });
        
        // Toggle IP Restriction
        $('#ipRestrictionToggle').change(function() {
            var isChecked = $(this).is(':checked');
            var statusVal = isChecked ? 'on' : 'off';
            
            $.ajax({
                type: 'post',
                url: "{{ route('ip-toggle') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: statusVal
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message, "Success");
                        setTimeout(function() {window.location.reload(); }, 1000);
                    } else {
                        toastr.error("Failed to update setting", "Error");
                        setTimeout(function() {window.location.reload(); }, 1000);
                    }
                },
                error: function(errorThrown) {
                    toastr.error("Error updating setting", "Error");
                    setTimeout(function() {window.location.reload(); }, 1000);
                }
            });
        });
    </script>
@endsection
