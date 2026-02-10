@extends('keen')
@section('content')

<!--begin::Content wrapper-->

<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">


    <!--begin::Add user card-->
    <div class="card shadow-sm mb-8">
        <div class="card-header py-4">
            <h3 class="card-title fw-bold mb-0">Add New User</h3>
        </div>
        <div class="card-body">
            <form id="addUserForm">
                @csrf
                <div class="row gx-5 gy-4">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control form-control-sm form-control-solid" id="name" name="name" required />
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="group" class="form-label fw-semibold">Group</label>
                        <select class="form-select form-select-sm form-select-solid" id="group" name="user_group" required>
                            <option value="sales">Sales</option>
                            <option value="accounting">Accounting</option>
                            <option value="typing">Typing</option>
                            <option value="HR">HR</option>
                            <option value="online">Online</option>
                            <option value="owner">Owner</option>
                            <option value="seo">SEO</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="email" class="form-label fw-semibold">Email address</label>
                        <input type="email" class="form-control form-control-sm form-control-solid" id="email" name="email" required />
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control form-control-sm form-control-solid" id="password" name="password" required />
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-5">
                    <button type="submit" class="btn btn-primary btn-lg px-6">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <!--end::Add user card-->

    <!--begin::Filters card-->
    <div class="card shadow-sm mb-7">
        <div class="card-body">
            <div class="row gx-5 gy-4 align-items-end">
                <!-- From date -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="min-date" class="form-label fw-semibold mb-1">From</label>
                    <input type="date" id="min-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
                <!-- To date -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="max-date" class="form-label fw-semibold mb-1">To</label>
                    <input type="date" id="max-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
                <!-- User group filter -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="filterUserGroup" class="form-label fw-semibold mb-1">Group</label>
                    <select id="filterUserGroup" class="form-select form-select-sm form-select-solid">
                        <option value="">All</option>
                        <option value="sales">Sales</option>
                        <option value="accounting">Accounting</option>
                        <option value="typing">Typing</option>
                        <option value="HR">HR</option>
                        <option value="online">Online</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <!-- User name filter -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="userName" class="form-label fw-semibold mb-1">User name</label>
                    <select id="userName" class="form-select form-select-sm form-select-solid">
                        <option value="">All</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!--end::Filters card-->

    <!--begin::Datatable card-->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="users-datatable" class="table table-striped table-row-dashed fs-6 gy-5 gs-5 w-100">
                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                        <tr>
                            <th>Created at</th>
                            <th>User name</th>
                            <th>Group</th>
                            <th>Active</th>
                            <th class="text-end">Options</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--end::Datatable card-->

</div>
<!--end::Content container-->

</div>
<!--end::Content wrapper-->

<!-- Modal -->
<div id="edit-users" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editUsersLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsersLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateuserForm">
                    @csrf
                    <div class="mb-3">
                        <label for="idInput" class="form-label">ID</label>
                        <input hidden readOnly type="text" class="form-control" id="idInput" name="idInput">
                    </div>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Name</label>
                        <input readOnly type="text" class="form-control" id="nameInput" name="nameInput">
                    </div>


                                
                    <div class="mb-3">
                        <label for="statusInput" class="form-label">Status</label>
                        <select class="form-select" id="statusInput" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
            
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="groupInput" class="form-label">Group</label>
                        <select class="form-select" id="groupInput" name="group">
                            <option value="sales">Sales</option>
                            <option value="accounting">Accounting</option>
                            <option value="typing">Typing</option>
                            <option value="HR">HR</option>
                            <option value="online">Online</option>
                            <option value="owner">Owner</option>
                            <option value="agent">Agent</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>


                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@push('scripts')
    @vite('resources/js/admin/admin.js')
@endpush
