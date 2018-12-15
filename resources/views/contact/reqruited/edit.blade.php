@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left"><h2>Edit Contact: Reqruited</h2></div>
        <div class="pull-right"><a class="btn btn-primary" href="{{ route('contact.index',['type'=>'candidate']) }}"> Back</a></div>
    </div>
</div>
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
{!! Form::model($contact, ['method' => 'POST','route' => ['contact.update', $contact->id]]) !!}
@include('contact.candidate.form')
{!! Form::close() !!}
@endsection