@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="clearfix">
            <h1 class="pull-left">Import Contacts</h1>
        </div>
        <hr>
        <!-- will be used to show any messages -->
        @if ($alert = Session::get('alert'))
        <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
        @endif
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<div class="row">
    <div class="col-sm-6 col-xs-12 text-center">
        Download template CSV file for Company <a href="{{asset('import/template/company.csv')}}" class="btn btn-default">HERE</a>
    </div>
    <!-- ./col -->
    <div class="col-sm-6 col-xs-12 text-center">
        Download template CSV file for Candidate <a href="{{asset('import/template/candidate.csv')}}" class="btn btn-default">HERE</a>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>
<div class="row">
    {!! Form::open(['route' => 'contact.import','method'=>'POST','files'=>true]) !!}
        <div class="col-xs-12 col-sm-offset-3 col-sm-3 form-group">
            <label>Contact type:</label>
            {!! Form::select('type', $types,null,['placeholder'=>'Select type','class'=>'form-control']) !!}
        </div>
        <div class="col-xs-12 col-sm-3 form-group">
            <label>Upload file (*.CSV):</label>
            {!! Form::file('file',['required'=>true,'accept'=>'.csv, text/csv']) !!}
        </div>
        <div class="col-xs-12 text-center form-group">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    {!! Form::close() !!}
</div>
<!-- /.row -->
@endsection
