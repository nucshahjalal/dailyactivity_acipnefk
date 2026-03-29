@extends('admin.layouts.open_link_master')
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style type="text/css">
        #map {
            height: 500px;
        }

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
    <form action="{{ route('open_link_dashboard') }}" method="get" class="form-inline mt-1">

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
    <div class="row">
        <div class="col-12">
            <h5>Actual Visit OverView Map</h5>
            <div id="map"></div>
        </div>
    </div>
    <div class="mt-2">
        <h5>Graphical Overview</h5>
    </div>
    <div class="row">
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpOverview" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpRecovery" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpService" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="visitedVSnotvisited" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="planVSactualvisit" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div id="hqEmpVSnotPlanned" style="min-width: 200px; height: 400px; margin: 0 auto"></div>
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
                            <div style="min-width: 200px; max-height: 400px; overflow-y: auto; margin: 0 auto">
                                <div id="divID" class="table-responsive mt-1">
                                    <table class="table table-bordered" id="dTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>No. of Tours</th>
                                                <th>Active</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $count = 1;
                                            @endphp
                                            @foreach ($employeesWithTourCount as $employee)
                                                <tr>
                                                    <th scope="row">{{ $count++ }}</th>
                                                    <td>{{ $employee->emp_name }}</td>
                                                    <td>{{ $employee->emp_id }}</td>
                                                    <td>{{ $employee->no_of_tours }}</td>
                                                    @if ($employee->no_of_tours > 0)
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
                <div class="col-lg-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Territory Wise Visit Plan Report</h5>
                            <div
                                style="min-width: 200px; max-height: 400px; overflow-y: auto;overflow-x: auto; margin: 0 auto">
                                <div id="divID" class="table-responsive mt-1">
                                    <table class="table table-bordered" id="dTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Territory Name</th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Designation</th>
                                                <th>Created Date</th>
                                                <th>Details</th>
                                                <th>Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $count = 1;
                                            @endphp
                                            @foreach ($territoryVisitPlans as $territoryVisitPlan)
                                                @php
                                                    $employee = \App\Models\User::where(
                                                        'id',
                                                        $territoryVisitPlan->userid,
                                                    )->first();

                                                @endphp
                                                @if (isset($employee))
                                                    <tr>
                                                        <th scope="row">{{ $count++ }}</th>
                                                        <td>{{ $territoryVisitPlan->seleted_territory }}</td>
                                                        <td>{{ $employee->emp_name }}</td>
                                                        <td>{{ $employee->emp_id }}</td>
                                                        <td>{{ $employee->emp_designation }}</td>
                                                        <td>{{ $territoryVisitPlan->date }}</td>
                                                        <td>{{ $territoryVisitPlan->details }}</td>
                                                        <td></td>

                                                    </tr>
                                                @endif
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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalEmployee) !!};
            var existingUsers = {!! json_encode((float) $existingUsers) !!};
            var notExistingUsers = {!! json_encode((float) $notExistingUsers) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (existingUsers / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpOverview', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'HQ Employee Overview (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45,
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
                            name: 'Existing Employee',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Existing Employee',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
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
                    text: 'HQ Employee Overview Recovery (Total: ' + totalEmployee +
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
                            name: 'Existing Recovery Employee',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Existing Recovery Employee',
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
                    text: 'HQ Employee Overview Service (Total: ' + totalEmployee +
                        ')', // Display totalEmployee in title
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45,
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
                            name: 'Existing Service Employee',
                            y: existingUsers,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Existing Service Employee',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            var total_territory = {!! json_encode((float) $total_territory) !!};
            var total_visited_territory = {!! json_encode((float) $total_visited_territory) !!};
            var total_not_visited_territory = {!! json_encode((float) $total_not_visited_territory) !!};

            // Calculate percentages for existing and not existing
            var existingPercentage = (total_visited_territory / total_territory) * 100;
            var notExistingPercentage = (total_not_visited_territory / total_territory) * 100;

            Highcharts.chart('visitedVSnotvisited', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Territory Visited Vs Not Visited (Total: ' + total_territory +
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
                            name: 'Visited Territory',
                            y: total_visited_territory,
                            percentage: existingPercentage
                        },
                        {
                            name: 'Not Visited Territory',
                            y: total_not_visited_territory,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var total_territory = {!! json_encode((float) $total_territory) !!};
            var total_planned_territory = {!! json_encode((float) $total_planned_territory) !!};
            var total_plan_wise_visited = {!! json_encode((float) $total_plan_wise_visited) !!};

            // Calculate percentages for existing and not existing
            var planned_territoryPercentage = (total_planned_territory / total_territory) * 100;
            var plan_wise_visitedPercentage = (total_plan_wise_visited / total_territory) * 100;

            Highcharts.chart('planVSactualvisit', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Plan Wise Visit Vs Actual Visit Overview',
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
                            y: total_planned_territory,
                            percentage: planned_territoryPercentage
                        },
                        {
                            name: 'Actual Visit',
                            y: total_plan_wise_visited,
                            percentage: plan_wise_visitedPercentage
                        }
                    ]
                }]
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            var totalEmployee = {!! json_encode((float) $totalEmployee) !!};
            var notExistingUsers = {!! json_encode((float) $notExistingUsers) !!};

            var totalPercentage = (totalEmployee / totalEmployee) * 100;
            var notExistingPercentage = (notExistingUsers / totalEmployee) * 100;

            Highcharts.chart('hqEmpVSnotPlanned', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Total HQ Employee Vs Not Planned Employee',
                    style: {
                        fontSize: '18px'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45,
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
                            name: 'Total HQ Employee',
                            y: totalEmployee,
                            percentage: totalPercentage
                        },
                        {
                            name: 'Not Planned Employee',
                            y: notExistingUsers,
                            percentage: notExistingPercentage
                        }
                    ]
                }]
            });
        });

        $(document).ready(function() {
            // Initialize the map
            const map = L.map('map').setView([23.6850, 90.3563], 7); // Center on Bangladesh

            // Add a tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add GeoJSON data to the map
            const upazilas = @json($upazilas);

            L.geoJSON(upazilas, {
                style: function(feature) {
                    // Calculate total visits for this area
                    let totalVisits = 0;
                    if (feature.properties.visit_counts) {
                        totalVisits = Object.values(feature.properties.visit_counts).reduce((sum,
                            count) => sum + count, 0);
                    }

                    return {
                        fillColor: totalVisits > 0 ? '#e65c00' :
                        '#FFFFFF', // Orange if visits exist, else white
                        fillOpacity: 0.3,
                        color: '#000000', // Border color black
                        weight: 1
                    };
                },
                onEachFeature: function(feature, layer) {
                    const name = feature.properties.NAME_4;
                    const visitCounts = feature.properties.visit_counts || {};

                    // Format user-wise visit counts
                    let visitDetails = Object.entries(visitCounts).map(([user, count]) => {
                        return `<b>${user}</b>: ${count} visits`;
                    }).join('<br>');

                    if (!visitDetails) {
                        visitDetails = 'No visits recorded';
                    }

                    // Create a popup with detailed visit info
                    const popupContent = `
                <div style="color: #000000;">
                    <b>${name}</b><br>
                    ${visitDetails}
                </div>
            `;

                    // Bind the popup to the layer
                    layer.bindPopup(popupContent);
                }
            }).addTo(map);
        });
    </script>
@endsection
