@extends('admin.layouts.master3')
@section('styles')
   <style type="text/css">
   	.uppercard{
   		height: 100px;
        padding-top: 15px;
        padding-left:0px;
        text-align: center;
   	}
    .uppercard2{
   		height: 120px;
        padding-top: 15px;
        padding-left:0px;
        text-align: center;
   	}

   	.uppercard:hover{
   		border: 3px solid skyblue;
   	}
    .uppercard2:hover{
   		border: 3px solid skyblue;
   	}
   	.divTitle{
        font-weight: bold;
   		color: black !important;
        font-size: 14px !important;
   	}
   	.divValue{
   		font-size: 25px !important;
   		font-weight: bold;
        text-align: center !important;
   	}
    .graphOverview{
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
    }
    tbody {
        height: 80em;
        overflow: scroll;
    }
    #divID table {
        border-top: 1px solid #e7e7e7;
    }

   </style>
@endsection
@section('contentBody')
    @if($empId != 0)
        @php
            $employee = \App\Models\User::where('id',$empId)->first();
        @endphp
       <div class="text-center">
            <h4>{{ $employee->emp_name }}</h4>
            <p style="font-size: 20px;color:#373757;ack;font-weight:bold;">{{ $employee->emp_id }}</p>
       </div>
    @endif

    @if($searchTitle == 'Dashboard of All')
    <form action="{{ route('admin.dashboard') }}" method="get" class="form-inline mt-1">
    @else
    <form action="{{ route('admin.dashboard_employeeById',$empId) }}" method="get" class="form-inline mt-1">
    @endif
        @php
            $start_date=null;
            if(isset($_GET['start_date'])){
                $start_date=$_GET['start_date'];
            }
            $end_date=null;
            if(isset($_GET['end_date'])){
                $end_date=$_GET['end_date'];
            }
        @endphp
        <div class="form-group mb-2">
            <span>Start Date: </span>&nbsp;<input type="date" name="start_date" value="{{ $start_date }}" class="form-control" required>
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <span>End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-rounded btn-primary mb-2"><i class="fa fa-search"></i></button>
        <div style="margin-left: 300px;">
            @if($start_date == null && $end_date == null)
                <span style="font-weight:bold;font-size:20px;">Current Month</span>
            @else
                <span style="font-weight:bold;font-size:20px;">{{ $start_date }} to {{ $end_date }}</span>
            @endif
        </div>
    </form>

    <div class="mt-2">
        <h5>Task Overview</h5>
    </div>
    <div class="row">
	   <div class="col-lg-3">
        @if($empId == 0)
	    	<a href="{{ route('admin.dashboard_task_list',[$anStartDate,$anEndDate]) }}">
        @else
            <a href="{{ route('admin.dashboard_task_list',[$anStartDate,$anEndDate,$empId]) }}">
        @endif
		        <div class="card uppercard" style="background-color: #bee0ec;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Total Task</div>
		                    <div class="stat-digit divValue">{{ $total_tasks }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	   </div>

	    <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_pending_list',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_pending_list',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #Cfbdf1;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Pending Task</div>
		                    <div class="stat-digit divValue">{{ $total_pending_tasks }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>

	    <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_processing_list',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_processing_list',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #E0be95;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Processing Task</div>
		                    <div class="stat-digit divValue">{{ $total_processing_tasks }}</div>
		                </div>
		            </div>
		        </div>
		    </a>
	    </div>

	    <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_done_list',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_done_list',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #E0d995;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Done Task</div>
		                    <div class="stat-digit divValue">{{ $total_done_tasks }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>

	   <div class="col-lg-3">
        @if($empId == 0)
            <a href="{{ route('admin.dashboard_related_action_list',[$anStartDate,$anEndDate]) }}">
        @else
            <a href="{{ route('admin.dashboard_related_action_list',[$anStartDate,$anEndDate,$empId]) }}">
        @endif
		        <div class="card uppercard" style="background-color: #e7fc9b;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Action Plan Wise</div>
		                    <div class="stat-digit divValue">{{ $total_related_action }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
        <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_agenda_wise_task',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_agenda_wise_task',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #fac9ef;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Agenda Wise Task</div>
		                    <div class="stat-digit divValue">{{ $total_agenda_wise_task }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
        <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_assigned_by_others_list',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_assigned_by_others_list',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #fce8e0;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Others Task</div>
		                    <div class="stat-digit divValue">{{ $total_assigned_by_others }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
        <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_agenda_submitted',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_agenda_submitted',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #A1f1ac;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Agenda Submitted</div>
		                    <div class="stat-digit divValue">{{ $total_agenda_submitted }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
    </div>
    <div class="mt-2">
        <h5>Tour Overview</h5>
    </div>
    <div class="row">
	   <div class="col-lg-3">
	    	<a href="{{ route('admin.dashboard_territory') }}">
		        <div class="card uppercard" style="background-color: #bee0ec;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Total Territory</div>
		                    <div class="stat-digit divValue">{{ $total_territory }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	   </div>

	    <div class="col-lg-3">
	   	    @if($empId == 0)
                <a href="{{ route('admin.dashboard_planned_territory',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_planned_territory',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #Cfbdf1;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">planned Territory</div>
		                    <div class="stat-digit divValue">{{ $total_planned_territory }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>

	    <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_visited_territory',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_visited_territory',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #E0be95;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Visited Territory</div>
		                    <div class="stat-digit divValue">{{ $total_visited_territory }}</div>
		                </div>
		            </div>
		        </div>
		    </a>
	    </div>

	    <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_not_visited_territory',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_not_visited_territory',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #E0d995;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Not Visited Territory</div>
		                    <div class="stat-digit divValue">{{ $total_not_visited_territory }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>

	   <div class="col-lg-3">
        @if($empId == 0)
            <a href="{{ route('admin.dashboard_plan_wise_visited',[$anStartDate,$anEndDate]) }}">
        @else
            <a href="{{ route('admin.dashboard_plan_wise_visited',[$anStartDate,$anEndDate,$empId]) }}">
        @endif
		        <div class="card uppercard" style="background-color: #a2d3fc;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Plan Wise visited</div>
		                    <div class="stat-digit divValue">{{ $total_plan_wise_visited }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
        <div class="col-lg-3">
            @if($empId == 0)
                <a href="{{ route('admin.dashboard_not_visited_yet',[$anStartDate,$anEndDate]) }}">
            @else
                <a href="{{ route('admin.dashboard_not_visited_yet',[$anStartDate,$anEndDate,$empId]) }}">
            @endif
		        <div class="card uppercard" style="background-color: #defcae;">
		            <div class="stat-widget-one">
		                <div class="stat-content dib">
		                    <div class="stat-text divTitle">Not Visited Yet</div>
		                    <div class="stat-digit divValue">{{ $total_not_visited_yet }}</div>
		                </div>
		            </div>
		        </div>
	        </a>
	    </div>
        @if($empId != 0)
            @php
                $employee = \App\Models\User::where('id',$empId)->first();
                $no_of_days = \App\Models\Task::where('userid', $empId)->whereBetween('date', [$anStartDate , $anEndDate])->distinct('date')->count('date');
            @endphp
            <div class="col-lg-3">
                <a href="#">
                    <div class="card uppercard" style="background-color: #d3f3fd;">
                        <div class="stat-widget-one">
                            <div class="stat-content dib">
                                <div class="stat-text divTitle">Total Active Day</div>
                                <div class="stat-digit divValue">{{ $no_of_days }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    @if($empId == 0)
        <div>
            <h5>Employee Overview</h5>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <a href="{{ route('admin.employee_list') }}">
                    <div class="card uppercard" style="background-color: #d3f3fd;">
                        <div class="stat-widget-one">
                            <div class="stat-content dib">
                                <div class="stat-text divTitle">Total Employee</div>
                                <div class="stat-digit divValue">{{ $total_employees }}</div>
                            </div>
                        </div>
                    </div>
                </a>
        </div>
        <div class="col-lg-3">
                <a href="{{ route('admin.employee_list_supervision') }}">
                    <div class="card uppercard" style="background-color: #d4fcba;">
                        <div class="stat-widget-one">
                            <div class="stat-content dib">
                                <div class="stat-text divTitle">In My Supervision</div>
                                <div class="stat-digit divValue">{{ $total_supervision_employees }}</div>
                            </div>
                        </div>
                    </div>
                </a>
        </div>
        <div class="col-lg-3">
                <a href="{{ route('admin.employee_list_active',[$anStartDate,$anEndDate]) }}">
                    <div class="card uppercard" style="background-color: #fae1ec;">
                        <div class="stat-widget-one">
                            <div class="stat-content dib">
                                <div class="stat-text divTitle">Active Employee</div>
                                <div class="stat-digit divValue">{{ $total_active_employees }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3">
                <a href="{{ route('admin.employee_list_inactive',[$anStartDate,$anEndDate]) }}">
                    <div class="card uppercard" style="background-color: #e3f8aa;">
                        <div class="stat-widget-one">
                            <div class="stat-content dib">
                                <div class="stat-text divTitle">Inactive Employee</div>
                                <div class="stat-digit divValue">{{ $total_inactive_employees }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif
    <div>
        <h5>Graphical Overview</h5>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-lg-4 col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            {{-- <div style="font-weight: bold;text-align:center;">Total Task : {{ $total_tasks }}</div> --}}
                            <div id="PendingProcessingDone" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            <div id="taskTypeOverview" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4">
                    <div class="card">
                        <div class="card-body">
                            <div id="tourOverview" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-lg-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div id="employeeOverview" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div style="min-width: 200px; max-height: 400px; overflow-y: auto; margin: 0 auto">
                                <div id="divID" class="table-responsive">
                                    <table class="table table-bordered" id="dTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Employee Name</th>
                                                <th>No. of Days</th>
                                                <th>Active</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Sort employees by the number of days
                                                $sortedEmployees = $employees->sortBy(function ($employee) use ($anStartDate, $anEndDate) {
                                                    return \App\Models\Task::where('userid', $employee->id)
                                                        ->whereBetween('date', [$anStartDate, $anEndDate])
                                                        ->distinct('date')->count('date');
                                                });
                                                $count = 1;
                                            @endphp
                                            @foreach($sortedEmployees as $key => $employee)
                                                @php
                                                    $no_of_days = \App\Models\Task::where('userid', $employee->id)
                                                        ->whereBetween('date', [$anStartDate, $anEndDate])
                                                        ->distinct('date')->count('date');
                                                @endphp
                                                <tr>
                                                    <th scope="row">{{ $count++ }}</th>
                                                    <td>{{ $employee->emp_name }}</td>
                                                    <td>{{ $no_of_days }}</td>
                                                    @if($no_of_days > 0)
                                                        <td><span style="font-size:24px;color:green">✓</span></td>
                                                    @else
                                                        <td><span style="font-size:24px;color:red">✗</span></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="https://code.highcharts.com/stock/highstock.js"></script>

    <script>
        $(document).ready(function() {
            $('#dTable').DataTable({
                "targets": 'no-sort',
                "bSort": false,
                "order": [],
                "language": {
                    "lengthMenu": "",
                },
                scrollX: true,
                scrollY: 300,
                paging: false,

            });
        });
        document.addEventListener('DOMContentLoaded', function () {

            var total_pending_tasks = {!! json_encode((float)$total_pending_tasks) !!};
            var total_processing_tasks = {!! json_encode((float)$total_processing_tasks) !!};
            var total_done_tasks = {!! json_encode((float)$total_done_tasks) !!};

            Highcharts.chart('PendingProcessingDone', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Monthly Task Overview',
                    style: {
                        fontSize: '18px' // Increase the font size of the title
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45,
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [
                        ['Pending', total_pending_tasks],
                        ['Processing', total_processing_tasks],
                        ['Done', total_done_tasks],
                    ]
                    }]
                });
            });
        document.addEventListener('DOMContentLoaded', function () {
            var total_related_action = {!! json_encode((float)$total_related_action) !!};
            var total_agenda_wise_task = {!! json_encode((float)$total_agenda_wise_task) !!};
            var total_assigned_by_others = {!! json_encode((float)$total_assigned_by_others) !!};
            Highcharts.chart('taskTypeOverview', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Task Type Overview',
                    style: {
                        fontSize: '18px' // Increase the font size of the title
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [
                        ['Related to Action Plan', total_related_action],
                        ['Agenda Wise Task', total_agenda_wise_task],
                        ['Others', total_assigned_by_others],
                    ]
                }]
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            var total_visited_territory = {!! json_encode((float)$total_visited_territory) !!};
            var total_not_visited_territory = {!! json_encode((float)$total_not_visited_territory) !!};
            var total_plan_wise_visited = {!! json_encode((float)$total_plan_wise_visited) !!};
            Highcharts.chart('tourOverview', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Tour Overview',
                    style: {
                        fontSize: '18px' // Increase the font size of the title
                    }
                },
                plotOptions: {
                    pie: {

                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true,
                        innerSize: '50%',
                        depth: 45,
                        startAngle: -90,
                        endAngle: 90,
                        center: ['50%', '75%']
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [
                        ['Visited', total_visited_territory],
                        ['Not Visited', total_not_visited_territory],
                        ['Plan Wise Visited', total_plan_wise_visited],
                    ]
                }]
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            var total_active_employees = {!! json_encode((float)$total_active_employees) !!};
            var total_inactive_employees = {!! json_encode((float)$total_inactive_employees) !!};
            Highcharts.chart('employeeOverview', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Employee Overview',
                    style: {
                        fontSize: '18px' // Increase the font size of the title
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [
                        ['Active Employee', total_active_employees],
                        ['Inactive Employee', total_inactive_employees],
                    ]
                }]
            });
        });
    </script>


@endsection
