@extends('keen')
@section('content')

<div id="vue-app" class="container">
  <maid-attachment></maid-attachment>
  <maid-table-attachment> </maid-table-attachment>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
@endpush
