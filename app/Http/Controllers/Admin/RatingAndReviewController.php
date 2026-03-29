<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Review;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RatingAndReviewController extends Controller
{
    public function my_supervision(Request $request){

        $data['title'] = "On My Supervision";
        $userId = session('userId');

        date_default_timezone_set("Asia/Dhaka");

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        if(isset($request->start_date))
        {
            $employees = User::where('sup_id', $userId)->get();
            $data['mydate'] = $request->start_date;
        }else{
            $employees = User::where('sup_id', $userId)->get();
            $data['mydate'] = date("Y-m-d");
        }

        $data['employees'] = $employees;
        $data['searchTitle'] = 'On My Supervision';
        return view('admin.rating.index',$data);
    }
    public function all_employee(Request $request){
        $data['title'] = "All Employee's Overall Rating";
        $userId = session('userId');

        $auth = auth()->user();

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        if(isset($request->start_date) && isset($request->end_date))
        {
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();
            $data['sDate'] = $request->start_date;
            $data['eDate'] = $request->end_date;

        }else{

            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();
            $data['sDate'] = $formattedStartDate;
            $data['eDate'] = $formattedEndDate;
        }

        $data['mydate'] = date("Y-m-d");
        $data['employees'] = $employees;
        $data['searchTitle'] = "All Employee's Overall Rating";
        return view('admin.rating.overall_rating',$data);
    }

    public function in_emp_rating(Request $request,$employee_id,$sDate,$eDate){

        $data['title'] = "Individual Employee's " . $sDate . " to " . $eDate . " Rating";
        $userId = session('userId');

        $auth = auth()->user();
        $reviews = Review::where('userid', $employee_id)->whereBetween('date', [$sDate, $eDate])->get();

        $data['reviews'] = $reviews;
        $data['sDate'] = $sDate;
        $data['eDate'] = $eDate;
        $data['empId'] = $employee_id;

        $data['searchTitle'] = "Individual Employee's " . $sDate . " to " . $eDate . " Rating";
        return view('admin.rating.in_emp_rating',$data);
    }

    public function add($employee_id,$mydate){
        $data['title'] = "Add Rating and Reviews";
        $userId = session('userId');
        $data['empId'] = $employee_id;

        date_default_timezone_set("Asia/Dhaka");
        $data['mydate'] = $mydate;

        return view('admin.rating.add',$data);
    }
    public function store(Request $request){
       // dd($request->all());
        date_default_timezone_set("Asia/Dhaka");
        $mydate = $request->myDate;
        $myMonth = date("m");
        $myYear = date("Y");

        $mytime = date("h:i:sa");

        $data['title'] = "Store Rating and Reviews";
        $userId = session('userId');
        //$review = Review::where(['userid'=>$request->empId,'month'=>$request->month,'year'=>$request->year])->first();

        $review = Review::where(['userid'=>$request->empId,'date'=>$mydate])->first();
        if(isset($review)){
            $notification = array(
                'message' => 'Rating and Reviews Already Stored',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }else{
            $new_review = new Review();
            $new_review->userid = $request->empId;
            $new_review->sup_id = $userId;
            $new_review->date = $mydate;
            $new_review->time = $mytime;
            if(isset($request->rating)){
                $new_review->rta_rating = $request->rating;
            }else{
                $new_review->rta_rating = 0;
            }
            if(isset($request->other_rating)){
                $new_review->other_rating = $request->other_rating;
            }else{
                $new_review->other_rating = 0;
            }

            $new_review->rta_review = $request->rta_review;
            $new_review->other_review = $request->other_review;
            // $new_review->year = $request->year;
            // $new_review->month = $request->month;
            $new_review->year = $myYear;
            $new_review->month = $myMonth;
            $new_review->save();

            $notification = array(
                'message' => 'Rating and Reviews Stored Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('admin.rating_review_on_my_supervision')->with($notification);
        }
    }


    public function todaysOverview(Request $request,$empId,$myDate){

        $data['title'] = $myDate ."'s Overview of Individual Employee";
        $userId = session('userId');
        date_default_timezone_set("Asia/Dhaka");
        $mydate = $myDate;

        $auth = auth()->user();

        $total_tasks = Task::where('userid', $empId)->where('date',$mydate)->count();
        $total_pending_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'0'])->where('date',$mydate)->count();
        $total_processing_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'1'])->where('date',$mydate)->count();
        $total_done_tasks = Task::where('userid', $empId)->where('done','1')->where('date',$mydate)->count();
        $total_related_action = Task::where('userid', $empId)->where('worktype','Related to action plan')->where('date',$mydate)->count();
        $total_assigned_by_others = Task::where('userid', $empId)->where('worktype','Assigned by other')->where('date',$mydate)->count();
        $total_agenda_wise_task = Task::where('userid', $empId)->where('is_agenda','1')->where('date',$mydate)->count();
        $total_agenda_submitted =  Agenda::where('userid', $empId)->where('date',$mydate)
                                ->distinct('userid')->count('userid');

        $territory = Territory::all();
        $territoryList = $territory->pluck('name');

        $total_territory = Territory::count();
        $total_planned_territory = Task::where('userid', $empId)->where('is_agenda','2')->where('date',$mydate)->count();
        $total_visited_territory = Task::where('userid', $empId)->select('visited_territory')
                                ->distinct()->where('visited_territory', '!=', 'none')->where('date',$mydate)
                            ->count('visited_territory');

        $total_not_visited_territory = $total_territory - $total_visited_territory;

        $plan_wise_visits = Task::where('userid', $empId)
        ->where('is_agenda', '2')
        ->where('visited_territory', '<>', 'none')
        ->where('seleted_territory', '<>', 'none')
        ->whereColumn('visited_territory', 'seleted_territory')
        ->where('date', $mydate)
        // ->groupBy('visited_territory', 'seleted_territory')
        // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
        ->get();

        $total_plan_wise_visited = 0;
        foreach($plan_wise_visits as $plan_wise_visit){
            $total_plan_wise_visited ++;
        }

        $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;

        $data['anStartDate'] = $mydate;
        $data['anEndDate'] = $mydate;



        $data['total_tasks'] = $total_tasks;
        $data['total_pending_tasks'] = $total_pending_tasks;
        $data['total_processing_tasks'] = $total_processing_tasks;
        $data['total_done_tasks'] = $total_done_tasks;
        $data['total_related_action'] = $total_related_action;
        $data['total_assigned_by_others'] = $total_assigned_by_others;
        $data['total_agenda_wise_task'] = $total_agenda_wise_task;
        $data['total_agenda_submitted'] = $total_agenda_submitted;

        $data['total_territory'] = $total_territory;
        $data['total_planned_territory'] = $total_planned_territory;
        $data['total_visited_territory'] = $total_visited_territory;
        $data['total_not_visited_territory'] = $total_not_visited_territory;
        $data['total_plan_wise_visited'] = $total_plan_wise_visited;
        $data['total_not_visited_yet'] = $total_not_visited_yet;
        $data['total_active_employees'] = 0;
        $data['total_inactive_employees'] = 0;

        $employees = User::where('id', $empId)->get();

        $data['employees'] = $employees;

        $data['searchTitle'] = 'Dashboard of Employee';
        $data['empId'] = $empId;

        return view('admin.rating.todaysOverview',$data);

    }


    public function assign_task(Request $request){
        $data['title'] = "Assign Task";
        $userId = session('userId');

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        $employees = User::where('sup_id', $userId)->get();

        date_default_timezone_set("Asia/Dhaka");
        $data['mydate'] = date("Y-m-d");

        $data['employees'] = $employees;
        return view('admin.rating.assign_task',$data);
    }

    public function create_assign_task($employee_id){
        $data['title'] = "Create Task";
        $userId = session('userId');
        $data['empId'] = $employee_id;
        return view('admin.rating.create_assign_task',$data);
    }
    public function assign_task_store(Request $request){
        $data['title'] = "Store Task";
        $userId = session('userId');

        date_default_timezone_set("Asia/Dhaka");
        $mydate = date("Y-m-d");
        $mytime = date("h:i:sA");

        $task = new Task();
        $task->details = $request->details;
        $task->remarks = "" . $request->remarks;
        $task->worktype = $request->workType;
        $task->date = $mydate;
        $task->time = $mytime;
        $task->status = 0;
        $task->starttime = $request->startTime;
        $task->userid = $request->empId;
        $task->done = 0;
        $task->done_date = '""';
        $task->done_time = '""';
        $task->pending = 1;
        $task->pending_date = $mydate;
        $task->pending_time = $mytime;
        $task->is_agenda = -1;
        $task->selected_agenda = -1;
        $task->seleted_territory = "none";
        $task->visited_territory = "none";
        $task->save();

        $notification = array(
            'message' => 'Task Assigned Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('admin.emp_assign_task')->with($notification);
    }
}
