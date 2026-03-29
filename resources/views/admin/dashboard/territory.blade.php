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
                                </tr>

                            </thead>
                            <tbody>
                                @foreach($territorys as $key=>$territory)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $territory->name }}</td>
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
