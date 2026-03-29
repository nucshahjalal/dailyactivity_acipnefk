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
