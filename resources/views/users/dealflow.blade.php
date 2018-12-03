@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <h3 class="pull-left">Deal Flow</h3>
        </div>
        <hr>
        <div class="col-xs-12 col-sm-3">
            <label>User Type:</label>
            {!! Form::select('usertype',['monikl'=>'Monikl','reqruited'=>'Reqruited'],null,['placeholder'=>'Select UserType','class'=>'form-control select2','id'=>'usertypes']) !!}
        </div>        
    </div>
</div>
<hr>
<div id="funnel"></div>
@endsection
@section('javascript')
    <script type="text/javascript">
        //https://jsfiddle.net/LukaszWiktor/wtsk2L97/
        var data = [
            ["Total Calls", 0],
            ["Answered Calls", 0],
            ["Followup and Appointment Calls", 0],
            ["Success Calls", 0]
        ];
        var chart = new D3Funnel("#funnel");
        var options = {
          chart: {
            width: 800,
            animate: 200,
            bottomWidth: 3 / 8,
            bottomPinch: 1,
          },
          block: {
            dynamicHeight: false,
          },
        };
        
        if ($('#usertypes').val().length != 0) {
            getData();
        }

        $('#usertypes').on('change',function(argument) {
            getData();
        });
        
        function getData() {
            var usertype = $('#usertypes').val();

            $.ajax({
                type: 'GET',
                url: '{{route('reporting.dealflow.data')}}',
                data: {usertype: usertype}
            }).done(function(response) {
                var resdata = [
                    ["Total Calls", response.totalcalls],
                    ["Answered Calls", response.answeredcalls],
                    ["Followup and Appointment Calls", response.followupcalls],
                    ["Success Calls", response.successcalls]
                ];
                chart.draw(resdata, options);
            }).fail(function() {
                chart.draw(data, options);
            });
        }
    </script>
@endsection