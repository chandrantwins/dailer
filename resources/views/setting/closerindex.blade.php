@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.2.17/jquery.timepicker.min.css"/>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery.businessHours/1.0.1/jquery.businessHours.css"/>

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
        <h3>Working Timezone config</h3>
        <hr>
		<select class="select_timezone form-group">
			<option value="">Select Timezone</option>
			<option value="5">Pacific Standard Time (UTC - 8)</option>
			<option value="9">Mountain Standard Time (UTC - 7)</option>
			<option value="13">Central Standard Time (UTC - 6)</option>
			<option value="16">Eastern Standard Time (UTC - 5)</option>
		</select>
	</div>
    <div class="col-md-6 col-xs-12">
        <h3>Working Hours config</h3>
        <hr>
        {!! Form::open(['route' => 'closer.setting','method'=>'POST']) !!}
			<div id="container" class="container">
				<div>
					<div id="businessHoursContainer3"></div>					
				</div>	  
			</div>
        {!! Form::close() !!}
    </div>
	<div class="col-md-12 col-xs-12">
		<div class="col-xs-12 text-center form-group">
			<button type="submit" class="btn btn-primary pull-left">Save</button>
		</div>
	</div>
</div>
<!-- /.row -->
@endsection
@section('javascript')
<script type="text/javascript">
    $(function () {
		var businessHoursManager = $("#businessHoursContainer3").businessHours();
		$("#btnSerialize").click(function() {
			$("textarea#businessHoursOutput1").val(JSON.stringify(businessHoursManager.serialize()));
		}); 
        Rainbow.color();
        var b3 = $("#businessHoursContainer3");
        var businessHoursManagerBootstrap = b3.businessHours({
            postInit: function () {
                b3.find('.operationTimeFrom, .operationTimeTill').timepicker({
                    'timeFormat': 'H:i',
                    'step': 15
                });
            },
            dayTmpl: '<div class="dayContainer" style="width: 80px;">' +
            '<div data-original-title="" class="colorBox"><input type="checkbox" class="invisible operationState"/></div>' +
            '<div class="weekday"></div>' +
            '<div class="operationDayTimeContainer">' +
            '<div class="operationTime input-group">' +
                '<span class="input-group-addon">' +
                    '<i class="fa fa-sun-o"></i>' +
                '</span>' +
            '<input type="text" name="startTime" class="mini-time form-control operationTimeFrom" value=""/></div>' +
            '<div class="operationTime input-group">' +
            '<span class="input-group-addon"><i class="fa fa-moon-o"></i></span><input type="text" name="endTime" class="mini-time form-control operationTimeTill" value=""/></div>' +
            '</div></div>'
        });
    })
</script>
@endsection