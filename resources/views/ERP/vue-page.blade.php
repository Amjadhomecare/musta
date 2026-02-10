{{-- resources/views/ERP/vue-page.blade.php --}}
@extends('keen')
@section('content')

  
       
            <script>
                window.Laravel = {!! json_encode([
                    'user' => Auth::user(),
                ]) !!};
            </script>

            <div id="vue-app" data-page="{{ $vuePage }}">
                <page-loader></page-loader>
            </div>
     
@endsection

@push('scripts')
@vite(['resources/js/app.js'])
@endpush  
