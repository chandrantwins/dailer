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
                    <th width="20%">Title</th>
                    <td>{{$contact->title}}</td>
                </tr>                
                <tr>
                    <th width="20%">Email address</th>
                    <td>{{$contact->email}}</td>
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
                            echo '</ul>';
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
        <a class="btn btn-default pull-right" data-toggle="modal" data-target="#invoice_modal">Send Invoice</a>        
        <a class="btn btn-default pull-right" href="{{ route('contact.reqcalendar',$contact->id) }}">Schedule</a>
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

<!-- Modal -->
<div class="modal fade" id="invoice_modal" tabindex="-1" role="dialog" aria-labelledby="Email" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="form-group">
            <h3>Enter billing details here:</h3>
            <hr>            
            <form action="{{route('contact.information',$contact->id)}}" method="get" id="invoice_form">
                <div class="col-xs-12 col-md-12">
                    <div class="row text-primary" id="paymentstatus"></div>
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
                            <label>Number of users:</label>
                            {!! Form::text('noofusers',1,['placeholder'=>'Number of users','class'=>'form-control','id'=>'noofusers']) !!}
                        </div>
                        <div class="col-xs-12 form-group">
                            <label>Is this a monthly subscription or one time payment:</label>
                            <br />{{Form::checkbox('paymentterm[]', 'monthly', true,['class'=>'payment'])}} Monthly
                            <br />{{Form::checkbox('paymentterm[]', 'onetime', false,['class'=>'payment'])}} One Time Payment
                        </div>
                        <div class="col-xs-12 form-group" id="monthlyoption">
                            <label>Select payment billing period:</label>
                            <br />{{Form::checkbox('monthterm[]', '3', true,['class'=>'mpayment'])}} 3 Months
                            <br />{{Form::checkbox('monthterm[]', '6', false,['class'=>'mpayment'])}} 6 Months
                            <br />{{Form::checkbox('monthterm[]', '12', false,['class'=>'mpayment'])}} 12 Months
                        </div>
                    </div>
                </div>
            </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="invoiceemail">Send</button>        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
    $(function () {
        var settings = [
            {minv:1, maxv:10,license:100,fee:100},
            {minv:11, maxv:50,license:75,fee:250},
            {minv:51, maxv:100, license:50,fee:500},
            {minv:100, maxv:1000000, license:25,fee:1000}
        ];
        $('#paymentstatus').html('Your invoice amount for '+$('.payment').val()+' is $100');
        $('.payment').prop('id',$('.payment').val());
        $('.mpayment').prop('id',$('.mpayment').val());
        $('.payment').on('change', function() {
            $('.payment').prop('id',$(this).val());
            $('#paymentstatus').html('Your invoice amount for '+$(this).val()+' is $100');
            if($(this).val() == 'monthly' && $(this).is(":checked")){
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                $('#paymentstatus').html('Your invoice amount for '+$(this).val()+' is $'+($('#noofusers').val()*setobj.license)+ ' plus setup fee only for first month $'+setobj.fee);                
            }else{                
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                var nomonths = $('.mpayment').attr('id');
                if(nomonths != 3){
                    var months = (nomonths == 6)?5:10;
                    var amount = ($('#noofusers').val()*setobj.license*months)*0.90;                    
                }else
                    var amount = $('#noofusers').val()*setobj.license*$('.mpayment').attr('id');
                $('#paymentstatus').html('Your invoice amount for '+$(this).val()+' is $'+amount+ ' plus setup fee only for first month $'+setobj.fee);                
            }
            $('.payment').not(this).prop('checked', false);            
        });
        $('#noofusers').on('change', function() {
            if($('.payment').attr('id') == 'monthly'){
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                $('#paymentstatus').html('Your invoice amount for '+$('.payment').attr('id')+' is $'+($('#noofusers').val()*setobj.license)+ ' plus setup fee only for first month $'+setobj.fee);
            }else{
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                var nomonths = $('.mpayment').attr('id');
                if(nomonths != 3){
                    var months = (nomonths == 6)?5:10;
                    var amount = ($('#noofusers').val()*setobj.license*months)*0.90;                    
                }else
                    var amount = $('#noofusers').val()*setobj.license*$('.mpayment').attr('id');
                $('#paymentstatus').html('Your invoice amount for '+$('.payment').attr('id')+' is $'+amount+ ' plus setup fee only for first month $'+setobj.fee);                
            }
        });
        $('.mpayment').on('change', function() {            
            $('.mpayment').prop('id',$(this).val());
            $('.mpayment').not(this).prop('checked', false);
            if($('.payment').attr('id') == 'monthly'){
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                $('#paymentstatus').html('Your invoice amount for '+$('.payment').attr('id')+' is $'+($('#noofusers').val()*setobj.license)+ ' plus setup fee only for first month $'+setobj.fee);                
            }else{                
                var setobj = settings.find(function (obj) { return obj.minv <= $('#noofusers').val() && $('#noofusers').val() <= obj.maxv; });
                var nomonths = $('.mpayment').attr('id');
                if(nomonths != 3){
                    var months = (nomonths == 6)?5:10;
                    var amount = ($('#noofusers').val()*setobj.license*months)*0.90;
                }
                else
                    var amount = $('#noofusers').val()*setobj.license*$('.mpayment').attr('id');
                $('#paymentstatus').html('Your invoice amount for '+$('.payment').attr('id')+' is $'+amount+ ' plus setup fee only for first month $'+setobj.fee);
            }
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

        $('#invoiceemail').on('click',function(event) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });            
            $alert_success = '<div class="alert alert-success">Thank you! Your invoice has been sent successfully.</div>'
            $alert_danger = '<div class="alert alert-danger">Your invoice has not been sent. Please contact your responsible</div>';
            var data = $("#invoice_form").serializeArray();
            data.push({name: 'id', value: '{{$contact->id}}'});
            $.ajax({
                type: 'POST',
                url: '{{route('contact.invoice')}}',
                data: data,
                success: function(response) {
                    if(response.success){
                        window.location.href = response.url;
                    }
                }                
            }).done(function(response) {
                $('#here').html($alert_success);
            }).fail(function(response) {
                $('#here').html($alert_danger);
            }).always(function(response) {
                $('#invoice_modal').modal('hide');
            });
        });
    });
</script>
@endsection