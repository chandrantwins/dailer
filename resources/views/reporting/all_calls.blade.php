@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <h3 class="pull-left">All Calls</h3>
        </div>
    </div>
</div>
<!-- /.row -->
<hr>
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
    <div class="col-xs-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Caller</th>
                    <th>Contact name</th>
                    <th>Business name</th>
                    <th>Phone number</th>
                    <th>Email</th>
                    <th>Date time</th>
                    <th>Note</th>
                    <th>Result</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($calls as $call)
                <tr>
                    <td><?= $call[0] ?></td>
                    <td><?= $call[1] ?></td>
                    <td><?= $call[2] ?></td>
                    <td><?= $call[3] ?></td>
                    <td><?= $call[4] ?></td>
                    <td><?= $call[5] ?></td>
                    <td><?= $call[6] ?></td>
                    <td><?= $call[7] ?></td>
                    <td><?= $call[8] ?></td>
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
<script type="text/javascript">
    $(document).ready(function() {
        table.destroy();
        table = $('.table').DataTable();
    });
</script>
@endsection