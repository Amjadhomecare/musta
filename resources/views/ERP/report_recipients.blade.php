@extends('keen')

@section('content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-dark">Success</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="card shadow-sm mb-10">
            <div class="card-header">
                <h3 class="card-title">Add New Recipient</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('report-recipients.store') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-solid" placeholder="example@domain.com" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Report Type</label>
                        <select name="report_type" class="form-select form-select-solid">
                            <option value="all">All Reports</option>
                            <option value="monthly_onclick">Monthly OnClick</option>
                            <option value="comparative_trial">Comparative Trial</option>
                            <option value="income_statement">Income Statement</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-duotone ki-plus fs-2"></i> Add Recipient
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Manage Recipients</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-200px">Email</th>
                                <th class="min-w-150px">Report Type</th>
                                <th class="min-w-100px text-center">Status</th>
                                <th class="min-w-100px text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recipients as $recipient)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <span class="symbol-label bg-light-primary text-primary fs-2 fw-bold">
                                                {{ strtoupper(substr($recipient->email, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-bold text-hover-primary mb-1 fs-6">{{ $recipient->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info fw-bold">{{ str_replace('_', ' ', ucfirst($recipient->report_type)) }}</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('report-recipients.toggle', $recipient) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-100px">
                                            @if($recipient->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('report-recipients.destroy', $recipient) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            <i class="ki-duotone ki-trash fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($recipients->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-10">No recipients found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
