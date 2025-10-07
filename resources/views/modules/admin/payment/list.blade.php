
@extends('layouts.master')
@section('title','Session List | '.config('app.name'))

@section('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/tables/datatable/rowGroup.bootstrap5.min.css"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('') }}app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css"> --}}
    <style>
        .update_status{
            padding: 4px;
            border-color: #dfdde4;
            border-radius: 6px;
            color: #605e5e;
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
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Payment List</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <!-- Basic table -->
                <section id="basic-datatable">

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="datatables-basic table">
                                    <thead>
                                        <tr>
                                            <th>id</th>
                                            <th>Name</th>

                                            <th>Payment Method</th>
                                            <th>Price</th>
                                            <th>Payment Status</th>
                                            <th>Booked Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payments as $session)

                                        <tr>
                                            <td>{{ $session->id }}</td>
                                            <td>
                                                <div class="d-flex justify-content-left align-items-center">
                                                    {{-- <div class="avatar  me-1"><img src="{{ asset( ($session?->image_id) ? $session?->image_id : 'assets/images/no-preview.png' ) }}" alt="Avatar" width="32" height="32"></div> --}}
                                                    <div class="d-flex flex-column">
                                                        <span class="emp_name text-truncate fw-bold">{{ $session?->getSession?->card_holder_name }}</span>
                                                        <small class="emp_post text-truncate text-muted">{{ $session?->getSession?->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $session->payment_method }}</td>
                                            <td>${{ $session->amount }}</td>

                                            <td><span class="badge badge-light-{{ ($session->status=='succeeded' || $session->status=='free' ? "success":($session->status=='pending' ? "warning":"danger") ) }}">{{ $session->status }}</span></td>
                                            <td>{{ $session->created_at->format('d-M-Y') }}</td>
                                            {{-- <td>


                                                @if ($session->session_status == 'pending')
                                                    <a title="Reschedule" href="{{ route('session-edit', $session->id) }}" class="item-edit">
                                                        <x-edit-icon/>
                                                    </a>
                                                @endif

                                                <a href="{{ route('user-edit', $session->user_id) }}" class="delete-record">
                                                    <i data-feather='user'></i>
                                                </a>
                                            </td> --}}
                                        </tr>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->

@endsection

@section('scripts')
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js"></script>
    {{-- <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script> --}}
    <script src="{{ asset('') }}app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>

    <script>
        var table = $('.datatables-basic').DataTable({

            order: [[0, 'desc']],
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

                // {
                //     text: 'Add New session',
                //     className: 'add-new btn btn-primary',
                //     attr: {
                //         // 'data-bs-toggle': 'modal',
                //         // 'data-bs-target': '#addservicemodal'
                //     },
                //     init: function (api, node, config) {
                //         $(node).removeClass('btn-secondary');
                //     }
                // }
            ],
            });

            $(document).on("click",".add-new",function() {
                $(location).prop('href', "{{ route('session-create') }}");
            });

            table.on('draw', function () {
            feather.replace({
                width: 14,
                height: 14
            });
            });
    </script>

    <script>

        $(document).on( 'change', '.update_status', function(e){

            var id = $(this).attr('id');
            var $this = $(this);
            var status = $this.val();

            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            url = "{{ route('charge-payment') }}"

            $.ajax({
                url: url,
                type: "POST",
                data: {id : id, status:status},
            }).done(function (data) {
                if (data.status) {
                    toastr.success(data.message);
                    if (status == "done") {
                        $this.parents('tr').find('.badge-light-warning').text('Success');
                        $this.parents('tr').find('.badge-light-warning').removeClass('badge-light-warning').addClass('badge-light-success');
                    }
                    $this.parent().html(status);

                } else{
                    toastr.error(data.message);
                }
                console.log(data);
            });

        });
    </script>
@endsection
