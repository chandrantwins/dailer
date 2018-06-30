@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Show Contact</h2>
        </div>
        @if (\App\User::ADMIN == Auth::user()->role)
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('users.index',['type'=>'candidate']) }}"> Back</a>
        </div>
        @endif
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-8">
        <div id="here"></div>
        {{$contact->type}}
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
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="email_modal" tabindex="-1" role="dialog" aria-labelledby="Email" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="form-group">
            <h3>Is this information correct? If not, please change it here:</h3>
            <hr>
            <form action="{{route('contact.information',$contact->id)}}" method="get" id="information_form">
                <div class="col-xs-12 col-md-6">
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label>First name:</label>
                            {!! Form::text('first_name',$contact->first_name,['placeholder'=>'First name','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Last name:</label>
                            {!! Form::text('last_name',$contact->last_name,['placeholder'=>'Last name','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Email address:</label>
                            {!! Form::email('email',$contact->email,['placeholder'=>'Email address','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Job description:</label>
                            {!! Form::textarea('description',$contact->description,['placeholder'=>'Job description','class'=>'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label>Phone number:</label>
                            {!! Form::text('phone',$contact->phone,['placeholder'=>'Phone number','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Position:</label>
                            {!! Form::text('position',$contact->position,['placeholder'=>'Position','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>City position is in:</label>
                            {!! Form::text('city_position', $contact->city_position, ['placeholder'=>'City position is in','class'=>'form-control']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Note:</label>
                            {!! Form::textarea('note', $contact->note, ['placeholder'=>'Note','class'=>'form-control']) !!}
                        </div>
                    </div>
                </div>
            </form>
        </div>
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