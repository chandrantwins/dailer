@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <!-- small box -->
        <div class="small-box bg-red col-xs-12 col-sm-5 ">
            <div class="inner">
                <h3 class="text-center">{{$blacklist->where('contacts.type', 'company')->where('calls.answer', \App\Call::ANSWER_UNSUCCESSFUL)->count()}}</h3>
                <p class="text-center">Call answered but unsuccessful</p>
            </div>
        </div>
        <!-- small box -->
        <div class="small-box bg-red col-xs-12 col-sm-offset-1 col-sm-5">
            <div class="inner">
                <h3 class="text-center">{{$blacklist->where('contacts.type', 'company')->where('calls.answer', \App\Call::ANSWER_ASKED_REMOVED)->count() + $blacklist->where('contacts.type', 'candidate')->where('calls.answer', 5)->count()}}</h3>
                <p class="text-center">Call answered but asked to be removed</p>
            </div>
        </div>
        <!-- small box -->
        <div class="small-box bg-red col-xs-12 col-sm-5">
            <div class="inner">
                <h3 class="text-center">{{$blacklist->where('contacts.type', 'company')->where('calls.answer', \App\Call::ANSWER_WRONG_NUMBER)->count()}}</h3>
                <p class="text-center">Wrong number</p>
            </div>
        </div>
        <!-- small box -->
        <div class="small-box bg-red col-xs-12 col-sm-offset-1 col-sm-5">
            <div class="inner">
                <h3 class="text-center">{{$blacklist->count()}}</h3>
                <p class="text-center">Total Blacklist</p>
            </div>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-xs-12 col-sm-6">
        <div class="clearfix">
            <h3 class="pull-left">Blacklist</h3>
        </div>
        <hr>
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Type</td>
                    <td>Phone number</td>
                    <td>Result</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
            @foreach($blacklist as $call)
                <tr>
                    <td>{{$call->contact()->first()->first_name}} {{$call->contact()->first()->last_name}}</td>
                    <td>{{$call->contact()->first()->type}}</td>
                    <td>{{$call->contact()->first()->phone}}</td>
                    <td>{{$answer_is[$call->contact()->first()->type][$call->answer]}}</td>
                    <td>{{ \Carbon\Carbon::parse($call->update_at)->toDateTimeString()}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection