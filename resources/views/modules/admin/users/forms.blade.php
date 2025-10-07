@extends('layouts.master')
@section('title',($isEdit) ? "Edit User" : 'Add User'.' | '.config('app.name'))

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
                            <h2 class="content-header-title float-start mb-0"> {{ ($isEdit) ? "Edit User" : 'Add User' }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <!-- Basic Vertical form layout section start -->

                <section id="basic-vertical-layouts">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="form form-vertical" method="POST" action="{{ ($isEdit) ?  route('user-update', $id) : route('user-store') }}" >
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6  col-12">
                                                <div class="mb-1">
                                                    <label class="form-label" for="first-name-vertical" >Name</label>
                                                    <input type="text" id="first-name-vertical" class="form-control" value="{{ ($isEdit) ? $user?->full_name : old('name')  }}" name="name" placeholder="Full Name" />
                                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6  col-12">
                                                <div class="mb-1">
                                                    <label class="form-label" for="email">Email</label>
                                                    <input type="email" id="email" class="form-control" value="{{ ($isEdit) ? $user?->email : old('email')  }}" name="email" placeholder="Email" />
                                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6  col-12">
                                                <div class="mb-1">
                                                    <label class="form-label" for="phone">Phone Number</label>
                                                    <input type="phone" id="phone" class="form-control" value="{{ ($isEdit) ? $user?->phone : old('phone')  }}" name="phone" placeholder="Phone Number" />
                                                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                            @if (auth()->user()->user_role == "admin")
                                                <div class="col-md-6  col-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="user_role">User Role</label>
                                                        <select name="user_role" class="form-control" id="user_role">
                                                            <option {{ ($isEdit && $user?->user_role == 'user') ? "selected" : "" }} value="user">User</option>
                                                            <option {{ ($isEdit && $user?->user_role == 'manager') ? "selected" : "" }} value="manager">Manager</option>
                                                            <option {{ ($isEdit && $user?->user_role == 'admin') ? "selected" : "" }} value="admin">Admin</option>
                                                        </select>
                                                        @error('user_role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6  col-12 designation">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="designation_id">Designations</label>
                                                        <select name="designation_id" class="form-control" id="designation_id">
                                                            @foreach ($designations as $designation)
                                                                <option {{ ($isEdit && $user?->relation?->designation_id == $designation->id) ? "selected" : "" }} value="{{ $designation->id }}">{{ $designation->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('designation_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6  col-12 department">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="department_id">Departments</label>
                                                        <select name="department_id" class="form-control" id="department_id">
                                                            @foreach ($departments as $department)
                                                                <option {{ ($isEdit && $user?->relation?->department_id == $department->id ) ? "selected" : "" }} value="{{ $department->id }}">{{ $department->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6  col-12 shift_timing">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="shift_timing_id">Shift Timing</label>
                                                        <select name="shift_timing_id" class="form-control" id="shift_timing_id">
                                                            @foreach ($ShiftTimings as $ShiftTiming)
                                                                <option {{ ($isEdit && $user?->relation?->shift_timing_id == $ShiftTiming->id) ? "selected" : "" }} value="{{ $ShiftTiming->id }}">{{ $ShiftTiming->name }} ({{ $ShiftTiming->start_time->format('h:i a') }} - {{ $ShiftTiming->end_time->format('h:i a') }} )</option>
                                                            @endforeach
                                                        </select>
                                                        @error('shift_timing_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6  col-12 manager">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="manager_id">Manager</label>
                                                        <select name="manager_id" class="form-control" id="manager_id">
                                                            @foreach ($managers as $manager)
                                                                <option {{ ($isEdit && $user?->relation?->manager_id == $manager->id) ? "selected" : "" }} value="{{ $manager->id }}">{{ $manager->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('manager_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>

                                            @endif

                                            <div class="col-md-6  col-12">
                                                <div class="mb-1">
                                                    <label class="form-label" for="email-id-vertical">Password</label>
                                                    <div class="input-group input-group-merge form-password-toggle">
                                                        <input type="password" class="form-control form-control-merge" id="password" name="password" tabindex="2" placeholder="Password" aria-describedby="login-password">
                                                        <span class="input-group-text cursor-pointer">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        </span>
                                                    </div>
                                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary me-1">Submit</button>
                                                <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- Basic Vertical form layout section end -->

            </div>
        </div>
    </div>

    <!-- END: Content-->

@endsection

@section('scripts')

<script>
    $("#user_role").change(function(){
        var target = $('.designation, .department, .shift_timing, .manager');
        if ($(this).val() != 'user') {
            target.hide()
        }else{
            target.show()
        }
    });
</script>
@endsection

