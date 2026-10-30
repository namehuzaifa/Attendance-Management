@extends('layouts.master')
@section('title', 'My Leave | ' . config('app.name'))

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <h2 class="content-header-title float-start mb-0">My Leave</h2>
            </div>
        </div>

        <div class="content-body">
            <div class="row match-height">
                {{-- ------------------------------------------------- balance --}}
                <div class="col-lg-4 col-12 mb-2">
                    <div class="card hrms-card h-100 mb-0">
                        <div class="card-header pb-50">
                            <h4 class="card-title">Balance {{ now()->year }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="hrms-ring"
                                 style="--value: {{ $balance['quota'] > 0 ? min(100, round(($balance['remaining'] / $balance['quota']) * 100)) : 0 }}">
                                <div class="hrms-ring__inner">
                                    <div>
                                        <div class="hrms-ring__value">{{ $balance['remaining'] }}</div>
                                        <div class="hrms-ring__label">Days left</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row text-center mt-2">
                                <div class="col-4">
                                    <div class="fw-bolder h5 mb-0">{{ $balance['quota'] }}</div>
                                    <small class="text-muted">Quota</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bolder h5 mb-0">{{ $balance['used'] }}</div>
                                    <small class="text-muted">Used</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bolder h5 mb-0">{{ $balance['pending'] }}</div>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>

                            @if (!empty($balance['by_type']))
                                <hr>
                                @foreach ($balance['by_type'] as $type => $days)
                                    <div class="d-flex justify-content-between mb-25">
                                        <span>{{ ucfirst($type) }}</span>
                                        <span class="fw-bolder">{{ $days }}d</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- --------------------------------------------- apply form --}}
                <div class="col-lg-8 col-12 mb-2">
                    <div class="card hrms-card h-100 mb-0">
                        <div class="card-header pb-50">
                            <h4 class="card-title">Apply for leave</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('leave.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 col-12 mb-1">
                                        <label class="form-label" for="type">Type</label>
                                        <select name="type" id="type" class="form-select" required>
                                            @foreach ($types as $type)
                                                <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                                    {{ ucfirst($type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4 col-12 mb-1">
                                        <label class="form-label" for="start_date">From</label>
                                        <input type="date" name="start_date" id="start_date"
                                               class="form-control" value="{{ old('start_date') }}" required>
                                        @error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4 col-12 mb-1">
                                        <label class="form-label" for="end_date">To</label>
                                        <input type="date" name="end_date" id="end_date"
                                               class="form-control" value="{{ old('end_date') }}" required>
                                        @error('end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12 mb-1">
                                        <label class="form-label" for="reason">Reason</label>
                                        <textarea name="reason" id="reason" rows="3" class="form-control"
                                                  placeholder="Tell your manager why" required>{{ old('reason') }}</textarea>
                                        @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i data-feather="send" class="me-25"></i> Submit request
                                        </button>
                                        <small class="text-muted ms-1">
                                            Weekly offs and public holidays are not deducted.
                                        </small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------- my requests --}}
            <div class="card hrms-card">
                <div class="card-header pb-50">
                    <h4 class="card-title">My requests</h4>
                </div>
                <div class="table-responsive">
                    <table class="table hrms-table mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Decided by</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leaves as $leave)
                                <tr>
                                    <td class="fw-bolder">{{ ucfirst($leave->type) }}</td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }}
                                        @if (!$leave->start_date->isSameDay($leave->end_date))
                                            – {{ $leave->end_date->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $leave->days }}</td>
                                    <td>
                                        <span title="{{ $leave->reason }}">
                                            {{ \Illuminate\Support\Str::limit($leave->reason, 40) }}
                                        </span>
                                        @if ($leave->admin_notes)
                                            <br><small class="text-muted">Note: {{ $leave->admin_notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="hrms-chip hrms-tint-{{ $leave->status_color }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $leave->approver?->full_name ?? '—' }}</td>
                                    <td class="text-end">
                                        @if ($leave->status === 'pending')
                                            <form method="POST" action="{{ route('leave.cancel', $leave->id) }}"
                                                  onsubmit="return confirm('Cancel this request?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="hrms-empty">
                                            <i data-feather="coffee"></i>
                                            <p class="mb-0">You have not requested any leave yet.</p>
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
@endsection
