@extends('layouts.master')
@section('title', 'Employees | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">Employees</h2>
            </div>
        </div>

        <div class="content-body">
            <div class="card hrms-card">
                <div class="card-header">
                    <form method="GET" class="row g-1 align-items-end flex-grow-1">
                        <div class="col-md-4 col-12">
                            <input type="search" name="q" value="{{ $search }}" class="form-control"
                                   placeholder="Search name, email or code">
                        </div>
                        <div class="col-md-3 col-12">
                            <select name="role" class="form-select" onchange="this.form.submit()">
                                <option value="">All roles</option>
                                @foreach (['user' => 'User', 'manager' => 'Manager', 'admin' => 'Admin'] as $value => $label)
                                    <option value="{{ $value }}" {{ $role === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <button class="btn btn-outline-primary w-100">Search</button>
                        </div>
                    </form>

                    <a href="{{ route('user-create') }}" class="btn btn-primary ms-1">
                        <i data-feather="user-plus" class="me-25"></i> Add employee
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Code</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Shift</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $parts = preg_split('/\s+/', trim((string) $user->full_name));
                                    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="hrms-avatar-initials hrms-tint-primary">{{ $initials }}</span>
                                            <div>
                                                <div class="fw-bolder">{{ $user->full_name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->employee_code ?: '—' }}</td>
                                    <td>{{ optional(optional($user->relation)->department)->name ?? '—' }}</td>
                                    <td>{{ optional(optional($user->relation)->designation)->name ?? '—' }}</td>
                                    <td>{{ optional(optional($user->relation)->shiftTiming)->name ?? '—' }}</td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $user->user_role === 'admin' ? 'danger' : ($user->user_role === 'manager' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($user->user_role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $user->is_active ? 'success' : 'secondary' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('user-edit', $user->id) }}" class="me-50" title="Edit">
                                            <i data-feather="edit"></i>
                                        </a>
                                        {{-- A POST form, because a delete behind a plain link fires on any
                                             crafted URL an admin happens to open. --}}
                                        <form method="POST" action="{{ route('user-delete', $user->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete {{ $user->full_name }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0 text-danger align-baseline" title="Delete">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="hrms-empty">
                                            <i data-feather="users"></i>
                                            <p class="mb-0">No employees match this search.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="card-body">{{ $users->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
