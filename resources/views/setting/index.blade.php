@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Settings</h2>
        </div>
    </div>
</div>
<hr>
<!-- /.row -->
@if ($alert = Session::get('alert'))
<div class="row">
    <div class="col-xs-12">
    <!-- will be used to show any messages -->
    <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
    </div>
</div>
<!-- /.row -->
@endif
<div class="row">
    <div class="col-md-6 col-xs-12">
        <table class="table">
            <tbody>
                {{ $empty = false }}
                @foreach($settings as $key=>$setting)
                @if ("mail_" == substr($setting->key,0,5))
                <tr>
                    <th width="30%">{{$setting->name}}:</th>
                    <td width="70%">{{$setting->value}}</td>
                </tr>
                <tr><td width="30%"></td><td width="70%"></td></tr>
                @endif
                @endforeach
            </tbody>
        </table>
        <a href="{{route('setting.check')}}" class="btn btn-info" id="check_smtp">Check SMTP Connection</a>
    </div>
    <div class="col-md-6 col-xs-12" style="border-left: 2px solid black;">
        <h3>SMTP config</h3>
        <hr>
        {!! Form::open(['route' => 'setting.index','method'=>'POST']) !!}
            @foreach($settings as $key=>$setting)
            @if ("mail_" == substr($setting->key,0,5))
            @if ($setting->key != 'mail_encryption')
            <div class="col-xs-12 form-group">
                <label>{{$setting->name}}:</label>
                {!! Form::text($setting->key,$setting->value,['placeholder'=>$setting->name,'class'=>'form-control','required'=>true]) !!}
            </div>
            @else
            <div class="col-xs-12 form-group">
                <label>{{$setting->name}}:</label>
                {!! Form::select($setting->key,$encryptions,$setting->value,['placeholder'=>'Select encryption','class'=>'form-control','required'=>true]) !!}
            </div>
            @endif
            @endif
            @endforeach
            <div class="col-xs-12 text-center form-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        {!! Form::close() !!}
    </div>
    <div class="col-md-6 col-xs-12">
        <h3>Payout config</h3>
        <hr>
        {!! Form::open(['route' => 'setting.index','method'=>'POST']) !!}
            @foreach($settings as $key=>$setting)
            @if ("payout_" == substr($setting->key,0,7))
            <div class="col-xs-12 form-group">
                <label>{{$setting->name}}:</label>
                {!! Form::text($setting->key,$setting->value,['placeholder'=>$setting->name,'class'=>'form-control','required'=>true]) !!}
            </div>
            @endif
            @endforeach
            <div class="col-xs-12 text-center form-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        {!! Form::close() !!}
    </div>
    <div class="col-md-6 col-xs-12">
        <h3>App config</h3>
        <hr>
        {!! Form::open(['route' => 'setting.index','method'=>'POST']) !!}
            @foreach($settings as $key=>$setting)
            @if ("app_" == substr($setting->key,0,4))
            <div class="col-xs-12 form-group">
                <label>{{$setting->name}}:</label>
                {!! Form::text($setting->key,$setting->value,['placeholder'=>$setting->name,'class'=>'form-control','required'=>true]) !!}
            </div>
            @endif
            @endforeach
            <div class="col-xs-12 text-center form-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.row -->
@endsection
