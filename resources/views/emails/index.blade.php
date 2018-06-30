@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="clearfix">
            <h1 class="pull-left">All the Emails</h1>
            <a href="{{route('emails.create')}}" class="btn btn-primary pull-right">Create new Email</a>
        </div>
        <hr>
        <!-- will be used to show any messages -->
        @if ($alert = Session::get('alert'))
        <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
        @endif
        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <td width="15%">Subject</td>
                    <td width="15%">Type</td>
                    <td width="50%">Message</td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
            @foreach($emails as $email)
                <tr>
                    <td>{{$email->subject}}</td>
                    <td>{{$email->type}}</td>
                    <td class="text-overflow">{!!$email->message!!}</td>
                    <!-- we will also add show, edit, and delete buttons -->
                    <td>
                        <a class="btn btn-info btn-xs" href="{{route('emails.show',$email->id)}}">Show</a>
                        <a class="btn btn-primary btn-xs" href="{{route('emails.edit',$email->id)}}">Edit</a>
                        {!! Form::open(['method' => 'DELETE','route' => ['emails.destroy', $email->id],'style'=>'display:inline']) !!}
                        {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-xs']) !!}
                        {!! Form::close() !!}
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