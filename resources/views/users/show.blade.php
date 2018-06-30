@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Show User</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('users.index') }}"> Back</a>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
        <table class="table table-hover">
            <tbody>
            <tr>
                <th width="20%">Full name: </th>
                <td>{{$user->first_name}} {{$user->last_name}}</td>
            </tr>
            <tr>
                <th width="20%">User type: </th>
                <td>{{$user->role}}</td>
            </tr>
            <tr>
                <th width="20%">Affiliate link: </th>
                <td>{{$user->affiliate}}</td>
            </tr>
            <tr>
                <th width="20%">Username: </th>
                <td>{{$user->username}}</td>
            </tr>
            <tr>
                <th width="20%">Email address: </th>
                <td>{{$user->email}}</td>
            </tr>
            <tr>
                <th width="20%">Phone number: </th>
                <td>{{$user->phone}}</td>
            </tr>
            <tr>
                <th width="20%">Added date: </th>
                <td>{{$user->created_at}}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection