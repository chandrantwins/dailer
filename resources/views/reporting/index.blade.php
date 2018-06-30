@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-offset-3 col-sm-6 form-group">
        <label>Caller:</label>
        {!! Form::select('caller', $callers, null, ['id'=>'caller','class'=>'form-control']) !!}
    </div>
</div>
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{$successful->count()}}</h3>
                <p>Success ranking</p>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{$gatekeeper->count()}}</h3>
                <p>Gatekeeper</p>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{$asked_removed->count()}}</h3>
                <p>Asked to be removed</p>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{$blacklist->count()}}</h3>
                <p>Blacklist</p>
            </div>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="clearfix">
            <h3 class="pull-left">Success ranking last 10</h3>
        </div>
        <hr>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Type</td>
                    <td>Phone number</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
            @foreach($successful as $element)
                <tr>
                    <td>{{$element->contact()->first()->contact_name}}</td>
                    <td>{{$element->contact()->first()->type}}</td>
                    <td>{{$element->contact()->first()->phone}}</td>
                    <td>{{ \Carbon\Carbon::parse($element->update_at)->toDateTimeString()}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
    <div class="col-xs-12 col-sm-6">
        <div class="clearfix">
            <h3 class="pull-left">Blacklist last 10</h3>
        </div>
        <hr>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Type</td>
                    <td>Phone number</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
            @foreach($blacklist as $element)
                <tr>
                    <td>{{$element->contact()->first()->contact_name}}</td>
                    <td>{{$element->contact()->first()->type}}</td>
                    <td>{{$element->contact()->first()->phone}}</td>
                    <td>{{ \Carbon\Carbon::parse($element->update_at)->toDateTimeString()}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection