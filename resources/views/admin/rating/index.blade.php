@extends('admin.layouts.master2')
@section('styles')
    <style type="text/css">
        #divID table {
            border-top: 1px solid #e7e7e7;
        }

        .divtd a:hover {
            color: black !important;
            font-weight: bold;
        }
    </style>
@endsection
@section('contentBody')
    {{-- <div class="col-lg-12">
            <div class="card"> --}}
    <div class="card-title">
        <h4 class="text-warning">{{ $title }}</h4>
        <form style="margin-left:400px;" action="{{ route('admin.rating_review_on_my_supervision') }}" method="get" class="form-inline mt-1">
            @php
                $start_date = null;
                if (isset($_GET['start_date'])) {
                    $start_date = $_GET['start_date'];
                }
                // $end_date=null;
                // if(isset($_GET['end_date'])){
                //     $end_date=$_GET['end_date'];
                // }
            @endphp
            <div class="form-group mb-2">
                <span class="text-dark">Date: </span>&nbsp;<input type="date" name="start_date"
                    value="{{ $mydate }}" max="{{ date('Y-m-d') }}" class="form-control" required>
            </div>
            {{-- <div class="form-group mx-sm-3 mb-2">
                <span class="text-dark">End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}" class="form-control" required>
            </div> --}}
            <button type="submit" class="btn btn-rounded btn-primary mb-2 ml-2"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <div class="card-body">
        <div id="divID" class="table-responsive">
            <table class="table table-bordered" id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Designation</th>
                        @if ($start_date == null)
                            <th>Today's Overview</th>
                            <th>Today's Rating</th>
                        @else
                            <th>{{ $start_date }}'s Overview</th>
                            <th>{{ $start_date }}'s Rating</th>
                        @endif
                        <th>Monthly Rating</th>
                        <th>Action</th>
                    </tr>

                </thead>
                <tbody>
                    @foreach ($employees as $key => $employee)
                        @php
                            $today_task_rating = \App\Models\Review::where([
                                'userid' => $employee->id,
                                'date' => $mydate,
                            ])->sum('rta_rating');
                            $today_other_rating = \App\Models\Review::where([
                                'userid' => $employee->id,
                                'date' => $mydate,
                            ])->sum('other_rating');
                            $total_today_rating = ((($today_task_rating * 2) * 0.70) + (($today_other_rating * 2) * 0.30)) / 2;

                            $monthStart = \Carbon\Carbon::parse($mydate)->startOfMonth()->toDateString();
                            $monthEnd = \Carbon\Carbon::parse($mydate)->endOfMonth()->toDateString();

                            $monthly_task_rating = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$monthStart, $monthEnd])
                                ->sum('rta_rating');

                            $monthly_other_rating = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$monthStart, $monthEnd])
                                ->sum('other_rating');

                            $total_monthly_rating = (($monthly_task_rating * 2) * 0.70) + (($monthly_other_rating * 2) * 0.30);
                            $total_monthly_rating = number_format($total_monthly_rating, 2);

                            // Calculate the number of days in the month
                            // $days_in_month = \Carbon\Carbon::parse($mydate)->daysInMonth;
                            // Calculate the monthly average rating

                            $givenDate = Carbon\Carbon::parse($mydate);

                            // Initialize day count
                            $day_of_today = 0;

                            $startOfMonth = $givenDate->copy()->startOfMonth(); // Clone the given date
                            $today = $givenDate;

                            // Loop through all days until today
                            for ($date = $startOfMonth; $date->lte($today); $date->addDay()) {
                                // Exclude Fridays (5) and Saturdays (6)
                                if (
                                    $date->dayOfWeek != Carbon\Carbon::FRIDAY &&
                                    $date->dayOfWeek != Carbon\Carbon::SATURDAY
                                ) {
                                    $day_of_today++;
                                }
                            }
                            $totalRating = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$monthStart, $monthEnd])
                                ->count();

                            // $day_of_today = \Carbon\Carbon::parse($mydate)->day;
                            if($totalRating > 0){
                                $monthly_average_rating = $total_monthly_rating / $totalRating;
                                $monthly_average_rating = number_format($monthly_average_rating, 2);
                            }else{
                                $monthly_average_rating = 0;
                            }


                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td> <a href="{{ route('admin.dashboard_employeeById', $employee->id) }}"
                                    target="_blank">{{ $employee->emp_name }}</a></td>
                            <td>{{ $employee->emp_id }}</td>
                            <td>{{ $employee->emp_designation }}</td>
                            <td> <a href="{{ route('admin.todaysOverview_employeeById', [$employee->id,$mydate]) }}" target="_blank"
                                    class="btn btn-primary">View</a></td>
                            <td>{{ $total_today_rating }}</td>
                            <td>{{ $monthly_average_rating }}</td>
                            <td>
                                <a href="{{ route('admin.rating_review_add', ['employee_id' => $employee->id,'mydate' => $mydate]) }}"
                                    class="btn btn-primary">Add Rating & Review</a>
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    {{-- </div>
        </div> --}}
@endsection
@section('scripts')
@endsection
