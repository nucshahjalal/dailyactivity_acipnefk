@extends('admin.layouts.master')
@section('styles')
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Calibri:400,300,700">

<style>
    .col-lg-6 {
        padding-top: 0px !important;
        padding-bottom: 0px !important;
    }
    .rating {
        border: none;
        margin-right: 29%;
    }

    .rating>[id^="star"] {
        display: none;
    }

    .rating>label:before {
        margin: 5px;
        font-size: 2.25em;
        font-family: FontAwesome;
        display: inline-block;
        content: "\f005";
    }

    .rating>.half:before {
        content: "\f089";
        position: absolute;
    }

    .rating>label {
        color: #ddd;
        float: right;
    }

    .rating>[id^="star"]:checked~label,
    .rating:not(:checked)>label:hover,
    .rating:not(:checked)>label:hover~label {
        color: #FFD700;
    }

    .reset-option {
        display: none;
    }

    .reset-button {
        background-color: rgb(255, 255, 255);
        text-transform: uppercase;
    }

    .rating-value {
        display: inline-block;
        margin-left: 10px;
        font-size: 1.5em;
        color: #FFD700;
    }
</style>

@endsection
@section('contentBody')
<div class="row text-center">
    <div class="col-lg-12">
        <form action="{{ route('admin.emp_assign_task_store') }}" method="post">
            @csrf
            <div class="card">
                <div class="card-title">
                    @php
                        $employee = \App\Models\User::where('id', $empId)->first();
                    @endphp
                    <input type="hidden" name="empId" value="{{ $empId }}">
                    <h3 class="text-primary font-weight-bold">Assign {{ $employee->emp_name }} ({{ $employee->emp_id }}) Task</h3>
                </div>

                <div class="card-body">
                    <div class="basic-elements">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Details <span class="text-danger">*</span></label>
                                    <textarea name="details" cols="30" rows="2" class="form-control" required>{{ old('details') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-dark">Expected Start Time <span class="text-danger">*</span></label>
                                    <select class="form-control startTime" id="startTime" name="startTime" required>
                                        <option value="1st Half">1st Half</option>
                                        <option value="2nd Half">2nd Half</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-dark">Type of Work <span class="text-danger">*</span></label>
                                    <select class="form-control workType" id="workType" name="workType" required>
                                        <option value="Related to action plan">Related to action plan</option>
                                        <option value="Assigned by other">Assigned by other</option>
                                        <option value="others">others</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="remarks" cols="30" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-lg btn-success">Submit</button>
                                <a href="{{ route('admin.emp_assign_task') }}" class="btn btn-lg btn-danger ml-1" onclick="return confirm('Are you sure to cancel !!')">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Handle click events for Action Plan Wise Task rating
        $("input[name='rating']").click(function () {
            var sim = $("input[name='rating']:checked").val();
            sim = sim * 2;
            $(".myratings").text(sim).show();
        });

        // Handle click events for Other Task rating
        $("input[name='other_rating']").click(function () {
            var sim2 = $("input[name='other_rating']:checked").val();
            sim2 = sim2 * 2;
            $(".other_ratings").text(sim2).show();
        });
    });
</script>
@endsection
