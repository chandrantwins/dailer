@extends('layouts.app')

@section('style')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="clearfix">
            <h1 class="pull-left">Assign member to leaders</h1>
            <a href="{{route('users.create')}}" class="btn btn-primary pull-right">Create new User</a>
        </div>
    </div>
</div>
<hr>
<!-- /.row -->
<div class="row">
    <div class="col-xs-12">
        <div id="here"></div>
        @if ($alert = Session::get('alert'))
        <!-- will be used to show any messages -->
        <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
        @endif
    </div>
</div>
<!-- /.row -->
<div class="row">
    <div class="col-sm-6">
        <div id="here"></div>
        <form action="{{route('users.leaders.save')}}" method="post" id="leaders_form">
            <div class="form-group">
                <label>Leaders:</label>
                <select class="select2 form-control" name="leader" id="leader">
                    <option value=""></option>
                    @foreach($leaders as $leader)
                    <option value="<?= $leader->id ?>"><?= $leader->first_name.' '.$leader->last_name ?></option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Members:</label>
                <select class="select2 form-control" name="members[]" id="members" multiple>
                    @foreach($members as $member)
                    <option value="<?= $member->id ?>"><?= $member->first_name.' '.$member->last_name ?></option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <button type="button" class="btn btn-primary" id="assign-btn">Assign</button>
        </form>
    </div>
    <!-- ./col -->
    <div class="col-sm-6">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Full name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection

@section('javascript')
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
        //Initialize Select2 Elements
        $('#leader').select2({
            placeholder: "Select a leader",
            allowClear: true
        });

        $('#members').select2({
            placeholder: "Select a members",
            allowClear: true
        });

        if ($('#leader').val().length != 0) {
            getData($('#leader').val());
        }

        $('#leader').on('change',function(argument) {
            getData($(this).val());
        });

        function getData($user) {
            $.ajax({
                type: 'GET',
                url: '{{route('users.leaders.data')}}',
                data: {user: $user}
            }).done(function(response) {
                table.clear().draw();
                table.rows.add(response).draw();
            });
        }
        $('#assign-btn').on('click',function(event) {
            $alert_success = '<div class="alert alert-success">Thank you! Assignment has been saved successfully.</div>'
            
            $alert_danger = '<div class="alert alert-danger">Error!! Assignment has not been saved.</div>';
            $form = $('#leaders_form');

            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serializeArray()
            }).done(function(response) {
                $('#here').html($alert_success);
            }).fail(function(response) {
                $('#here').html($alert_danger);
            }).always(function () {
                setTimeout(function(){ location.reload(); }, 5000);
            });
        });
    });
</script>
@endsection