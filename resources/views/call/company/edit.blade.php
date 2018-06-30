@extends('layouts.app')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('vendor/select2/dist/css/select2.min.css')}}">
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left"><h2>Edit Call</h2></div>
        <div class="pull-right"><a class="btn btn-primary" href="{{ route('reporting.calls') }}"> Back</a></div>
    </div>
</div>
<hr>
@if (count($errors) > 0)
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div id="alert"></div>
</div>
@endif
<div class="row">
    <div class="col-xs-12 col-sm-6">
    {!! Form::model($call, ['method' => 'POST','route' => ['reporting.calls.edit', 'call'=>$call->id],'onsubmit'=>'return validateForm()']) !!}
        <div class="form-group">
            <label>Status:</label>
            {!! Form::select('status',$status,$call->answer,['placeholder'=>'Select status','class'=>'form-control select2','required'=>true]) !!}
        </div>
        <div class="form-group" style="display: none;" id="follow-up">
            <label style="display:block;">Remined me in: </label> 
            {!! Form::number('hour','01',['name'=>'hour','placeholder'=>'0','class'=>'form-control','required'=>true,'min'=>'00','max'=>'12','style'=>'width:65px;display: inline-block;']) !!}
            {!! Form::number('minute','00',['name'=>'minute','placeholder'=>'0','class'=>'form-control','required'=>true,'min'=>'00','max'=>'59','style'=>'width:65px;display: inline-block;']) !!}
            {!! Form::number('second','00',['name'=>'second','placeholder'=>'0','class'=>'form-control','required'=>true,'min'=>'00','max'=>'59','style'=>'width:65px;display: inline-block;']) !!}
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    {!! Form::close() !!}
    </div>
    <div class="col-xs-12 col-sm-6">
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th width="20%">Contact name</th>
                    <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                </tr>
                <tr>
                    <th width="20%">Phone number</th>
                    <td>{{$contact->phone}}</td>
                </tr>
                <tr>
                    <th width="20%">Date time</th>
                    <td>{{$call->created_at->toDateTimeString()}}</td>
                </tr>
                <tr>
                    <th width="20%">Note: </th>
                    <td>{{$call->note}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('javascript')
<!-- Select2 -->
<script src="{{asset('vendor/select2/dist/js/select2.full.min.js')}}"></script>
<script type="text/javascript">
    $(function () {
        var t = $('#datatable').DataTable();
        //Initialize Select2 Elements
        $('.select2').select2();
console.log($('select[name="status"]').val());
        if ($('select[name="status"]').val() === "{{App\Call::ANSWER_PROGRESS}}") {
            $('#follow-up').show();
        }

        $('input[type="number"]').on('change',function(event) {
            if ($(this).val().length == 0) {
                $(this).val(0);
            }
        });

        $('select[name="status"]').on('change',function(event) {
            if ($(this).val() === "{{App\Call::ANSWER_PROGRESS}}") {
                $('#follow-up').show();
            } else {
                $('#follow-up').hide();
            }
        });
    });

    function validateForm() {
        var second = ($('#hour').val()*3600)+($('#minute').val()*60)+$('#second').val()*1;

        if (second < 300) {
            $('#alert').addClass('alert');
            $('#alert').addClass('alert-danger');
            $('#alert').html('add a duration great then 5 minutes');

            return false;
        }

        return true;
    }
</script>
@endsection
