@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="clearfix">
            <h1 class="pull-left">All the Users</h1>
            <a href="{{route('users.create')}}" class="btn btn-primary pull-right">Create new User</a>
        </div>
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
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th><input name="select_all" value="1" id="table-select-all" type="checkbox"></th>
                    <th width="20%">Full name</th>
                    <th width="15%">Username</th>
                    <th width="25%">Email</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    @if ($user->id != 1)
                    <td><input type="checkbox" name="user" value="{{$user->id}}"></td>
                    @else
                    <td></td>
                    @endif
                    <td>{{$user->first_name}} {{$user->last_name}}</td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->role}}</td>
                    <!-- we will also add show, edit, and delete buttons -->
                    <td>
                        <a class="btn btn-info btn-xs" href="{{ route('users.show',$user->id) }}">Show</a>
                        <a class="btn btn-primary btn-xs" href="{{ route('users.edit',$user->id) }}">Edit</a>
                        <a class="btn btn-success btn-xs" href="{{ route('users.activate',['users'=>$user->id]) }}">Activate</a>
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
        $('#DataTables_Table_0_filter > label').before('<a id="activate_users" class="btn btn-success btn-sm" style="margin-right: 10px;">Activate</button>');

        $('#activate_users').on('click', function() {
            $form = $('<form method="GET" action="{{route('users.activate')}}"></form>');
            // Iterate over all checkboxes in the table
            table.$('input[type="checkbox"]').each(function() {
                // If checkbox is checked
                if(this.checked) {
                    $form.append('<input type="checkbox" name="users" value="'+this.value+'" checked>');
                }
            });
            $form.append('<input type="button" value="button">');
            $('body').append($form);
            $form.submit();
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