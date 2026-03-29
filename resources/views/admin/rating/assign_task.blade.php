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
                        <th>Today's Overview</th>
                        <th>Action</th>
                    </tr>

                </thead>
                <tbody>
                    @foreach ($employees as $key => $employee)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td> <a href="{{ route('admin.dashboard_employeeById', $employee->id) }}"
                                    target="_blank">{{ $employee->emp_name }}</a></td>
                            <td>{{ $employee->emp_id }}</td>
                            <td>{{ $employee->emp_designation }}</td>
                            <td> <a href="{{ route('admin.todaysOverview_employeeById', [$employee->id,$mydate]) }}" target="_blank"
                                    class="btn btn-primary">View</a></td>
                            <td>
                                <a href="{{ route('admin.emp_create_assign_task', ['employee_id' => $employee->id]) }}"
                                    class="btn btn-primary">Add Task</a>
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
