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
        @php
            $task_rating = \App\Models\Review::where('userid', $empId)
                ->whereBetween('date', [$sDate, $eDate])
                ->sum('rta_rating');

            $other_rating = \App\Models\Review::where('userid', $empId)
                ->whereBetween('date', [$sDate, $eDate])
                ->sum('other_rating');

            $total_overall_rating = ($task_rating * 2 * 0.7) + ($other_rating * 2 * 0.3);
            $total_overall_rating = number_format($total_overall_rating, 2);

            $totalRatingCount = \App\Models\Review::where('userid', $empId)
                ->whereBetween('date', [$sDate, $eDate])
                ->count();

            if ($totalRatingCount > 0) {
                $total_avg = $total_overall_rating / $totalRatingCount;
                $total_avg = number_format($total_avg , 2);
            } else {
                $total_avg = 0;
            }
        @endphp
        <div class="row" style="float: right;">
            <div class="col-lg-12">
                <a href="#">
                    <div class="card" style="background-color: #bee0ec;">
                        <div class="divTitle">Total Average Rating</div>
                        <div class="stat-digit divValue text-center">{{ $total_avg }}</div>
                    </div>
                </a>
            </div>
        </div>
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
                        <th>Supervision ID</th>
                        <th>Rating</th>
                        <th>Action Plan Review</th>
                        <th>Other Task Review</th>
                        <th>Date</th>
                    </tr>

                </thead>
                <tbody>
                    @foreach ($reviews as $key => $review)
                        @php
                            $employee = \App\Models\User::where('id', $review->userid)->first();

                            $totalRating = ($review->rta_rating * 2 * 0.7) +($review->other_rating * 2 * 0.3);
                            $totalRating = number_format($totalRating, 2);
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $employee->emp_name }}</td>
                            <td>{{ $employee->emp_id }}</td>
                            <td>{{ $employee->emp_designation }}</td>
                            <td>{{ $employee->sup_id }}</td>
                            <td>{{ $totalRating }}</td>
                            <td>{{ $review->rta_review }}</td>
                            <td>{{ $review->other_review }}</td>
                            <td>{{ $review->date }}</td>
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
