@extends('layouts.app')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <h3 class="pull-left">Calls</h3>
        </div>
        <hr>
        <div class="col-xs-12 col-sm-offset-3 col-sm-3">
            <label>Caller:</label>
            {!! Form::select('caller',$callers,null,['placeholder'=>'Select caller','class'=>'form-control select2','id'=>'callers']) !!}
        </div>
    </div>
</div>
<!-- /.row -->
<hr>
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
    <div class="col-xs-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Caller username</td>
                    <td>Contact name</td>
                    <td>Business name</td>
                    <td>Phone number</td>
                    <td>Email</td>
                    <td>Date time</td>
                    <td>Note</td>
                    <td>Result</td>
                    <td>Actions</td>
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
<!-- Select2 -->
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable Element
        table.destroy();
        table = $('.table').DataTable();

        // Initialize Select2 Elements
        $('.select2').select2();
        if ($('#callers').val().length != 0) {
            getData($('#callers').val());
        }

        $('#callers').on('change',function(argument) {
            getData($(this).val());
        });

        function getData($user) {
            $.ajax({
                type: 'GET',
                url: '{{route('reporting.calls.data')}}',
                data: {user: $user}
            }).done(function(response) {
                table.clear().draw();
                table.rows.add(response).draw();
            });
        }
    });
</script>
@endsection