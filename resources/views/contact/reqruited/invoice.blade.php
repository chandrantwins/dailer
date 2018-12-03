@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <h2 class="pull-left">Unpaid Invoice Contacts</h2>
    </div>
</div>
<hr>
<!-- /.row -->
<div class="row">
    <div class="col-sm-12">
        <table id="datatable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <td>Customer Id</td>
                    <td>Customer Name</td>                    
                    <td>Amount</td>
                    <td>Status</td>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $invoicecust)
                <tr>
                    <td>{{$invoicecust->customerid}}</td>
                    <td>{{$invoicecust->customername}}</td>
                    <td>{{$invoicecust->amount}}</td>
                    <td>{{($invoicecust->status == 0)?"Unpaid":"Paid"}}</td>                    
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
@endsection