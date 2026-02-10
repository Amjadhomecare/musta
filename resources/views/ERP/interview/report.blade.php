@extends('keen')
@section('content')

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto  p-6 shadow rounded">
            <div id="vue-app">
                <maid-interview></maid-interview>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@vite(['resources/js/app.js'])

@endpush