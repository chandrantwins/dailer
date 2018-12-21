@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <h2 class="pull-left">All the Contacts: Reqruited</h2>
        @if (\App\User::ADMIN == Auth::user()->role)
            <div class="pull-right">
                <a href="{{route('contact.assign',['type'=>'reqruited'])}}" class="btn btn-info">Refresh assignment</a>
                <a href="{{route('contact.create',['type'=>'reqruited'])}}" class="btn btn-primary">Create new Contact</a>
            </div>
        @endif
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
    <div class="col-sm-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><input name="select_all" value="1" id="table-select-all" type="checkbox"></th>
                    <th>Contact name</th>
                    <th>Company name</th>
                    <th>Title</th>
                    <th>Email address</th>
                    <th>Phone number</th>
                    <th>Mobile number</th>
                    <th>Position</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($contacts as $contact)
                <tr>
                    <td><input type="checkbox" name="contact" value="{{$contact->id}}"></td>
                    <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                    <td>{{$contact->company_name}}</td>
                    <td>{{$contact->title}}</td>
                    <td>{{$contact->email}}</td>
                    <td>{{$contact->phone}}</td>
                    <td>{{$contact->mobile}}</td>
                    <td>{{$contact->position}}</td>
                    <!-- we will also add show, edit, and delete buttons -->
                    <td>
                        <a class="btn btn-info btn-xs" href="{{route('contact.show',['id'=>$contact->id])}}">Show</a>
                        @if (\App\User::ADMIN == Auth::user()->role)
                        <a class="btn btn-primary btn-xs" href="{{ route('contact.edit',$contact->id) }}">Edit</a>
                        <a class="btn btn-danger btn-xs" href="{{ route('contact.deactivate',['type'=>$contact->type,'contacts'=>$contact->id]) }}">Delete</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
		$('#datatable_filter > label').before('<button id="download_contacts" class="btn btn-success btn-sm" style="margin-right: 10px;">Download CSV</button>');
        $('#datatable_filter > label').before('<button id="deactivate_contacts" class="btn btn-warning btn-sm" style="margin-right: 10px;">Deactivate</button>');

        $('#deactivate_contacts').on('click', function() {
            var alert_success = '<div class="alert alert-success">You have deactivate contacts successfully.</div>';
            var alert_danger = '<div class="alert alert-danger">Unknow error!! You havn\'t deactivate contacts.</div>';

            var contacts = [];
            // Iterate over all checkboxes in the table
            table.$('input[type="checkbox"]').each(function(){
                // If checkbox is checked
                if(this.checked){
                    contacts.push(this.value);
                }
            });

            $.ajax({
                type: 'GET',
                url: '{{route('contact.deactivate')}}',
                data: {contacts: contacts}
            }).done(function(response) {
                $('#here').html(alert_success);
            }).fail(function(response) {
                $('#here').html(alert_danger);
            });
        });
		
		$('#download_contacts').on('click', function() {
            var alert_success = '<div class="alert alert-success">You have downloaded CSV successfully.</div>';
            var alert_danger = '<div class="alert alert-danger">Unknow error!! Download CSV process was not completed.</div>';

            var contacts = [];
            // Iterate over all checkboxes in the table
            table.$('input[type="checkbox"]').each(function(){
                // If checkbox is checked
                if(this.checked){
                    contacts.push(this.value);
                }
            });
			if (contacts.length === 0) {
				contacts.push('all');
			}
            $.ajax({
                type: 'GET',
                url: '{{route('contact.downloadcsv')}}',
                data: {contacts: contacts, types:'reqruited'}
            }).done(function(response) {
                $('#here').html(alert_success);
            }).fail(function(response) {
                $('#here').html(alert_danger);
            });
        });

        // Handle click on "Select all" control
        $('#table-select-all').on('click', function() {
            // Get all rows with search applied
            var rows = table.rows({ 'search': 'applied' }).nodes();
            // Check/uncheck checkboxes for all rows in the table
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
    });
</script>
@endsection
