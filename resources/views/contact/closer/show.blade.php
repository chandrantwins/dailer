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
                    <th width="20%">Company name</th>
                    <td>{{$contact->company_name}}</td>
                </tr>
                <tr>
                    <th width="20%">Position</th>
                    <td>{{$contact->position}}</td>
                </tr>
                <tr>
                    <th width="20%">Title</th>
                    <td>{{$contact->title}}</td>
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
                    <th width="20%">Note: </th>
                    <td>
                        @php
                            echo '<ui>';
                            foreach($contact->calls as $call){
                                if(!is_null($call->note)){
                                    echo '<li>'.$call->note.'</li>';
                                }
                            }
                            echo '</ul>'
                        @endphp                        
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
        <a class="btn btn-default pull-right" href="{{ route('contact.closercalendar',['id'=>$contact->id,'appointmentid'=>$appointment->id]) }}">ReSchedule</a>
    </div>
</div>

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
        $('#information').on('click',function(event) {
            $alert_success = '<div class="alert alert-success">Thank you! Your update successfully saved.</div>'
            
            $alert_danger = '<div class="alert alert-danger">Error!! Please contact your responsible</div>';
            $form = $('#information_form');
            var data = $form.serializeArray();
            data.push({name: 'appointmentid', value: '{{$appointment->id}}'});
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: data,
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