@extends('layouts.app')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('vendor/bootstrap-daterangepicker/daterangepicker.css')}}">
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <h3 class="pull-left">Last 24 Hours</h3>
        </div>
        <hr>
        <div class="col-xs-12 col-sm-3">
            <label>User Type:</label>
            {!! Form::select('usertype',['candidate'=>'Candidate','company'=>'Company','closer'=>'Closer','reqruited'=>'Reqruited'],null,['placeholder'=>'Select UserType','class'=>'form-control select2','id'=>'usertypes']) !!}
        </div>        
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Name</td>
                    <td>Total Calls</td>
                    <td>Followup Calls</td>
                    <td>Appointments</td>
                    <td>Success</td>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
@endsection
@section('javascript')
    <script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable Element
        table.destroy();
        table = $('.table').DataTable();

        if ($('#usertypes').val().length != 0) {
            getData($('#usertypes').val());
        }

        $('#usertypes').on('change',function(argument) {
            getData($(this).val());
        });

        function getData($type) {
            $.ajax({
                type: 'GET',
                url: '{{route('reporting.todaystatus.data')}}',
                data: {type: $type}
            }).done(function(response) {
                table.clear().draw();
                table.rows.add(response).draw();
            });
        }
    });        
    </script>
@endsection