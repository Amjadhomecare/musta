@extends('keen')
@section('content')

<div id="vue-app" class="container">

  <subscription-live> </subscription-live>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
@endpush
