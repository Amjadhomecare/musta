@extends('keen')
@section('content')

<div id="vue-app" class="container">
  <general-report></general-report>
</div>
@endsection

@push('scripts')
  
 @vite('resources/js/app.js')

@endpush
