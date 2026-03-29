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
    <div class="card-title">
        <h4 class="text-warning">{{ $title }}</h4>
        <form style="margin-left:250px;" action="{{ route('admin.rating_review_all_employee') }}" method="get" class="form-inline mt-1">
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
                <span class="text-dark">Date: </span>&nbsp;<input type="date" name="start_date"
                    value="{{ $sDate }}" class="form-control" required>
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <span class="text-dark">End Date: </span>&nbsp;<input type="date" name="end_date"
                value="{{ $eDate }}" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-rounded btn-primary mb-2 ml-2"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <div class="card-body">
        @php
            // Sort the employees based on average rating in ascending order
            $sortedEmployees = $employees->sortBy(function($employee) use ($sDate, $eDate) {
                $task_rating = \App\Models\Review::where('userid', $employee->id)
                    ->whereBetween('date', [$sDate, $eDate])
                    ->sum('rta_rating');

                $other_rating = \App\Models\Review::where('userid', $employee->id)
                    ->whereBetween('date', [$sDate, $eDate])
                    ->sum('other_rating');

                $total_overall_rating = (($task_rating * 2) * 0.70) + (($other_rating * 2) * 0.30);

                $totalRatingCount = \App\Models\Review::where('userid', $employee->id)
                    ->whereBetween('date', [$sDate, $eDate])
                    ->count();

                if ($totalRatingCount > 0) {
                    $total_average_rating = $total_overall_rating / $totalRatingCount;
                    return number_format($total_average_rating, 2);
                } else {
                    return 0;
                }
            });
        @endphp
        <div id="divID" class="table-responsive">
            <table class="table table-bordered" id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Designation</th>
                        <th>Supervision ID</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $count = 1;
                    @endphp
                    @foreach ($sortedEmployees as $key => $employee)
                        @php
                            $task_rating = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$sDate, $eDate])
                                ->sum('rta_rating');

                            $other_rating = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$sDate, $eDate])
                                ->sum('other_rating');

                            $total_overall_rating = (($task_rating * 2) * 0.70) + (($other_rating * 2) * 0.30);
                            $total_overall_rating = number_format($total_overall_rating, 2);

                            $totalRatingCount = \App\Models\Review::where('userid', $employee->id)
                                ->whereBetween('date', [$sDate, $eDate])
                                ->count();

                            if($totalRatingCount > 0){
                                $total_average_rating = $total_overall_rating / $totalRatingCount;
                                $total_average_rating = number_format($total_average_rating, 2);
                            }else{
                                $total_average_rating = 0;
                            }
                        @endphp
                        <tr>
                            <th scope="row">{{ $count++ }}</th>
                            <td> <a href="{{ route('admin.in_emp_over_all_rating', [$employee->id, $sDate, $eDate]) }}">{{ $employee->emp_name }}</a></td>
                            <td>{{ $employee->emp_id }}</td>
                            <td>{{ $employee->emp_designation }}</td>
                            <td>{{ $employee->sup_id }}</td>
                            <td>{{ $total_average_rating }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
