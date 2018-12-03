@extends('layouts.app')

@section('style')
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{asset('vendor/bootstrap-daterangepicker/daterangepicker.css')}}">
@endsection

@section('content')
    @if (in_array(Auth::user()->role, [\App\User::ADMIN,\App\User::SUBADMIN,\App\User::CANDIDATE,\App\User::CLOSER,\App\User::REQRUITED]))    
    <div class="row">
        @if (in_array(Auth::user()->role, [\App\User::ADMIN,\App\User::SUBADMIN]))
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-primary btn-dashboard disabled" data-type="candidate" data-row="1">Candidate</button>
        </div>
        <!-- ./col -->
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-success btn-dashboard" data-type="company" data-row="1">Company</button>
        </div>
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-info btn-dashboard" data-type="reqruited" data-row="1">Reqruited</button>
        </div>
        <!-- ./col -->
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-warning btn-dashboard" data-type="both" data-row="1">All</button>
        </div>
        <!-- ./col -->
        @endif
        <div class="col-lg-offset-4 col-lg-4 col-xs-4">
            <div class="form-group">
                <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-row-1" data-row="1">
                        <span><i class="fa fa-calendar"></i> Date range picker</span>
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
            <!-- /.form group -->
        </div>
        <!-- ./col -->
    </div>    
    <div class="row">
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 class="text-center" id="successful-row-1">{{$data['candidate']['successful']}}</h3>
                    <p class="text-center">Success ranking</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 class="text-center" id="in_progress-row-1">{{$data['candidate']['in_progress']}}</h3>
                    <p class="text-center">Follow ups</p>
                </div>
            </div>
        </div>
        @if (in_array(Auth::user()->role, [\App\User::CLOSER,\App\User::REQRUITED]))
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 class="text-center" id="wrong_numbers-row-1">{{$data['candidate']['wrong_numbers']}}</h3>
                    <p class="text-center">No Shows</p>
                </div>
            </div>
        </div>			
        @endif
        @if (!in_array(Auth::user()->role, [\App\User::CLOSER,\App\User::REQRUITED]))
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 class="text-center" id="wrong_numbers-row-1">{{$data['candidate']['wrong_numbers']}}</h3>
                    <p class="text-center">Wrong numbers</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 class="text-center" id="blacklist-row-1">{{$data['candidate']['blacklist']}}</h3>
                    <p class="text-center">Blacklist</p>
                </div>
            </div>
        </div>
        @endif
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gray">
                <div class="inner">
                    <h3 class="text-center" id="total-row-1">{{$data['candidate']['total']}}</h3>
                    <p class="text-center">Total</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 class="text-center" id="answered-row-1">{{$data['candidate']['answered']}}</h3>
                    <p class="text-center">Total answered</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->
    <hr>
    @endif
    @if (in_array(Auth::user()->role, [\App\User::ADMIN,\App\User::SUBADMIN,\App\User::COMPANY]))
    <div class="row">
        @if (in_array(Auth::user()->role, [\App\User::ADMIN,\App\User::SUBADMIN]))
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-primary btn-dashboard" data-type="candidate" data-row="2">Candidate</button>
        </div>
        <!-- ./col -->
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-success btn-dashboard disabled" data-type="company" data-row="2">Company</button>
        </div>
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-info btn-dashboard" data-type="reqruited" data-row="2">Reqruited</button>
        </div>
        <!-- ./col -->
        <div class="col-lg-1 col-xs-2">
            <button class="btn btn-warning btn-dashboard" data-type="both" data-row="2">All</button>
        </div>
        <!-- ./col -->
        @endif
        <div class="col-lg-offset-4 col-lg-4 col-xs-4">
            <div class="form-group">
                <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-row-2" data-row="2">
                        <span><i class="fa fa-calendar"></i> Date range picker</span>
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
            <!-- /.form group -->
        </div>
        <!-- ./col -->
    </div>
    <div class="row">
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 class="text-center" id="successful-row-2">{{$data['company']['successful']}}</h3>
                    <p class="text-center">Success ranking</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 class="text-center" id="in_progress-row-2">{{$data['company']['in_progress']}}</h3>
                    <p class="text-center">Follow ups</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 class="text-center" id="wrong_numbers-row-2">{{$data['company']['wrong_numbers']}}</h3>
                    <p class="text-center">Wrong numbers</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 class="text-center" id="blacklist-row-2">{{$data['company']['blacklist']}}</h3>
                    <p class="text-center">Blacklist</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gray">
                <div class="inner">
                    <h3 class="text-center" id="total-row-2">{{$data['company']['total']}}</h3>
                    <p class="text-center">Total</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 class="text-center" id="answered-row-2">{{$data['company']['answered']}}</h3>
                    <p class="text-center">Total answered</p>
                </div>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->
    @endif
    @if (in_array(Auth::user()->role, [\App\User::CANDIDATE,\App\User::COMPANY]))
    <hr>
    <div class="row">
        <div class="col-xs-12">
            <p>Estimated payout from <strong id="date-payout">Last 7 Days</strong></p>
            <p>based on marked successes = <strong>$ <span>{{$data[Auth::User()->role]['successful']*$data['payout_'.Auth::User()->role]}}</span></strong></p>
            <p>Estimated total payout for all time</p>
            <p>based on marked successes = <strong>$ <span>{{$data['successful']->count()*$data['payout_'.Auth::User()->role]}}</span></strong></p>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="clearfix">
                <h3 class="pull-left">Success ranking last 10</h3>
            </div>
            <hr>
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Type</td>
                    <td>Phone number</td>
                    <td>Date</td>
                </tr>
                </thead>
                <tbody>
                @foreach($data['successful'] as $element)
                    <tr>
                        <?php $contact = $element->contact()->first(); ?>
                        <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                        <td>{{$contact->type}}</td>
                        <td>{{$contact->phone}}</td>
                        <td>{{$element->created_at->toDateTimeString()}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- ./col -->
        <div class="col-xs-12 col-sm-6">
            <div class="clearfix">
                <h3 class="pull-left">Blacklist last 10</h3>
            </div>
            <hr>
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <td>Contact name</td>
                    <td>Type</td>
                    <td>Phone number</td>
                    <td>Date</td>
                </tr>
                </thead>
                <tbody>
                @foreach($data['blacklist'] as $element)
                    <tr>
                        <?php $contact = $element->contact()->first(); ?>
                        <td>{{$contact->first_name}} {{$contact->last_name}}</td>
                        <td>{{$contact->type}}</td>
                        <td>{{$contact->phone}}</td>
                        <td>{{$element->created_at->toDateTimeString()}}</td>
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
    <!-- daterange picker -->
    <script src="{{asset('vendor/moment/min/moment.min.js')}}"></script>
    <script src="{{asset('vendor/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <script type="text/javascript">        
        $('#daterange-row-1').daterangepicker(
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
                $('#daterange-row-1 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
            }
        );

        $('#daterange-row-2').daterangepicker(
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
                $('#daterange-row-2 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
            }
        );
        @if (in_array(Auth::user()->role, [\App\User::ADMIN,\App\User::SUBADMIN]))
        function dashboard($this) {
            var row = $this.attr('data-row');
            var type = $this.attr('data-type');
            var selector = 'daterange-row-' + row;
            var startDate = $('#' + selector).data('daterangepicker').startDate.format('YYYY/MM/DD H:mm:ss');
            var endDate = $('#' + selector).data('daterangepicker').endDate.format('YYYY/MM/DD H:mm:ss');

            $('.btn-dashboard[data-row="' + row + '"]').removeClass('disabled');
            $this.addClass('disabled');

            $.ajax({
                type: 'GET',
                url: '{{route('reporting.dashboard')}}',
                data: {type: type, startDate: startDate, endDate: endDate}
            }).done(function (response) {
                $('#successful-row-' + row).html(response['successful']);
                $('#in_progress-row-' + row).html(response['in_progress']);
                $('#answered-row-' + row).html(response['answered']);
                $('#wrong_numbers-row-' + row).html(response['wrong_numbers']);
                $('#blacklist-row-' + row).html(response['blacklist']);
                $('#total-row-' + row).html(response['total']);
            }).fail(function (response) {
                $('.btn-dashboard').removeClass('disabled');
                alert('Unkown Errer!!');
            });
        }

        $('.btn-dashboard').on('click', function(event, picker) {
            dashboard($(this));
        });

        $('#daterange-row-1').on('apply.daterangepicker', function(event, picker) {
            dashboard($('.btn-dashboard.disabled[data-row="1"]'));
        });

        $('#daterange-row-2').on('apply.daterangepicker', function(event, picker) {
            dashboard($('.btn-dashboard.disabled[data-row="2"]'));
        });
        @else
        function dashboard($this) {
            var row = $this.attr('data-row');
            var selector = 'daterange-row-' + row;
            var startDate = $('#' + selector).data('daterangepicker').startDate.format('YYYY/MM/DD H:mm:ss');
            var endDate = $('#' + selector).data('daterangepicker').endDate.format('YYYY/MM/DD H:mm:ss');

            $('.btn-dashboard[data-row="' + row + '"]').removeClass('disabled');
            $this.addClass('disabled');

            $.ajax({
                type: 'GET',
                url: '{{route('reporting.dashboard')}}',
                data: {type: '{{Auth::user()->role}}', startDate: startDate, endDate: endDate}
            }).done(function (response) {
                $('#successful-row-' + row).html(response['successful']);
                $('#in_progress-row-' + row).html(response['in_progress']);
                $('#answered-row-' + row).html(response['answered']);
                $('#wrong_numbers-row-' + row).html(response['wrong_numbers']);
                $('#blacklist-row-' + row).html(response['blacklist']);
                $('#total-row-' + row).html(response['total']);
            }).fail(function (response) {
                $('.btn-dashboard').removeClass('disabled');
                alert('Unkown Errer!!');
            });
        }

        $('#daterange-row-1').on('apply.daterangepicker', function(event, picker) {
            //$('#date-payout').html(picker.startDate.format('YYYY-MM-DD') + ' -> ' + picker.endDate.format('YYYY-MM-DD'));;
            dashboard($(this));
        });

        $('#daterange-row-2').on('apply.daterangepicker', function(event, picker) {
            //$('#date-payout').html(picker.startDate.format('YYYY-MM-DD') + ' -> ' + picker.endDate.format('YYYY-MM-DD'));;
            dashboard($(this));
        });
    @endif
    </script>
@endsection