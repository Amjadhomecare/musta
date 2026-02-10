@extends('keen')
@section('content')

<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
<table id="pending-arrival-datatable" class="table  table-bordered table-hover" style="width:100%">

<thead>
    <tr>         
            <th>Date</th>
            <th>Name</th>
            <th>Status</th>
            <th>Nationality</th>
            <th>Agent</th>
            <th>note</th>
            <th>Action</th>
    </tr>
</thead>
</table>

</div>
</div>
</div>
</div>



<div id="approving-form-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Approving the Maid</h5>
            </div>

            <!-- Modal Body -->
            <div class="modal-body mt-3">
              <form id="approve_maid" class="px-3">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">

  <input type="hidden" id="maidIdInput" name="maid_id">  <!-- NEW -->

  <div class="mb-3">
    <input readonly type="text" id="maidNameInput" name="maid_name" class="form-control form-control-lg mb-2" placeholder="Maid Name">
  </div>

  <div class="mb-3">
    <label for="agentAccountSelect" class="form-label">Agent Account/:</label>
    <select id="agentAccountSelect" name="agent_acc" class="form-select form-select-lg mb-2" data-toggle="select2">
      @foreach($ledgers as $agent)
        <option value="{{ $agent->ledger }}">{{ $agent->ledger }}</option>
      @endforeach
    </select>
  </div>

  <div class="mb-3">
    <input type="number" name="cost" class="form-control form-control-lg mb-2" placeholder="Cost">
  </div>

  <div class="form-check mb-3">
    <input type="checkbox" name="dh" class="form-check-input" id="dhCheck">
    <label for="dhCheck" style="color:red" class="form-check-label">Assign As DH</label>
  </div>

  <div class="mb-3">
    <input type="text" name="note" class="form-control form-control-lg mb-2" placeholder="Note">
  </div>

  <button type="submit" class="btn btn-primary btn-lg w-100">Approve</button>
</form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@endsection



@push('scripts')
    @vite('resources/js/accounts/arrival_approval.js')
@endpush
