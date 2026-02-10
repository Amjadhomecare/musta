@extends('keen')

@section('content')
<div class="container mt-5">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-bold">
                    Register a ran away or Released maid
                </div>

                <div class="card-body">
                    <form action="{{ route('storeMaidRelease') }}" method="POST">
                        @csrf
                            <input type="hidden" id="maid_id" name="maid_id">

                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <select class="form-control" id="name" name="name" required></select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="nationality">Nationality</label>
                            <input readonly type="text" class="form-control" id="nationality" name="nationality" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="agent">Agent</label>
                            <input readonly type="text" class="form-control" id="agent" name="agent" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_status">Reason</label>
                            <select class="form-control" id="new_status" name="new_status" required>
                                <option value="ran away inside guaranty">ran away inside guaranty</option>
                                <option value="ran away outside guaranty">ran away outside guaranty</option>
                                <option value="send back agent">send back agent(charge the agent)</option>
                                <option value="transferred">transferred</option>
                                <option value="released">released(this mean no charge to the agent)</option>
                                <option value="visa rejected">Visa rejected (charge the agent the vist visa)</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="note">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Listing --}}
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="release_table" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Nationality</th>
                            <th>Agent</th>
                            <th>Note</th>
                            <th>New Status</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    {{-- DataTables will fill tbody --}}
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
 
    @vite(['resources/js/complain/release.js'])
@endpush
