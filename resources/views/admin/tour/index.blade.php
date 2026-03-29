@extends('admin.layouts.master')
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

                    @if($searchTitle == 'On My Supervision')
                    <form action="{{ route('admin.tour_on_my_supervision') }}" method="get" class="form-inline" mt-1>
                    @else
                    <form action="{{ route('admin.tour_all_employee') }}" method="get" class="form-inline" mt-1>
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
                        <div class="form-group mb-2 d-flex align-items-center">
                            <span style="white-space: nowrap;">Portfolio: </span>&nbsp;
                            <select name="n_portfolio" class="form-control w-75"> <!-- w-75 যোগ করা হয়েছে -->
                                <option value="">Select</option>
                                @foreach ($portfolios as $portfolio)
                                    <option value="{{ $portfolio }}" {{ request('n_portfolio') == $portfolio ? 'selected' : '' }}>
                                        {{ $portfolio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <span>Start Date: </span>&nbsp;<input type="date" name="start_date" value="{{ $start_date }}" class="form-control" required>
                        </div>
                        <div class="form-group mx-sm-2 mb-2">
                            <span>End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-rounded btn-primary mb-2"><i class="fa fa-search"></i></button>
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
                                    <th>Status</th>
                                    <th>Last Update</th>
                                    <th>Portfolio</th>
                                    <th>selected Territory</th>
                                    
                                    <th>Visited Territory</th>
                                    <th>Plan Wise Visited</th>
                                    
                                </tr>

                            </thead>
                            <tbody>
                                @foreach($tasks as $key=>$task)
                                    @php
                                        $employee = \App\Models\User::where('id',$task->userid)->first();
                                       // $lastTask = \App\Models\Task::where('id',$task->id)->first();
                                    @endphp
                                    @if(isset($employee))
                                        @php
                                            if($task->done == '0' && $task->pending == '0'){
                                                $status = 'Pending';
                                                $lastUpdate = $task->date.' '.$task->time;
                                            }
                                            elseif($task->done == '0' && $task->pending == '1'){
                                                $status = 'Processing';
                                                $lastUpdate =  $task->pending_date. ' ' .$task->pending_time ;

                                            }elseif($task->done == '1'){
                                                $status = 'Done';
                                                $lastUpdate = $task->done_date. ' ' .$task->done_time ;

                                            }else{
                                                $status = '';
                                                $lastUpdate = ' ' ;
                                            }
                                        @endphp
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $task->user->emp_name }}</td>
                                        <td>{{ $task->user->emp_id }}</td>
                                        <td>{{ $task->user->emp_designation }}</td>

                                        @if($task->done == '0' && $task->pending == '0')
                                            <td><span class="text-danger">Pending</span></td>
                                        @elseif($task->done == '0' && $task->pending == '1')
                                            <td><span class="text-warning">Processing</span></td>
                                        @elseif($task->done == '1')
                                            <td><span class="text-success">Done</span></td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td>{{ $lastUpdate }}</td>
                                         <td>{{ $task->user->n_portfolio }}</td>
                                        <td>{{ $task->seleted_territory }}</td>
                                        <td>{{ $task->visited_territory }}</td>
                                        
                                        @if($task->seleted_territory == $task->visited_territory && $task->seleted_territory != 'none' && $task->visited_territory!='none')
                                            <td><span style="font-size:24px;color:green">✓</span></td>
                                        @else
                                            <td><span style="font-size:24px;color:red">✗</span></td>
                                        @endif

                                    </tr>
                                    @endif
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
