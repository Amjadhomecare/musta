@extends('keen')
@section('content')
<div class="container mt-5">
    <h2>Upload CSV File for Bulk JV</h2>

    <!-- Display success or error messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- CSV Upload Form -->
    <form action="{{ route('bulk_jv.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
            <label for="csv_file" class="form-label">Select CSV File</label>
            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv, .txt" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection

