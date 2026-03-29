@extends('admin.layouts.master2')
@section('styles')
   <style type="text/css">
        #divID table {
            border-top: 1px solid #e7e7e7;
        }
        .divtd a:hover{
            color:  black !important;
            font-weight: bold;
        }
   </style>
@endsection
@section('contentBody')

        {{-- <div class="col-lg-12">
            <div class="card"> --}}
                <div class="card-title">
                    <h4 class="text-warning">{{ $title }}</h4>

                    {{-- <form action="{{ route('admin.employee_list') }}" method="get" class="form-inline mt-3">
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
                            <span>Start Date: </span>&nbsp;<input type="date" name="start_date" value="{{ $start_date }}" class="form-control">
                        </div>
                        <div class="form-group mx-sm-3 mb-2">
                            <span>End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-rounded btn-primary mb-2"><i class="fa fa-search"></i></button>
                    </form> --}}
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
                                    <th>Portfolio</th>
                                    <th>Team</th>
                                    <th>Supervisor ID</th>
                                </tr>

                            </thead>
                            <tbody>
                                @foreach($employees as $key=>$employee)
                                    @php

                                    @endphp
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td> <a href="{{ route('admin.dashboard_employeeById',$employee->id) }}" target="_blank">{{ $employee->emp_name }}</a></td>
                                        <td>{{ $employee->emp_id }}</td>
                                        <td>{{ $employee->emp_designation }}</td>
                                        <td>{{ $employee->portfolio_sub }}</td>
                                        <td>{{ $employee->portfolio }}</td>
                                        <td>{{ $employee->sup_id }}</td>
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
