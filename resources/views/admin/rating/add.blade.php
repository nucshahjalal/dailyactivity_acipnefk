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
        <form action="{{ route('admin.rating_review_store') }}" method="post">
            @csrf
            <div class="card">
                <div class="card-title">
                    @php
                        $employee = \App\Models\User::where('id', $empId)->first();
                    @endphp
                    <input type="hidden" name="empId" value="{{ $empId }}">
                    <h3 class="text-primary font-weight-bold">Submit {{ $employee->emp_name }} ({{ $employee->emp_id }}) Overview</h3>
                </div>

                <div class="card-body">
                    <div class="basic-elements">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <h4 class="mt-1">Date <span class="text-danger">*</span></h4>
                                <div class="form-group">
                                    <input type="date" name="myDate" value="{{ $mydate }}" class="form-control" required
                                           max="{{ date('Y-m-d') }}" readonly> <!-- Set the max attribute to the current date -->
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <h4 class="mt-1">Related to Action Plan Wise Task <span class="myratings rating-value"></span></h4>
                                <fieldset class="rating" required>
                                    <input type="radio" id="star5" name="rating" value="5" /><label class="full" for="star5"
                                        title="Awesome - 5 stars"></label>
                                    <input type="radio" id="star4half" name="rating" value="4.5" /><label class="half"
                                        for="star4half" title="Pretty good - 4.5 stars"></label>
                                    <input type="radio" id="star4" name="rating" value="4" /><label class="full" for="star4"
                                        title="Pretty good - 4 stars"></label>
                                    <input type="radio" id="star3half" name="rating" value="3.5" /><label class="half"
                                        for="star3half" title="Meh - 3.5 stars"></label>
                                    <input type="radio" id="star3" name="rating" value="3" /><label class="full" for="star3"
                                        title="Meh - 3 stars"></label>
                                    <input type="radio" id="star2half" name="rating" value="2.5" /><label class="half"
                                        for="star2half" title="Kinda bad - 2.5 stars"></label>
                                    <input type="radio" id="star2" name="rating" value="2" /><label class="full" for="star2"
                                        title="Kinda bad - 2 stars"></label>
                                    <input type="radio" id="star1half" name="rating" value="1.5" /><label class="half"
                                        for="star1half" title="Meh - 1.5 stars"></label>
                                    <input type="radio" id="star1" name="rating" value="1" /><label class="full" for="star1"
                                        title="Sucks big time - 1 star"></label>
                                    <input type="radio" id="starhalf" name="rating" value="0.5" /><label class="half"
                                        for="starhalf" title="Sucks big time - 0.5 stars"></label>
                                    <input type="radio" class="reset-option" name="rating" value="reset" />
                                </fieldset>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <textarea class="form-control" name="rta_review" cols="30" rows="4" placeholder="Enter Your Review">{{ old('rta_review') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <h4 class="mt-1">Others Task <span class="other_ratings rating-value"></span></h4>
                                <fieldset class="rating" required>
                                    <input type="radio" id="star52" name="other_rating" value="5" /><label class="full" for="star52"
                                        title="Awesome - 5 stars"></label>
                                    <input type="radio" id="star4half2" name="other_rating" value="4.5" /><label class="half"
                                        for="star4half2" title="Pretty good - 4.5 stars"></label>
                                    <input type="radio" id="star42" name="other_rating" value="4" /><label class="full" for="star42"
                                        title="Pretty good - 4 stars"></label>
                                    <input type="radio" id="star3half2" name="other_rating" value="3.5" /><label class="half"
                                        for="star3half2" title="Meh - 3.5 stars"></label>
                                    <input type="radio" id="star32" name="other_rating" value="3" /><label class="full" for="star32"
                                        title="Meh - 3 stars"></label>
                                    <input type="radio" id="star2half2" name="other_rating" value="2.5" /><label class="half"
                                        for="star2half2" title="Kinda bad - 2.5 stars"></label>
                                    <input type="radio" id="star22" name="other_rating" value="2" /><label class="full" for="star22"
                                        title="Kinda bad - 2 stars"></label>
                                    <input type="radio" id="star1half2" name="other_rating" value="1.5" /><label class="half"
                                        for="star1half2" title="Meh - 1.5 stars"></label>
                                    <input type="radio" id="star12" name="other_rating" value="1" /><label class="full" for="star12"
                                        title="Sucks big time - 1 star"></label>
                                    <input type="radio" id="starhalf2" name="other_rating" value="0.5" /><label class="half"
                                        for="starhalf2" title="Sucks big time - 0.5 stars"></label>
                                    <input type="radio" class="reset-option" name="other_rating" value="reset" />
                                </fieldset>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <textarea class="form-control" name="other_review" cols="30" rows="4" placeholder="Enter Your Review">{{ old('other_review') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-lg btn-success">Submit</button>
                                <a href="{{ route('admin.rating_review_all_employee') }}" class="btn btn-lg btn-danger ml-1" onclick="return confirm('Are you sure to cancel !!')">Cancel</a>
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
