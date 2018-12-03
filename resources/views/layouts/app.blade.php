<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Monikl</title>
        <!-- Styles -->
         <link rel="stylesheet" href="{{asset('vendor/bootstrap/dist/css/bootstrap.min.css')}}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{asset('vendor/font-awesome/css/font-awesome.min.css')}}">
        <!-- Ionicons -->
        <link rel="stylesheet" href="{{asset('vendor/Ionicons/css/ionicons.min.css')}}">
        <!-- DataTables -->
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('css/AdminLTE.min.css')}}">
		<link rel="stylesheet" href="{{asset('css/custom.css')}}">
        <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="{{asset('css/skins/skin-green.min.css')}}">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
        @yield('style')
        <style type="text/css">
            td.text-overflow {
                position: relative;
                max-width: 0;
                overflow: hidden;
            }

            .text-overflow > p {
                position: relative;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 100%;
                display: inline-block;
            }
        </style>
    </head>
    <body class="hold-transition skin-green sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <!-- Logo -->
                <a href="#" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><b>Monikl</b></span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><img src="{{asset('imgs/logo.png')}}" alt="logo monikl" class="img-responsive"></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li><a href="{{route('logout')}}" class="btn">Sign out</a></li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu" data-widget="tree">
                        <li class="header"></li>
                        <li class="header">MAIN NAVIGATION</li>
                        <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                        @if (Auth::user()->hasRole(\App\User::CLOSER))
                            <li><a href="{{route('appoinments')}}"><i class="fa fa-phone"></i> Appoinments</a></li>
                            <li><a href="{{route('closer.setting')}}"><i class="fa fa-gear"></i> Settings</a></li>
                        @endif
                        @if (Auth::user()->hasRole(\App\User::ADMIN) || Auth::user()->hasRole(\App\User::SUBADMIN))
                        <li><a href="{{route('users.dealflow')}}"><i class="fa fa-envelope"></i> DealFlow</a></li>
                        <li><a href="{{route('users.todaystatus')}}"><i class="fa fa-envelope"></i> Last24hours</a></li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-phone"></i> <span>Appointments</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{route('appointments.upcoming')}}"><i class="fa fa-volume-control-phone"></i> <span>Upcoming</span></a>
                                <li><a href="{{route('appointments.finished')}}"><i class="fa fa-phone-square"></i> Finished</a></li>
                            </ul>
                        </li> 
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i> <span>Callers</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{route('users.index')}}"><i class="fa fa-users"></i> <span>Users</span></a>
                                @if (Auth::user()->hasRole(\App\User::ADMIN))
                                <li><a href="{{route('users.leaders')}}"><i class="fa fa-users"></i> <span>Leaders</span></a>
                                @endif
                                <li><a href="{{route('users.deactivate_index')}}"><i class="fa fa-users"></i> Deactivated users</a></li>
                            </ul>
                        </li>                        
                        @if (Auth::user()->hasRole(\App\User::ADMIN))
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-address-book"></i> <span>Contacts</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{route('contact.index',['type'=>'company'])}}"><i class="fa fa-circle-o"></i> Company</a></li>
                                <li><a href="{{route('contact.index',['type'=>'candidate'])}}"><i class="fa fa-circle-o"></i> Candidate</a></li>
                                <li><a href="{{route('contact.index',['type'=>'reqruited'])}}"><i class="fa fa-circle-o"></i> Reqruited</a></li>
                                @if (Auth::user()->hasRole(\App\User::ADMIN))
                                <li><a href="{{route('contact.deactivate_index')}}"><i class="fa fa-circle-o"></i> Deactivated contacts</a></li>
                                @endif
                                <li><a href="{{route('contact.import')}}"><i class="fa fa-circle-o"></i> Import</a></li>
                            </ul>
                        </li>
                        @endif
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-line-chart"></i> <span>Reporting</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{route('reporting.blacklist')}}"><i class="fa fa-circle-o"></i> Blacklist</a></li>
                                <li><a href="{{route('reporting.calls.all')}}"><i class="fa fa-circle-o"></i> All Calls</a></li>
                                <li><a href="{{route('reporting.calls')}}"><i class="fa fa-circle-o"></i> Calls By caller</a></li>
                                <li><a href="{{route('reporting.statistics')}}"><i class="fa fa-circle-o"></i> Statistic</a></li>
                                <li><a href="{{route('reporting.appointment')}}"><i class="fa fa-circle-o"></i> Appointments</a></li>
                            </ul>
                        </li>
                        @if (Auth::user()->hasRole(\App\User::ADMIN))
                        <li><a href="{{route('emails.index')}}"><i class="fa fa-envelope"></i> Emails</a></li>
                        <li><a href="{{route('setting.index')}}"><i class="fa fa-gear"></i> Settings</a></li>
                        @endif
                        @elseif (in_array(Auth::user()->role, [\App\User::CANDIDATE,\App\User::COMPANY]))
                        <li><a href="{{route('contact.get')}}"><i class="fa fa-circle-o"></i> Contacts</a></li>
                        <li><a href="{{route('contact.follow-ups')}}"><i class="fa fa-arrow-left"></i> Follow-ups</a></li>
                        @endif
                        @if (in_array(Auth::user()->role, [\App\User::REQRUITED]))
                            <li><a href="{{route('contact.get')}}"><i class="fa fa-circle-o"></i> Contacts</a></li>
                            <li><a href="{{route('contact.follow-ups')}}"><i class="fa fa-arrow-left"></i> Follow-ups</a></li>
                            <li><a href="{{route('reqruited.appointments')}}"><i class="fa fa-phone"></i> Appoinments</a></li>
                            <li><a href="{{route('reqruited.pendinginvoice')}}"><i class="fa fa-phone"></i> Unpaid Invoice</a></li>
                            <li><a href="{{route('reqruited.setting')}}"><i class="fa fa-gear"></i> Settings</a></li>
                        @endif
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <section class="content">
                    <div class="box">
                        <div class="box-body">
                            @yield('content')
                        </div>
                    </div>
                </section>
            </div>
            <!-- /Content Wrapper -->
        </div>
        <!-- ./wrapper -->
        <!-- jQuery 3 -->
        <script src="{{asset('vendor/jquery/dist/jquery.min.js')}}"></script>
        <!-- Bootstrap 3.3.7 -->
        <script src="{{asset('vendor/bootstrap/dist/js/bootstrap.min.js')}}"></script>
        <!-- Slimscroll -->
        <script src="{{asset('vendor/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
        <!-- FastClick -->
        <script src="{{asset('vendor/fastclick/lib/fastclick.js')}}"></script>
        <!-- DataTables -->
        <script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <!-- AdminLTE App -->
        <script src="{{asset('/js/adminlte.min.js')}}"></script>
        <!-- Full calendar -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
        <!-- d3.js -->
        <script type="text/javascript" src="https://d3js.org/d3.v3.min.js"></script>
        <!-- d3 funnel -->
        <script src="{{asset('/js/d3-funnel.js')}}"></script>
    
        <!--<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
        https://stackoverflow.com/questions/27834349/how-to-set-the-business-hours-for-fullcalender-v2-2-5 -->
        <script src="{{asset('/js/fullcalendar.js')}}"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.2.17/jquery.timepicker.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/js/rainbow.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/rainbow/1.2.0/js/language/generic.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.businessHours/1.0.1/jquery.businessHours.js"></script>
		
        @if(isset($calendar_details))
         {!! $calendar_details->script() !!}
        @endif
        <script type="text/javascript">
            var table;
            $(document).ready(function() {
                table = $('.table').DataTable({
                    "ordering": true
                });
                leaderdropdown();
                function leaderdropdown(){                    
                   if($("#roles").val()=="subadmin" || $("#roles").val()=="admin"){                       
                       $("#leaderrole").attr("style", "opacity: 0;");
                       document.getElementById('user_id').value = 0;
                       document.getElementById('user_id').removeAttribute("required");
                   }else{
                       $("#leaderrole").attr("style", "");
                       $("#user_id").attr("required",true);
                   }                    
                }
                $("#roles").change(function(){
                    leaderdropdown();
                });
            });
        </script>
        @yield('javascript')
    </body>
</html>