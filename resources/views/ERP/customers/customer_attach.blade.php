@extends('keen')
@section('content')

<div id="vue-app" class="container">
  <customer-attachment></customer-attachment>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
@endpush
