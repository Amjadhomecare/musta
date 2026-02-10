@extends('keen')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="pending-relesed-datatable" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Nationality</th>
                            <th>maid type</th>
                            <th>Agent</th>
                            <th>Reason</th>
                            <th>note</th>
                            <th>Action</th>
                            <th>Created by</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables fills tbody --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="approving-form-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Approving the Maid</h5>
            </div>

            <div class="modal-body mt-3">
                <form id="approve_maid" class="px-3">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    {{-- Hidden maid_id used by /ajax-release-maid --}}
                    <input type="hidden" id="maidIDInput" name="maid_id">

                    <div class="mb-3">
                        <input readonly type="text" id="maidNameInput" name="maid_name"
                               class="form-control form-control-lg mb-2" placeholder="Maid Name">
                    </div>

                    <div class="mb-3">
                        <label for="agentAccountSelect" class="form-label">Agent Account:</label>
                        <select id="agentAccountSelect" name="agent_acc" class="form-select form-select-lg mb-2">
                            @foreach($ledgers as $agent)
                                <option value="{{ $agent->ledger }}">{{ $agent->ledger }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status</label>
                        <select id="new_status" name="new_status" class="form-select form-select-lg mb-2">
                            <option></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <input type="number" name="cost" class="form-control form-control-lg mb-2" placeholder="Cost">
                    </div>

                    <div class="mb-3">
                        <input type="text" name="note" class="form-control form-control-lg mb-2" placeholder="Note">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/complain/release_approve.js')
@endpush
