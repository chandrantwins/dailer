@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <h2 class="pull-left">Follow ups Contacts</h2>
    </div>
</div>
<hr>
<!-- /.row -->
<div class="row">
    <div class="col-sm-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Email address</td>
                    <td>Phone number</td>
                    <td>Last call</td>
                    <td>Call me in</td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
            @foreach($calls as $call)
                <tr>
                    <td>{{$call->first_name}} {{$call->last_name}}</td>
                    <td>{{$call->email}}</td>
                    <td>{{$call->phone}}</td>
                    <td>{{\Carbon\Carbon::parse($call->updated_at)->format('d-m-Y h:i:s A')}}</td>
                    <td>{{$call->originalremindmeat}} ({{\Carbon\Carbon::createFromTimeStamp(strtotime($call->remind_me_at))->diffForHumans()}})</td>
                    <td>
                        <a class="btn btn-info" href="{{route('contact.follow-ups',['contact'=>$call->contact_id, 'call'=>$call->call_id])}}">Call me</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection