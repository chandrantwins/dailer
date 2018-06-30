@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <h2 class="pull-left">All the deactivated Contacts</h2>
    </div>
</div>
<hr>
<!-- /.row -->
<div class="row">
    <div class="col-xs-12">
        <div id="here"></div>
        @if ($alert = Session::get('alert'))
        <!-- will be used to show any messages -->
        <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
        @endif
    </div>
</div>
<!-- /.row -->
<div class="row">
    <div class="col-sm-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><input name="select_all" value="1" id="table-select-all" type="checkbox"></th>
                    <th>Contact name</th>
                    <th>Type</th>
                    <th>Position</th>
                    <th>Email address</th>
                    <th>Phone number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($contacts as $contact)
                <tr>
                    <td><input type="checkbox" name="contact" value="{{$contact->id}}"></td>
                    <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                    <td>{{$contact->type}}</td>
                    <td>{{$contact->position}}</td>
                    <td>{{$contact->email}}</td>
                    <td>{{$contact->phone}}</td>
                    <!-- we will also add show, edit, and delete buttons -->
                    <td>
                        <a class="btn btn-info btn-xs" href="{{route('contact.show',['id'=>$contact->id])}}">Show</a>
                        @if (\App\User::ADMIN == Auth::user()->role)
                        <a class="btn btn-primary btn-xs" href="{{ route('contact.edit',['id'=>$contact->id]) }}">Edit</a>
                        <a class="btn btn-success btn-xs" href="{{ route('contact.activate',['type'=>$contact->type,'contacts'=>$contact->id]) }}">Activate</a>
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
        $('#datatable_filter > label').before('<a id="activate_contacts" class="btn btn-primary btn-sm" style="margin-right: 10px;">Activate</button>');

        $('#activate_contacts').on('click', function() {
            var alert_success = '<div class="alert alert-success">You have activate contacts successfully.</div>';
            var alert_danger = '<div class="alert alert-danger">Unknow error!! You havn\'t activate contacts.</div>';

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
                url: '{{route('contact.activate')}}',
                data: {contacts: contacts}
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