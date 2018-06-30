@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Show Contact</h2>
        </div>
        @if (\App\User::ADMIN == Auth::user()->role)
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('users.index',['type'=>$contact->type]) }}"> Back</a>
        </div>
        @endif
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
        <div id="here"></div>
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th width="20%">Contact name</th>
                    <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                </tr>
                <tr>
                    <th width="20%">Position</th>
                    <td>{{$contact->position}}</td>
                </tr>
                <tr>
                    <th width="20%">Email address</th>
                    <td>{{$contact->email}}</td>
                </tr>
                <tr>
                    <th width="20%">City position is in</th>
                    <td>{{$contact->city_position}}</td>
                </tr>
                <tr>
                    <th width="20%">Phone number</th>
                    <td>{{$contact->phone}}</td>
                </tr>
                <tr>
                    <th width="20%">Job description</th>
                    <td>{{$contact->description}}</td>
                </tr>
                <tr>
                    <th width="20%">Note: </th>
                    <td>
                        @if(Session('call'))
                            {{ Session('call')->note."\n" }}
                        @endif
                        {{$contact->note}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12">
        <a class="btn btn-info" href="{{ route('contact.question',$contact->id) }}">Next page</a>        
        <a class="btn btn-default pull-right" data-toggle="modal" data-target="#email_modal">Send email or update the contact</a>
        <a class="btn btn-default pull-right" data-toggle="modal" data-target="#calendar_modal">Schedule</a>
    </div>
</div>
<!-- calendar Modal -->
<div class="modal fade" id="calendar_modal" tabindex="-1" role="dialog" aria-labelledby="Email" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
	<div class="panel panel-primary">
		<div class="panel-heading"> Event Details</div>
		<div class="panel-body"> 
		@if(isset($calendar_details))		
			{!! $calendar_details->calendar() !!}                    
		@endif
		</div>
        </div>
      </div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-warning" id="information">Assign</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="eventmodal{{str_random(6)}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create new event</h4>
            </div>
            <div class="modal-body">
			<form action="{{route('contact.eventinformation')}}" method="post" id="eventinformation_form" name="{{$contact->id}}">
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
                    {!! Form::text('phone',$contact->phone,['placeholder'=>'Phone number','class'=>'form-control']) !!}
                </div>
				<div class="form-group">
                    <label>Event title:</label>
                    {!! Form::text('event_name','',['placeholder'=>'Event title','class'=>'form-control', 'id'=>'event_name']) !!}
                </div>
				<div class="form-group">
                    <label>Starts at:</label>
                    {!! Form::text('start_date','',['placeholder'=>'Starts at','class'=>'form-control', 'id'=>'start_date']) !!}
                </div>
				<div class="form-group">
                    <label>Ends at:</label>
                    {!! Form::text('end_date','',['placeholder'=>'Ends at','class'=>'form-control', 'id'=>'end_date']) !!}
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

<!-- Modal -->
<div class="modal fade" id="email_modal" tabindex="-1" role="dialog" aria-labelledby="Email" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
            <h3>Is this information correct? If not, please change it here:</h3>
            <hr>
            <form action="{{route('contact.information',$contact->id)}}" method="get" id="information_form">
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
                    {!! Form::text('phone',$contact->phone,['placeholder'=>'Phone number','class'=>'form-control']) !!}
                </div>
            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="email">Send</button>
        <button type="button" class="btn btn-warning" id="information">Update</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
    $(function () {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
        $('#calendar_modal').on('shown.bs.modal', function () {
            $("div[id^='calendar']").fullCalendar('render');
            if($(".select_timezone").length == 0){
                $(".fc-right").append('<select class="select_timezone form-group"><option value="">Select Timezone</option><option value="5">Pacific Standard Time (UTC - 8)</option><option value="9">Mountain Standard Time (UTC - 7)</option><option value="13">Central Standard Time (UTC - 6)</option><option value="16">Eastern Standard Time (UTC - 5)</option></select>');
                $(".select_timezone").on("change", function(event) {
                        if(this.value != ''){
							$.get('/contact/events/events-json/'+this.value, function (data) {
								//success data
								console.log(data);
								//$("div[id^='calendar']").fullCalendar('destroy');								
							}) 
								//$('div[id^=calendar]').fullCalendar('changeView', 'agendaWeek', this.value);
								//$('div[id^=calendar]').fullCalendar({
								//	editable: false,
								//	events: '/contact/events/events-json/'+this.value
								//});
                                
                                //$('div[id^=calendar]').fullCalendar('gotoDate', "2018-"+this.value+"-1");
                        }
                });
            }
        });
        // Bind the dates to datetimepicker.
        // You should pass the options you need
        $("#start_date, #end_date").datetimepicker({format: 'YYYY-MM-DD HH:MM:SS'});
        // Whenever the user clicks on the "save" button om the dialog
        $('#save-event').on('click', function() {
			$alert_success = '<div class="alert alert-success">Thank you! Your update successfully saved.</div>';
			$alert_danger = '<div class="alert alert-danger">Error!! Please contact your responsible</div>';
            var title = $('#event_name').val();
            if (title) {
				$form = $('#eventinformation_form');
                var eventData = {
                    title: title,
                    start: $('#start_date').val(),
                    end: $('#end_date').val()
                };				
				var data = $form.serializeArray();
				data.push({name: 'id', value: $form.attr('name')});				
				$.ajax({
					type: $form.attr('method'),
					url: $form.attr('action'),
					data: data,
				}).done(function(response) {
					$("div[id^='calendar']").fullCalendar('renderEvent', eventData, true); // stick? = true
					$('#here').html($alert_success);
				}).fail(function(response) {
					$('#here').html($alert_danger);
				});
                
            }
            // hide modal
            $("div[id^='eventmodal']").modal('hide');
        });
        
        $('#information').on('click',function(event) {
            $alert_success = '<div class="alert alert-success">Thank you! Your update successfully saved.</div>'
            
            $alert_danger = '<div class="alert alert-danger">Error!! Please contact your responsible</div>';
            $form = $('#information_form');
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serializeArray(),
            }).done(function(response) {
                $('#here').html($alert_success);
            }).fail(function(response) {
                $('#here').html($alert_danger);
            });
        });

        $('#email').on('click',function(event) {
            $alert_success = '<div class="alert alert-success">Thank you! Your message has been sent successfully.</div>'
            
            $alert_danger = '<div class="alert alert-danger">Your message has not been sent. Please contact your responsible</div>';
            $.ajax({
                type: 'GET',
                url: '{{route('contact.email')}}',
                data: {contact:'{{$contact->id}}',email:$('input[name="email"]').val()}
            }).done(function(response) {
                if (response == 1) {
                    $('#here').html($alert_success);
                } else {
                    $('#here').html($alert_danger);
                }
            }).fail(function(response) {
                $('#here').html($alert_danger);
            }).always(function(response) {
                $('#email_modal').modal('hide');
            });
        });
    });
</script>
@endsection