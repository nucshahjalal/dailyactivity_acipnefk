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
                    <form action="{{ route('admin.agenda_on_my_supervision') }}" method="get" class="form-inline mt-1">
                    @else
                    <form action="{{ route('admin.agenda_all_employee') }}" method="get" class="form-inline mt-1">
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
                                    {{-- <th>Date</th> --}}
                                    <th>Agenda Details</th>
                                </tr>

                            </thead>
                            <tbody>
                                @foreach($agendas as $key=>$agenda)
                                    @php
                                        $employee = \App\Models\User::where('id',$agenda->userid)->first();
                                      //  $lastagenda = \App\Models\Agenda::where('id',$agenda->id)->first();
                                        $agendaDetails =  \App\Models\Agenda::where('userid',$agenda->userid)
                                        ->whereBetween('date', [$anStartDate, $anEndDate])->get();
                                    @endphp
                                    @if(isset($employee))
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $employee->emp_name }}</td>
                                        <td>{{ $employee->emp_id }}</td>
                                        <td>{{ $employee->emp_designation }}</td>
                                        {{-- <td>{{ $lastagenda->date }}</td> --}}
                                        <td style="width: 30%;">
                                            @foreach($agendaDetails as $agendaDetail)
                                                {{ $agendaDetail->agenda }}<br/>
                                            @endforeach
                                        </td>
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
