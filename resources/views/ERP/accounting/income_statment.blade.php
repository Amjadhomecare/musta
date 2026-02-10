@extends('keen')
@section('content')

<div id="vue-app" class="container">
  <income-statement></income-statement>
</div>
@endsection

@push('scripts')
  
 @vite('resources/js/app.js')

@endpush
