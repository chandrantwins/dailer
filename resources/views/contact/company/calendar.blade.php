@extends('layouts.app')

@section('content')
<div class="row">
	<div class="panel panel-primary">
		<div class="panel-heading">Contact Timezone</div>
		<div class="panel-body">
			{!! Form::open(array('route'=>['contact.postcalendar',$contact->id],'method'=>'POST','files'=>true)) !!}
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
							{!! Form::label('timezone','Timezone:')!!}
							<div class="form-group">
								{!! Form::select('timezoneindex',$timezones,null,['placeholder'=>'Select Timezone','class'=>'form-control','required'=>true,'id'=>'timezoneindex', 'onchange'=>'this.form.submit()']) !!}								
								{!! $errors->first('timezoneindex', '<p class="alert alert-danger">:message</p>')!!}
							</div>
						</div>
					</div>					
			</div>
		{!! Form::close() !!}
		</div>
	</div>	
</div>
<div id="here"></div>
<div class="row">
<h3> Appoinment Details</h3>
        @if(isset($calendar_details))		
                {!! $calendar_details->calendar() !!}                    
        @endif 		
</div>


<!-- calendar Modal -->


<div class="modal fade" tabindex="-1" role="dialog" id="eventmodal{{str_random(6)}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create new Appointment</h4>
            </div>
            <div class="modal-body">
            <form action="{{route('appointment.information')}}" method="post" id="appointmentinformation_form" name="{{$contact->id}}">
                <div class="form-group">
                    <label>First name:</label>
                    {!! Form::text('first_name',$contact->first_name,['placeholder'=>'First name','class'=>'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>Last name:</label>
                    {!! Form::text('last_name',$contact->last_name,['placeholder'=>'Last name','class'=>'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>Email address:</label>
                    {!! Form::email('email',$contact->email,['placeholder'=>'Email address','class'=>'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>Phone number:</label>
                    {!! Form::text('phone',$contact->phone,['placeholder'=>'Phone number','class'=>'form-control', 'id'=>'phone']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('name', 'Description') !!}
                    {!! Form::text('name', null, array('class' => 'form-control','placeholder' => 'Name','type' => 'text')) !!}
                </div>				
                <div class="form-group">
                    {!! Form::label('whenLocal', 'Appointment time') !!}
                    {!! Form::text('whenLocal', null, array('class' => 'form-control','placeholder' => 'Time of appointment','type' => 'text', 'id' => 'time-of-appointment-local')) !!}
                    {!! Form::hidden('when', null, array('id' => 'time-of-appointment')) !!}
                    {!! Form::hidden('timezoneOffset', null, array('id' => 'user-timezone')) !!}
                    {!! Form::hidden('timezone', null, array('id' => 'user-timezone-name')) !!}
                    {!! Form::hidden('phoneNumber', null, array('id' => 'phoneNumber')) !!}
                    {!! Form::hidden('delta', 15, array('id' => 'delta')) !!}
                </div>
            </form>				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-event">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@section('javascript')
<script src="{{  URL::asset('js/datetime-picker.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.13/moment-timezone-with-data.js"></script>

<script type="text/javascript">
    $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Whenever the user clicks on the "save" button om the dialog
            $('#save-event').on('click', function() {
            var appointmentInISOFormat = new Date($('input#time-of-appointment-local').val() + ' UTC').toISOString();
            $('input#time-of-appointment').val(appointmentInISOFormat);
            $('input#user-timezone').val(new Date().getTimezoneOffset());
            $('input#phoneNumber').val($('input#phone').val());
            $('input#user-timezone-name').val(moment.tz.guess());
            $alert_success = '<div class="alert alert-success">Thank you! Your Appointment successfully saved.</div>';
            $alert_danger = '<div class="alert alert-danger">Error!! Please contact your responsible</div>';
            var title = $('#name').val();
            $form = $('#appointmentinformation_form');
            var eventData = {
                title: title,
                start: $('#start_date').val(),
                end: $('#end_date').val()
            };				
            var data = $form.serializeArray();
            data.push({name: 'id', value: $form.attr('name')});
            data.push({name: 'timezoneindex', value: $('#timezoneindex').val()});
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: data,
                success: function(response) {
                    if(response.success){
                        window.location.href = response.url;
                    }
                }
            }).done(function(response) {
                $("div[id^='calendar']").fullCalendar('renderEvent', eventData, true); // stick? = true
                $('#here').html($alert_success);
            }).fail(function(response) {
                $('#here').html($alert_danger);
            });
            // hide modal
            $("div[id^='eventmodal']").modal('hide');
        });        
    });
</script>
@endsection