@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="panel panel-primary">
			<div class="panel-heading">Closer Events</div>
			<div class="panel-body">
				{!! Form::open(array('route'=>'events.add','method'=>'POST','files'=>true)) !!}
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12">
						@if(Session::has('success'))
							<div class="alert alert-success">{{Session::get('success') }}</div>
						@elseif (Session::has('warning'))
							<div class="alert alert-danger">{{Session::get('warning') }}</div>
						@endif
					</div>
					<div class="col-xs-4 col-sm-4 col-md-4">
						<div class="form-group">
							{!! Form::label('event_name','Event Name:')!!}
							<div class="">
								{!! Form::text('event_name',null,['class'=>'form-control'])!!}
								{!! $errors->first('event_name', '<p class="alert alert-danger">:message</p>')!!}
							</div>
						</div>
					</div>
					<div class="col-xs-3 col-sm-3 col-md-3">
						<div class="form-group">
							{!! Form::label('start_date','Start Date:')!!}
							<div class="">
								{!! Form::date('start_date',null,['class'=>'form-control'])!!}
								{!! $errors->first('start_date', '<p class="alert alert-danger">:message</p>')!!}
							</div>
						</div>
					</div>
					<div class="col-xs-3 col-sm-3 col-md-3">
						<div class="form-group">
							{!! Form::label('end_date','End Date:')!!}
							<div class="">
								{!! Form::date('end_date',null,['class'=>'form-control'])!!}
								{!! $errors->first('end_date', '<p class="alert alert-danger">:message</p>')!!}
							</div>
						</div>
					</div>
					<div class="col-xs-1 col-sm-1 col-md-1 text-center"> &nbsp;<br/>
						{!! Form::submit('Add Event', ['class'=>'btn btn-primary'])!!}
					</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>	
	<div class="panel panel-primary">
		<div class="panel-heading"> Event Details</div>
		<div class="panel-body">                    
			{!! $calendar_details->calendar() !!}
		</div>
                <!-- Modal -->
	<div class="modal fade" id="closerModal" tabindex="-1" role="dialog" aria-labelledby="closerModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="closerModalLabel">Details</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="closer_modal">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>	        
				</div>
			</div>
		</div>
	</div>
	</div>
<script type="text/javascript">
	function showModal(x) {
		console.log(x);
		var html = '<strong>' + x.title + '</strong>';
		var aux = new Date(x.start.toString());
		html = html + '<br><strong>Start Date: </strong>' + ((aux.getDate() < 10? '0' : '') + aux.getDate()) + '/' + ((aux.getMonth() < 9? '0' : '') + (aux.getMonth() + 1)) + '/' + aux.getFullYear();		
		var aux = new Date(x.end.toString());
		html = html + '<br><strong>End Date: </strong>' + ((aux.getDate() < 10? '0' : '') + aux.getDate()) + '/' + ((aux.getMonth() < 9? '0' : '') + (aux.getMonth() + 1)) + '/' + aux.getFullYear();
		$("#closer_modal").html(html);
		$("#closerModal").modal('show');
		// change the day's background color just for fun
		$(this).css('background-color', 'red');
	}
</script>	
@endsection