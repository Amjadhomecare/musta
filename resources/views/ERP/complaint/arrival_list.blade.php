@extends('keen')
@section('content')

<div class="container mt-5">
@if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    Add New Arrival
                   
     
                </div>
                <div class="card-body">
                   <form action="{{ route('storeMaidArrive') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="maid_id">Name</label>
                            <select class="form-control" id="maid_id" name="maid_id" required></select>
                        </div>
                        <div class="form-group">
                            <label for="nationality">Nationality</label>
                            <input readonly type="text" class="form-control" id="nationality" name="nationality" required>
                        </div>
                        <div class="form-group">
                            <label for="agent">Agent</label>
                            <input readonly type="text" class="form-control" id="agent" name="agent" required>
                        </div>
                        <div class="form-group">
                            <label for="note">Note</label>
                            <textarea class="form-control" id="note" name="note"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">
                <table id="arrival_table" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                          <th>Name</th>
                            <th>Nationality</th>
                            <th>Agent</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>




@endsection

@push('scripts')
    @vite('resources/js/complain/arrival.js')
@endpush

