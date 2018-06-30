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
                    <td>Company name</td>
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
                    <td>{{$call->company_name}}</td>
                    <td>{{$call->phone}}</td>
                    <td>{{$call->updated_at}}</td>
                    <td>
<?php
$remind_me = $call->remind_me_at - (new Carbon\Carbon($call->updated_at))->diffInSeconds(Carbon\Carbon::now());
$ago = false;
if($remind_me<0) {
    $remind_me *= -1;
    $ago = true;
}
?>
                        <?= sprintf('%d days %02d:%02d:%02d', $remind_me/86400, $remind_me/3600%24, $remind_me/60%60, $remind_me%60); ?><?= ($ago)?' ago':''; ?>
                    </td>
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