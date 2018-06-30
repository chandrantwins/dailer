@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Show Email</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('emails.index') }}"> Back</a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
        <table class="table table-hover">
            <tbody>
            <tr>
                <th width="20%">Subject: </th>
                <td>{{$email->subject}}</td>
            </tr>
            <tr>
                <th width="20%">Message: </th>
                <td>{!!$email->message!!}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection