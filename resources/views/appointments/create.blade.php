@extends('layouts.app')

@section('content')
    @include('appointments._form', array('heading' => 'New appointment',
                                        'route' => 'appointments.store',
                                        'submitText' => 'Add',
                                        'method' => 'POST'))
@stop

@section('javascript')
    <script src="{{  URL::asset('js/datetime-picker.js') }}"></script>
@stop