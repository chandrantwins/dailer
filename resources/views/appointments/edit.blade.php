@extends('layouts.app')

@section('content')
    @include('appointments._form', array('heading' => 'Edit appointment',
                                        'route' => array('appointments.update', $appointment->id),
                                        'submitText' => 'Edit',
                                        'method' => 'PUT'))
@stop

@section('javascript')
    <script src="{{  URL::asset('js/datetime-picker.js') }}"></script>
@stop