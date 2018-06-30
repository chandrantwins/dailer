@extends('layouts.app')

@section('style')
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('vendor/bootstrap-daterangepicker/daterangepicker.css')}}">
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <h3 class="pull-left">Statistics</h3>
        </div>
        <hr>
        <div class="col-xs-12 col-sm-offset-3 col-sm-3">
            <label>Caller:</label>
            {!! Form::select('caller',$callers,null,['placeholder'=>'Select caller','class'=>'form-control select2','id'=>'callers']) !!}
        </div>
        <div class="col-xs-12 col-sm-3">
                <br>
                <button type="button" class="btn btn-default pull-right" id="daterange-row" data-row="1">
                    <span><i class="fa fa-calendar"></i> Date range picker</span>
                    <i class="fa fa-caret-down"></i>
                </button>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <!-- DONUT CHART -->
        <div class="box box-danger">
            {{-- <div class="box-header with-border">
                <h3 class="box-title">Statistics</h3>
            </div> --}}
            <div class="box-body">
                <canvas id="pieChart" style="height:250px"></canvas>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="col-xs-12 col-sm-6">
            <table class="table table-hover dataTable" id="DataTables_Table_0">
                <tbody>
                <tr>
                    <th width="20%">Full name:</th>
                    <td id="name"></td>
                </tr>
                <tr>
                    <th width="20%">Email address:</th>
                    <td id="email"></td>
                </tr>
                <tr>
                    <th width="20%">Total calls:</th>
                    <td id="total_calls"></td>
                </tr>
                <tr>
                    <th width="20%">Successes:</th>
                    <td id="successful"></td>
                </tr>
                <tr>
                    <th width="20%">Follow up:</th>
                    <td id="followup"></td>
                </tr>
                <tr>
                    <th width="20%">Unsuccessful:</th>
                    <td id="unsuccessful"></td>
                </tr>
                <tr>
                    <th width="20%">Left message:</th>
                    <td id="left_message"></td>
                </tr>
                <tr>
                    <th width="20%">Could not get gate keeper:</th>
                    <td id="gatekeeper"></td>
                </tr>
                </tbody>
            </table>
    </div>    
    <!-- ./col -->
</div>
<!-- /.row -->
<hr>
@endsection

@section('javascript')
<!-- daterange picker -->
<script src="{{asset('vendor/moment/min/moment.min.js')}}"></script>
<script src="{{asset('vendor/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<!-- Select2 -->
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<!-- ChartJS -->
<script src="{{asset('vendor/chart.js/Chart.min.js')}}"></script>
<script type="text/javascript">
    $(function () {
        var t = $('#datatable').DataTable();
        //Initialize Select2 Elements
        $('.select2').select2();
        // Initialize date range Elements
        $('#daterange-row').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(6, 'days'),
                endDate: moment()
            },
            function (start, end) {
                $('#daterange-row span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
            }
        );

        $('#daterange-row').on('apply.daterangepicker', function (event, picker) {
            getData();
        });
        
        if ($('#callers').val().length != 0) {
            getData($('#callers').val());
        }

        //- PIE CHART -
        //-------------
        // Get context with jQuery - using jQuery's .get() method.
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var pieChart       = new Chart(pieChartCanvas);
        var PieData        = [
          {
            value    : 1,
            color    : '#d2d6de',
            highlight: '#d2d6de',
            label    : 'Empty'
          }
        ];
        var pieOptions     = {
          //Boolean - Whether we should show a stroke on each segment
          segmentShowStroke    : true,
          //String - The colour of each segment stroke
          segmentStrokeColor   : '#fff',
          //Number - The width of each segment stroke
          segmentStrokeWidth   : 2,
          //Number - The percentage of the chart that we cut out of the middle
          percentageInnerCutout: 50, // This is 0 for Pie charts
          //Number - Amount of animation steps
          animationSteps       : 100,
          //String - Animation easing effect
          animationEasing      : 'easeOutBounce',
          //Boolean - Whether we animate the rotation of the Doughnut
          animateRotate        : true,
          //Boolean - Whether we animate scaling the Doughnut from the centre
          animateScale         : false,
          //Boolean - whether to make the chart responsive to window resizing
          responsive           : true,
          // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
          maintainAspectRatio  : true,
          //String - A legend template
          legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
        };
        //Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        pieChart.Doughnut(PieData, pieOptions);

        $('#callers').on('change',function(argument) {
            getData();
        });

        function getData() {
            var caller = $('#callers').val();
            var startDate = $('#daterange-row').data('daterangepicker').startDate.format('YYYY/MM/DD H:mm:ss');
            var endDate = $('#daterange-row').data('daterangepicker').endDate.format('YYYY/MM/DD H:mm:ss');

            $.ajax({
                type: 'GET',
                url: '{{route('reporting.statistics.data')}}',
                data: {caller: caller, startDate: startDate,endDate: endDate}
            }).done(function(response) {
                pieChart.Doughnut(response.calls, pieOptions);
                $('#name').html(response.name);
                $('#email').html(response.email);
                
                $('#total_calls').html(response.total_calls);
                $('#successful').html(response.successful);
                $('#followup').html(response.followup);
                $('#left_message').html(response.left_message);
                $('#unsuccessful').html(response.unsuccessful);
                $('#gatekeeper').html(response.gatekeeper);                
            }).fail(function() {
                pieChart.Doughnut([{
                    value    : 1,
                    color    : '#d2d6de',
                    highlight: '#d2d6de',
                    label    : 'Empty'
                  }], pieOptions);
            });
        }
    });
</script>
@endsection