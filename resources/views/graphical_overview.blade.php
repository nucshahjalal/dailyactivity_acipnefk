@extends('admin.layouts.open_link_master')
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style type="text/css">
        .uppercard {
            height: 100px;
            padding-top: 15px;
            padding-left: 0px;
            text-align: center;
        }

        .uppercard2 {
            height: 120px;
            padding-top: 15px;
            padding-left: 0px;
            text-align: center;
        }

        .uppercard:hover {
            border: 3px solid skyblue;
        }

        .uppercard2:hover {
            border: 3px solid skyblue;
        }

        .divTitle {
            font-weight: bold;
            color: black !important;
            font-size: 14px !important;
        }

        .divValue {
            font-size: 25px !important;
            font-weight: bold;
            text-align: center !important;
        }

        .graphOverview {
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }

        #divID table {
            border-top: 1px solid #e7e7e7;
        }
    </style>
@endsection
@section('contentBody')
    <form action="{{ route('graphical_overview') }}" method="get" class="form-inline mt-1">

        @php
            $start_date = null;
            if (isset($_GET['start_date'])) {
                $start_date = $_GET['start_date'];
            }
            $end_date = null;
            if (isset($_GET['end_date'])) {
                $end_date = $_GET['end_date'];
            }
        @endphp
        <div class="form-group mb-2">
            <span>Start Date: </span>&nbsp;<input type="date" name="start_date" value="{{ $start_date }}"
                class="form-control" required>
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <span>End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}"
                class="form-control" required>
        </div>
        <button type="submit" class="btn btn-rounded btn-primary mb-2"><i class="fa fa-search"></i></button>
        <div style="margin-left: 300px;">
            @if ($start_date == null && $end_date == null)
                <span style="font-weight:bold;font-size:20px;">Current Month</span>
            @else
                <span style="font-weight:bold;font-size:20px;">{{ $start_date }} to {{ $end_date }}</span>
            @endif
        </div>
    </form>
    <div class="mt-2">
        <h5>Graphical Overview</h5>
    </div>
    <h6>Total HQ Employee Tour Plan OverView</h6>
    <div class="row">
        <div class="col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div id="planVsNotPlan" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div id="actualVisitedVSnotvisited" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="planVisitedVSnotVisited" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div> --}}

    </div>
    <h6>Marketing Team OverView</h6>
    <div class="row">
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpMarketingSUB11" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpMarketingSUB12" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpMarketingSUB13" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
    </div>
    <h6>Others Team OverView</h6>
    <div class="row">
        <div class="col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpRecovery" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpService" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
    </div>

    <h6>Territory Coverage</h6>
    <div class="row">
        <div class="col-lg-3 col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div id="territoryWisePlanned" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div id="territoryWiseActual" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div id="employeeRatingOverview" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
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
                            <h5>Activity Report</h5>
                            <div>
                                <div id="divID" class="table-responsive mt-1">
                                    <table class="table table-bordered" id="dTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                {{-- <th>Total Active Visit</th> --}}
                                                <th>Plan Visit</th>
                                                <th>Actual Visit</th>
                                                <th>Active</th>
                                                <th>Status</th>
                                                <th>Business</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $count = 1;
                                            @endphp
                                            @foreach ($employeesWithTourCount as $key => $employee)
                                                @php
                                                    if ($employee->status == 'Inactive') {
                                                        $statusClass = 'text-danger';
                                                    } else {
                                                        $statusClass = 'text-success';
                                                    }
                                                @endphp
                                                <tr>
                                                    <th scope="row">{{ $count++ }}</th>
                                                    <td>{{ $employee->emp_name }}</td>
                                                    <td>{{ $employee->emp_id }}</td>
                                                    {{-- <td>{{ $employee->no_of_tours }}</td> --}}
                                                    <td>{{ $employee->selected_territory_count }}</td>
                                                    <td>{{ $employee->visited_territory_count }}</td>
                                                    @if ($employee->no_of_tours > 0)
                                                        <td><span style="font-size:24px;color:green">✓</span></td>
                                                    @else
                                                        <td><span style="font-size:24px;color:red">✗</span></td>
                                                    @endif
                                                    <td class="{{ $statusClass }}">{{ $employee->status }}</td>
                                                    <td>{{ $employee->portfolio }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-lg-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Territory Wise Visit Plan Report</h5>
                            <div>
                                <div id="divID" class="table-responsive mt-1">
                                    <table class="table table-bordered" id="Ttable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Territory Name</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Designation</th>
                                                <th>Total Plan Visit</th>
                                                <th>Total Actual Visit</th>
                                                <th>Performance %</th>
                                                <th>Rank</th>
                                                <th>Rating</th>
                                                <th>Latest Date</th>
                                                <th>Latest Purpose</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>#</th>
                                                <th>Territory Name</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Designation</th>
                                                <th>Total Plan Visit</th>
                                                <th>Total Actual Visit</th>
                                                <th>Performance %</th>
                                                <th>Rank</th>
                                                <th>Rating</th>
                                                <th>Latest Date</th>
                                                <th>Latest Purpose</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            @foreach ($rankedPlans as $row)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $row->{'Sales & Recovery Territory Name'} }}</td>
                                                    <td>{{ $row->emp_name }}</td>
                                                    <td>{{ $row->emp_id }}</td>
                                                    <td>{{ $row->emp_designation }}</td>
                                                    <td>{{ $row->planned_visits }}</td>
                                                    <td>{{ $row->actual_visits }}</td>
                                                    <td>{{ $row->performance }}%</td>
                                                    <td>{{ $row->rank }}</td>
                                                    <td>
                                                        @if ($row->rating === 'Excellent')
                                                            <span class="badge bg-success">{{ $row->rating }}</span>
                                                        @elseif($row->rating === 'Good')
                                                            <span class="badge bg-primary">{{ $row->rating }}</span>
                                                        @elseif($row->rating === 'Average')
                                                            <span
                                                                class="badge bg-warning text-dark">{{ $row->rating }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $row->rating }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $row->latest_date }}</td>
                                                    <td>{{ $row->latest_details }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <div class="col-lg-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Territory Wise Visit Plan Report</h5>
                            <div>
                                <div id="divID" class="table-responsive mt-1">
                                    <table class="table table-bordered" id="Ttable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Territory Name</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Designation</th>
                                                <th>Total Plan Visit(Upazilla)</th>
                                                <th>Total Actual Visit(Upazilla)</th>
                                                {{-- <th>Performance %</th> --}}
                                                <th>Rating</th>
                                                <th>Latest Date</th>
                                                <th>Latest Purpose</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>#</th>
                                                <th>Territory Name</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Designation</th>
                                                <th>Total Plan Visit(Upazilla)</th>
                                                <th>Total Actual Visit(Upazilla)</th>
                                                {{-- <th>Performance %</th> --}}
                                                <th>Rating</th>
                                                <th>Latest Date</th>
                                                <th>Latest Purpose</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            @php
                                                $count = 1;
                                                $performanceData = collect();

                                                // Group by territory and employee
                                                $groupedData = $territoryVisitPlans->groupBy([
                                                    function ($item) {
                                                        return $item->{'Sales & Recovery Territory Name'};
                                                    },
                                                    function ($item) {
                                                        return $item->emp_name;
                                                    },
                                                ]);

                                                foreach ($groupedData as $territoryName => $employees) {
                                                    foreach ($employees as $employeeName => $plans) {
                                                        $firstPlan = $plans->first();
                                                        $totalPlanned = $plans->sum('planned_visits');
                                                        $totalActual = $plans->sum('actual_visits');
                                                        $planPerformance =
                                                            $totalPlanned > 0
                                                                ? round(($totalActual / $totalPlanned) * 100, 2)
                                                                : 0;

                                                        if ($planPerformance >= 90) {
                                                            $planRating = 'Excellent';
                                                        } elseif ($planPerformance >= 75) {
                                                            $planRating = 'Good';
                                                        } elseif ($planPerformance >= 50) {
                                                            $planRating = 'Average';
                                                        } else {
                                                            $planRating = 'Poor';
                                                        }

                                                        $performanceData->push([
                                                            'territory' => $territoryName,
                                                            'employee' => $employeeName,
                                                            'emp_id' => $firstPlan->emp_id,
                                                            'designation' => $firstPlan->emp_designation,
                                                            'totalPlanned' => $totalPlanned,
                                                            'totalActual' => $totalActual,
                                                            'performance' => $planPerformance,
                                                            'rating' => $planRating,
                                                            'latest_date' => $plans->max('latest_date'),
                                                            'latest_details' => optional(
                                                                $plans->sortByDesc('latest_date')->first(),
                                                            )->latest_details,
                                                        ]);
                                                    }
                                                }

                                                // Sort by performance desc
                                                $sortedData = $performanceData->sortByDesc('performance');
                                            @endphp

                                            @foreach ($sortedData as $row)
                                                <tr>
                                                    <th scope="row">{{ $count++ }}</th>
                                                    <td>{{ $row['territory'] }}</td>
                                                    <td>{{ $row['employee'] }}</td>
                                                    <td>{{ $row['emp_id'] }}</td>
                                                    <td>{{ $row['designation'] }}</td>
                                                    <td>{{ $row['totalPlanned'] }}</td>
                                                    <td>{{ $row['totalActual'] }}</td>
                                                    <td><a class="viewRating btn btn-sm btn-primary" href="#"
                                                            empId="{{ $row['emp_id'] }}" data-bs-toggle="modal"
                                                            data-bs-target="#myModal">View</a></td>
                                                    {{-- <td>{{ $row['performance'] }}%</td>
                                                    <td>
                                                        @if ($row['rating'] === 'Excellent')
                                                            <span class="badge bg-success">{{ $row['rating'] }}</span>
                                                        @elseif($row['rating'] === 'Good')
                                                            <span class="badge bg-primary">{{ $row['rating'] }}</span>
                                                        @elseif($row['rating'] === 'Average')
                                                            <span
                                                                class="badge bg-warning text-dark">{{ $row['rating'] }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $row['rating'] }}</span>
                                                        @endif
                                                    </td> --}}
                                                    <td>{{ $row['latest_date'] }}</td>
                                                    <td>{{ $row['latest_details'] }}</td>
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
<!-- The Modal -->
<div class="modal" id="myModal">
    <div class="modal-dialog" style="max-width: 1200px!important;">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Rating Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div id="rating_list_modal">

                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).on('click', '.viewRating', function() {
            var empId = $(this).attr('empId');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({

                url: "<?php echo route('show_appuser_rating_details'); ?>",

                method: 'POST',

                data: {
                    empId: empId
                },

                success: function(data) {
                    $('#rating_list_modal').html('');
                    $('#rating_list_modal').html(data.options);
                }
            });
        });
        $(document).ready(function() {
            $('#dTable').DataTable({
                "pageLength": 10,
                "targets": 'no-sort',
                "bSort": false,
                "order": [],
                "language": {
                    "lengthMenu": "",
                },
                dom: '<"top"lfB>rt<"bottom"ip><"clear">',
                buttons: [
                    'excel'
                ],
                "columnDefs": [{
                    "targets": [5],
                    "searchable": true
                }]
            });
        });
        $(document).ready(function() {
            $('#Ttable').DataTable({
                "pageLength": 10,
                "targets": 'no-sort',
                "bSort": false,
                "order": [],
                "language": {
                    "lengthMenu": "",
                },
                dom: '<"top"lfB>rt<"bottom"ip><"clear">',
                buttons: [
                    'excel'
                ]
            });
        });
        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalEmployee) !!};
            var existingUsers = {!! json_encode((float) $existingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('planVsNotPlan', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Tour Plan Vs No Tour Plan (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Tour Planning (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'No Tour Planning (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            var totalEmployee = {!! json_encode((float) $totalEmployee) !!};
            var total_actual_visit = {!! json_encode((float) $total_actual_visit) !!};
            var total_not_actual_visit = {!! json_encode((float) $total_not_actual_visit) !!};

            // Calculate percentages for existing and not existing
            var totalActualVisitPercentage = (total_actual_visit / totalEmployee) * 100;
            var totalNotActualVisitPercentage = (total_not_actual_visit / totalEmployee) * 100;

            Highcharts.chart('actualVisitedVSnotvisited', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Actual Visited Vs Not Visited (Total: ' + totalEmployee +
                        ')',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['#28a745',
                            '#dc3545'
                        ]
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'No Of Visiting Employee (' + total_actual_visit + ') ',
                            y: total_actual_visit,
                            percentage: totalActualVisitPercentage
                        },
                        {
                            name: 'No of unvisiting employee (' + total_not_actual_visit + ') ',
                            y: total_not_actual_visit,
                            percentage: totalNotActualVisitPercentage
                        }
                    ]
                }]
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            var totalEmployee = {!! json_encode((float) $totalEmployee) !!};
            var total_planned_visit = {!! json_encode((float) $total_planned_visit) !!};
            var total_unplanned_visit = {!! json_encode((float) $total_unplanned_visit) !!};

            // Calculate percentages for existing and not existing
            var plannedVisitedPercentage = (total_planned_visit / totalEmployee) * 100;
            var notPlanedvisitedPercentage = (total_unplanned_visit / totalEmployee) * 100;

            Highcharts.chart('planVisitedVSnotVisited', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Planed Visited Vs Not Visited (Total: ' + totalEmployee +
                        ')',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['green',
                            'orange'
                        ]

                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Plan Wise Visit',
                            y: total_planned_visit,
                            percentage: plannedVisitedPercentage
                        },
                        {
                            name: 'Actual Visit',
                            y: total_unplanned_visit,
                            percentage: notPlanedvisitedPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalRecoveryEmployee) !!};
            var existingUsers = {!! json_encode((float) $recoveryExistingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notRecoveryExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpRecovery', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Recovery Team (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['#28a745',
                            '#dc3545'
                        ] // Custom colors for the segments (green for existing, red for not existing)
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Planned Recovery Employee  (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Planned Recovery Employee  (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });
        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalServiceEmployee) !!};
            var existingUsers = {!! json_encode((float) $serviceExistingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notServiceExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpService', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Service Team (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Planned Service Employee (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Planned Service Employee (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalMerketing_SUB11_Employee) !!};
            var existingUsers = {!! json_encode((float) $marketing_SUB11_ExistingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notMarketing_SUB11_ExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpMarketingSUB11', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'New Machineries Planned Vs Unplanned (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['green',
                            'yellow'
                        ]
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Planned (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Unplanned (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalMerketing_SUB12_Employee) !!};
            var existingUsers = {!! json_encode((float) $marketing_SUB12_ExistingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notMarketing_SUB12_ExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpMarketingSUB12', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Tractor Planned Vs Unplanned (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Planned (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Unplanned (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });


        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalMerketing_SUB13_Employee) !!};
            var existingUsers = {!! json_encode((float) $marketing_SUB13_ExistingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notMarketing_SUB13_ExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpMarketingSUB13', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Construction Equipment Planned Vs Unplanned (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['skyblue',
                            'orange'
                        ]
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Planned (' + existingUsers + ') ',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Unplanned (' + notExistingUsers + ') ',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var total_territory = {!! json_encode((float) $total_territory) !!};
            var totalplanned_visit = {!! json_encode((float) $total_territory_wise_planned_visit) !!};
            var totalnot_planned_visit = {!! json_encode((float) $total_territory_wise_not_planned_visit) !!};

            // Calculate percentages for existing and not existing
            var totalPlannedVisitPercentage = (totalplanned_visit / total_territory) * 100;
            var totalNotPlannedVisitPercentage = (totalnot_planned_visit / total_territory) * 100;

            Highcharts.chart('territoryWisePlanned', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Plan Wise Territory Coverage (Total: ' + total_territory +
                        ')',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['green',
                            'orange'
                        ]
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Touched (' + totalplanned_visit + ') ',
                            y: totalplanned_visit,
                            percentage: totalPlannedVisitPercentage
                        },
                        {
                            name: 'Untouched (' + totalnot_planned_visit + ') ',
                            y: totalnot_planned_visit,
                            percentage: totalNotPlannedVisitPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var total_territory = {!! json_encode((float) $total_territory) !!};
            var totalactual_visit = {!! json_encode((float) $total_territory_wise_actual_visit) !!};
            var totalnot_actual_visit = {!! json_encode((float) $total_territory_wise_not_actual_visit) !!};

            // Calculate percentages for existing and not existing
            var totalActualVisitPercentage = (totalactual_visit / total_territory) * 100;
            var totalNotActualVisitPercentage = (totalnot_actual_visit / total_territory) * 100;

            Highcharts.chart('territoryWiseActual', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Actual Territory Coverage (Total: ' + total_territory +
                        ')',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                        colors: ['blue',
                            'yellow'
                        ]
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Touched (' + totalactual_visit + ') ',
                            y: totalactual_visit,
                            percentage: totalActualVisitPercentage
                        },
                        {
                            name: 'Untouched (' + totalnot_actual_visit + ') ',
                            y: totalnot_actual_visit,
                            percentage: totalNotActualVisitPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var totalEmployee = {!! json_encode((float) 100) !!};
            var happy = {!! json_encode((float) 50) !!};
            var unhappy = {!! json_encode((float) 40) !!};
            var neutral = {!! json_encode((float) 10) !!};

            // Calculate percentages for existing and not existing
            var happytPercentage = (happy / totalEmployee) * 100;
            var unhappyPercentage = (unhappy / totalEmployee) * 100;
            var neutralPercentage = (neutral / totalEmployee) * 100;

            Highcharts.chart('employeeRatingOverview', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'HQ Employee Wise Rating OverView (Total: ' + totalEmployee +
                        ')',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Enable data labels
                            format: '{point.name}: {point.percentage:.1f}%', // Show percentage
                            style: {
                                fontWeight: 'bold',
                                color: 'black'
                            }
                        },
                        showInLegend: true,
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [{
                            name: 'Good (' + happy + ') ',
                            y: happy,
                            percentage: happytPercentage
                        },
                        {
                            name: 'Bad (' + unhappy + ') ',
                            y: unhappy,
                            percentage: unhappyPercentage
                        },
                        {
                            name: 'Neutral (' + neutral + ') ',
                            y: neutral,
                            percentage: neutralPercentage
                        }
                    ]
                }]
            });
        });
    </script>
@endsection
