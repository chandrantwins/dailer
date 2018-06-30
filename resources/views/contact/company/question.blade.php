@extends('layouts.app')

@section('style')
<!-- Bootstrap time Picker -->
<link rel="stylesheet" href="{{asset('vendor/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}">
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="{{asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}">
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="clearfix">
            <h1 class="pull-left">Question page</h1>
        </div>
        <hr>
        <!-- will be used to show any messages -->
        @if ($alert = Session::get('alert'))
        <div class="alert alert-{{$alert['class']}}"><p>{{$alert['message']}}</p></div>
        @endif
        <div id="alert"></div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<div class="row">
    {!! Form::open(['route' => ['contact.question',$contact->id],'method'=>'POST']) !!}
        <div class="col-xs-12 col-sm-6">
            @foreach($questions as $key=>$question)
            <div class="form-group">
                <label>
                    {!! Form::radio('answer',$key,false,['required'=>true]) !!} {{$question}}
                </label>
            </div>
            @endforeach
        </div>
        <div class="col-xs-12 col-sm-6" style="display: none;" id="follow-up">
            <div class="form-group row">
                <div class="col-xs-12">
                    <label>Remined at: </label>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="input-group date">
                        <input type="text" name="date" class="form-control" id="datepicker">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                    <!-- /.input group -->
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="input-group">
                        <input type="text" name="time" class="form-control timepicker">
                        <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                    </div>
                    <!-- /.input group -->
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 form-group">
            <div class="form-group">
                <label>Note: </label>
                {!! Form::textarea('note', null, ['placeholder'=>'Note','class'=>'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 text-center form-group">
            <button type="submit" class="btn btn-primary">Next page</button>
        </div>
    {!! Form::close() !!}
</div>
<!-- /.row -->
@endsection

@section('javascript')
<!-- bootstrap time picker -->
<script src="{{asset('vendor/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
<!-- bootstrap datepicker -->
<script src="{{asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
    $(function () {
        if ($('input[name="answer"]:checked').val() === "{{App\Call::ANSWER_PROGRESS}}") {
            $('#follow-up').show();
        }

        //Date picker
        $('#datepicker').datepicker({
          autoclose: true
        });
        $('#datepicker').datepicker('update', new Date());

        //Timepicker
        $('.timepicker').timepicker({
          showInputs: true
        });

        $('input[type="number"]').on('change',function(event) {
            if ($(this).val().length == 0) {
                $(this).val(0);
            }
        });

        $('input[name="answer"]').on('change',function(event) {
            if ($(this).val() === "{{App\Call::ANSWER_PROGRESS}}") {
                $('#follow-up').show();
            } else {
                $('#follow-up').hide();
            }
        });
    });
</script>
@endsection