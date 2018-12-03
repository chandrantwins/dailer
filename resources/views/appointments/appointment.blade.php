@extends('layouts.app')

@section('content')
<h2> {{ $pagename }} Appointments </h2>
@if(count($appointments) > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Created By</th>
                <th>Appointed To</th>
                <th>Caller Name</th>
                <th>Phone Number</th>
                <th>Appointment time (UTC)</th>
            </tr>
        </thead>
        <tbody>
        @foreach($appointments as $apt)
            <tr>
                <td> {{ $apt->companyuser->first_name.' '.$apt->companyuser->last_name}}</td>
                <td> {{ $apt->closeruser->first_name .' '.$apt->closeruser->last_name}}</td>
                <td> {{ $apt->contact->first_name .' '.$apt->contact->last_name}}</td>
                <td> {{ $apt->phoneNumber }}</td>
                <td> {{ $apt->notificationTime }}</td>                
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div class="well"> There are no appointments to display </div>
@endif

@stop