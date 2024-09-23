@extends('layouts.app')

@section('content')
    cabinet index

@endsection



@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')

@endpush

{{--@push('modals')
    @include('partials.modals.tickets.create')
@endpush--}}

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>
@endpush
