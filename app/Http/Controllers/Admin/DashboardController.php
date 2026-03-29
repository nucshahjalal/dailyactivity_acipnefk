<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request){

        $data['title'] = "Dashboard";
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

            $employeesList = $employees->pluck('id');

            $total_tasks = Task::whereIn('userid', $employeesList)->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_pending_tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_processing_tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_done_tasks = Task::whereIn('userid', $employeesList)->where('done','1')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_related_action = Task::whereIn('userid', $employeesList)->where('worktype','Related to action plan')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_assigned_by_others = Task::whereIn('userid', $employeesList)->where('worktype','Assigned by other')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_agenda_wise_task = Task::whereIn('userid', $employeesList)->where('is_agenda','1')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_agenda_submitted =  Agenda::whereIn('userid', $employeesList)->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->distinct('userid')->count('userid');

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_planned_territory = Task::whereIn('userid', $employeesList)->where('is_agenda','2')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_visited_territory = Task::whereIn('userid', $employeesList)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')->whereBetween('date',[$request->start_date,$request->end_date])
                                ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;

            $plan_wise_visits = Task::whereIn('userid', $employeesList)
            ->where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$request->start_date,$request->end_date])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;


            $total_employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->count();

            $total_supervision_employees = User::where('sup_id', $userId)->count();

            $total_active_employees = Task::whereIn('userid', $employeesList)->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->distinct('userid')->count('userid');
            $total_inactive_employees = User::whereNotIn('id', function ($query) use ($request) {

                                        $query->select('userid')
                                            ->from('task')
                                            ->whereBetween('date', [$request->start_date, $request->end_date]);
                                    })
                                    ->whereIn('id', $employeesList)
                                    ->count();

            $data['anStartDate'] = $request->start_date;
            $data['anEndDate'] = $request->end_date;

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $total_tasks = Task::whereIn('userid', $employeesList)->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_pending_tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_processing_tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_done_tasks = Task::whereIn('userid', $employeesList)->where('done','1')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_related_action = Task::whereIn('userid', $employeesList)->where('worktype','Related to action plan')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_assigned_by_others = Task::whereIn('userid', $employeesList)->where('worktype','Assigned by other')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_agenda_wise_task = Task::whereIn('userid', $employeesList)->where('is_agenda','1')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_agenda_submitted =  Agenda::whereIn('userid', $employeesList)->whereBetween('date', [$formattedStartDate, $formattedEndDate])
                                    ->distinct('userid')->count('userid');

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_planned_territory = Task::whereIn('userid', $employeesList)->where('is_agenda','2')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_visited_territory = Task::whereIn('userid', $employeesList)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')->whereBetween('date',[$formattedStartDate,$formattedEndDate])
                                ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;


           $plan_wise_visits = Task::whereIn('userid', $employeesList)
            ->where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;


            $total_employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->count();

            $total_supervision_employees = User::where('sup_id', $userId)->count();

            $total_active_employees = Task::whereIn('userid', $employeesList)->whereBetween('date', [$formattedStartDate, $formattedEndDate])
                                    ->distinct('userid')->count('userid');

            $total_inactive_employees = User::whereNotIn('id', function ($query) use ($formattedStartDate, $formattedEndDate) {
                                        $query->select('userid')
                                            ->from('task')
                                            ->whereBetween('date', [$formattedStartDate, $formattedEndDate]);
                                    })
                                    ->whereIn('id', $employeesList)
                                    ->count();

            $data['anStartDate'] = $formattedStartDate;
            $data['anEndDate'] = $formattedEndDate;

            $dashboard_employees = User::where(function ($query) use ($userId) {
                $query->where('sup_id', $userId)
                      ->orWhere('level_2', $userId)
                      ->orWhere('level_3', $userId)
                      ->orWhere('level_4', $userId)
                      ->orWhere('level_5', $userId);
            })
            ->withCount([
                'tasks as no_of_days' => function ($query) use ($formattedStartDate, $formattedEndDate) {
                    $query->where('is_agenda', '2')
                          ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
                          ->selectRaw('COUNT(DISTINCT date)');
                }
            ])
            ->orderByDesc('no_of_days')
            ->get();
        }


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

        $data['total_employees'] = $total_employees;
        $data['total_supervision_employees'] = $total_supervision_employees;
        $data['total_active_employees'] = $total_active_employees;
        $data['total_inactive_employees'] = $total_inactive_employees;

        $data['employees'] = $employees;

        // $active_tasks = Task::select('userid', DB::raw('COALESCE(count(task.id), 0) as no_of_days'))
        // ->whereIn('userid', $employeesList)->whereBetween('date', [$formattedStartDate, $formattedEndDate])
        //                     ->groupBy('userid')->orderByDesc('no_of_days')->get();

        $active_tasks = DB::table('users')
        ->leftJoin('task', 'users.id', '=', 'task.userid')
        ->select('users.id', DB::raw('COALESCE(count(task.id), 0) as no_of_days'))
        ->whereBetween('task.date', [$formattedStartDate, $formattedEndDate])
        ->orWhereNull('task.userid') // Include employees with no task
        ->whereIn('users.id', $employeesList)
        ->groupBy('users.id')
        ->orderByDesc('no_of_days')
        ->get();


        $data['active_tasks'] = $active_tasks;

        $data['searchTitle'] = 'Dashboard of All';
        $data['empId'] = 0;

        return view('admin.dashboard',$data);
    }

    public function employeeById(Request $request,$empId){

        $data['title'] = "Dashboard of Individual Employee";
        $userId = session('userId');

        $auth = auth()->user();

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        if(isset($request->start_date) && isset($request->end_date))
        {
            $total_tasks = Task::where('userid', $empId)->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_pending_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_processing_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_done_tasks = Task::where('userid', $empId)->where('done','1')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_related_action = Task::where('userid', $empId)->where('worktype','Related to action plan')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_assigned_by_others = Task::where('userid', $empId)->where('worktype','Assigned by other')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_agenda_wise_task = Task::where('userid', $empId)->where('is_agenda','1')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_agenda_submitted =  Agenda::where('userid', $empId)->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->distinct('userid')->count('userid');

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_planned_territory = Task::where('userid', $empId)->where('is_agenda','2')->whereBetween('date',[$request->start_date,$request->end_date])->count();
            $total_visited_territory = Task::where('userid', $empId)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')->whereBetween('date',[$request->start_date,$request->end_date])
                                ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;

            $plan_wise_visits = Task::where('userid', $empId)
            ->where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$request->start_date,$request->end_date])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;

            $data['anStartDate'] = $request->start_date;
            $data['anEndDate'] = $request->end_date;

        }else{

            $total_tasks = Task::where('userid', $empId)->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_pending_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_processing_tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_done_tasks = Task::where('userid', $empId)->where('done','1')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_related_action = Task::where('userid', $empId)->where('worktype','Related to action plan')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_assigned_by_others = Task::where('userid', $empId)->where('worktype','Assigned by other')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_agenda_wise_task = Task::where('userid', $empId)->where('is_agenda','1')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_agenda_submitted =  Agenda::where('userid', $empId)->whereBetween('date', [$formattedStartDate, $formattedEndDate])
                                    ->distinct('userid')->count('userid');

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_planned_territory = Task::where('userid', $empId)->where('is_agenda','2')->whereBetween('date',[$formattedStartDate,$formattedEndDate])->count();
            $total_visited_territory = Task::where('userid', $empId)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')->whereBetween('date',[$formattedStartDate,$formattedEndDate])
                                ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;

            $plan_wise_visits = Task::where('userid', $empId)
            ->where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$formattedStartDate,$formattedEndDate])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;

            $data['anStartDate'] = $formattedStartDate;
            $data['anEndDate'] = $formattedEndDate;
        }


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

        return view('admin.dashboard2',$data);
    }
    public function tasks(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Task";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function pending(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Pending Task";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'0'])->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function processing(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Processing Task";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where(['done'=>'0','pending'=>'1'])->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function done(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Done Task";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where('done','1')->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where('done','1')->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function related_action(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Related Action";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where('worktype','Related to action plan')->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where('worktype','Related to action plan')->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function assigned_by_others(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Assigned by Others";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where('worktype','Assigned by other')->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where('worktype','Assigned by other')->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function agenda_wise_task(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Agenda wise Task ";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where('is_agenda','1')->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where('is_agenda','1')->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }
    public function agenda_submitted(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Agenda Submitted";
        $userId = session('userId');
        if(!empty($empId)){
            $agendas = Agenda::where('userid', $empId)
            ->whereBetween('date', [$startDate , $endDate])
            ->groupBy('userid')
            ->selectRaw('userid, MAX(id) as id')
            ->orderByDesc('id')
            ->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $agendas = Agenda::whereIn('userid', $employeesList)
            ->whereBetween('date', [$startDate , $endDate])
            ->groupBy('userid')
            ->selectRaw('userid, MAX(id) as id')
            ->orderByDesc('id')
            ->get();
        }
        $data['anStartDate'] = $startDate;
        $data['anEndDate'] = $endDate;

        $data['agendas'] = $agendas;

        return view('admin.dashboard.agenda',$data);
    }

    public function territory(Request $request){
        $data['title'] = "List of Territory";
        $userId = session('userId');
        $territorys = Territory::all();

        $data['territorys'] = $territorys;

        return view('admin.dashboard.territory',$data);
    }

    public function planned_territory(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Planned Territory";
        $userId = session('userId');
        if(!empty($empId)){
            $tasks = Task::where('userid', $empId)->where('is_agenda','2')->whereBetween('date',[$startDate,$endDate])->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $tasks = Task::whereIn('userid', $employeesList)->where('is_agenda','2')->whereBetween('date',[$startDate,$endDate])->get();
        }

        $data['tasks'] = $tasks;

        return view('admin.dashboard.task',$data);
    }

    public function visited_territory(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Visited Territory";
        $userId = session('userId');
        if(!empty($empId)){
            $taskByIds = Task::where('userid', $empId)->where('visited_territory', '!=', 'none')
            ->whereBetween('date', [$startDate, $endDate])->groupBy('visited_territory')
            ->selectRaw('visited_territory, MAX(id) as id')
            ->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $taskByIds =  Task::whereIn('userid', $employeesList)->where('visited_territory', '!=', 'none')
            ->whereBetween('date', [$startDate, $endDate])->groupBy('visited_territory')
            ->selectRaw('visited_territory, MAX(id) as id')
            ->get();
        }

        $data['taskByIds'] = $taskByIds;

        return view('admin.dashboard.taskById',$data);
    }
    public function not_visited_territory(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Not Visited Territory";
        $userId = session('userId');
        if(!empty($empId)){
            $territorys = Territory::whereNotExists(function ($query) use ($empId, $startDate, $endDate) {

                $query->select(DB::raw(1))
                    ->from('task')
                    ->whereRaw('territory.name = task.visited_territory')
                    ->where('task.userid', $empId)
                    ->whereBetween('task.date', [$startDate, $endDate]);

            })->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $territorys = Territory::whereNotExists(function ($query) use ($employeesList, $startDate, $endDate) {

                $query->select(DB::raw(1))
                    ->from('task')
                    ->whereRaw('territory.name = task.visited_territory')
                    ->whereIn('task.userid', $employeesList)
                    ->whereBetween('task.date', [$startDate, $endDate]);

            })->get();
        }


        $data['territorys'] = $territorys;

        return view('admin.dashboard.territory',$data);
    }

    public function plan_wise_visited(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Plan wise Visited";
        $userId = session('userId');
        if(!empty($empId)){
            $taskByIds = Task::where('userid', $empId)->where('is_agenda', '2')->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$startDate,$endDate])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $taskByIds = Task::whereIn('userid', $employeesList)->where('is_agenda', '2')->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$startDate,$endDate])
            // ->groupBy('visited_territory', 'seleted_territory')
            // ->selectRaw('visited_territory, seleted_territory, MAX(id) as id')
            ->get();
        }

        $data['taskByIds'] = $taskByIds;

        return view('admin.dashboard.taskById',$data);
    }

    public function not_visited_yet(Request $request,$startDate,$endDate,$empId=false){
        $data['title'] = "List of Not Visited Yet";
        $userId = session('userId');
        if(!empty($empId)){

            $planned_territory = Task::where('userid', $empId)->where('is_agenda','2')->whereBetween('date',[$startDate, $endDate]);

            $planned_wise_visited = Task::where('userid', $empId)->where('is_agenda', '2')->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$startDate,$endDate])->get();

            $planned_wise_visitedList = $planned_wise_visited->pluck('id');

            $tasks = $planned_territory->whereNotIn('id', $planned_wise_visitedList)->get();

        }else{
            $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

            $employeesList = $employees->pluck('id');

            $planned_territory = Task::whereIn('userid', $employeesList)->where('is_agenda','2')->whereBetween('date',[$startDate, $endDate]);

            $planned_wise_visited = Task::whereIn('userid', $employeesList)->where('is_agenda', '2')->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$startDate,$endDate])->get();

            $planned_wise_visitedList = $planned_wise_visited->pluck('id');

            $tasks = $planned_territory->whereNotIn('id', $planned_wise_visitedList)->get();
        }

        $data['tasks'] = $tasks;
        return view('admin.dashboard.task',$data);
    }

    public function open_link(Request $request){

        $data['title'] = "Open Link Dashboard";

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        if(isset($request->start_date) && isset($request->end_date))
        {
            $employees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')->get();
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id);
            $totalEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')->count();

            $existingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $employeesList)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get()
            ->unique('userid')
            ->count();
            $notExistingUsers = $totalEmployee - $existingUsers;

            $recoveryEmployees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Motors Agri Machineries%')
            ->get();
            $recoveryEmployeesList = $recoveryEmployees->pluck('id')->map(fn($id) => (int) $id);
            $totalRecoveryEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Motors Agri Machineries%')
            ->count();

            $recoveryExistingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $recoveryEmployeesList)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get()
            ->unique('userid')
            ->count();

            $notRecoveryExistingUsers = $totalRecoveryEmployee - $recoveryExistingUsers;

            $serviceEmployees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Service & Spare parts%')
            ->get();
            $serviceEmployeesList = $serviceEmployees->pluck('id')->map(fn($id) => (int) $id);
            $totalServiceEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Service & Spare parts%')
            ->count();

            $serviceExistingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $serviceEmployeesList)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get()
            ->unique('userid')
            ->count();

            $notServiceExistingUsers = $totalServiceEmployee - $serviceExistingUsers;

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_visited_territory = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')
                                    ->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;

            $total_planned_territory = Task::where('is_agenda','2')->whereIn('userid', $employeesList)->where('seleted_territory', '!=', 'none')
            ->whereBetween('date', [$request->start_date, $request->end_date])->count();

            $plan_wise_visits = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;

            $territoryVisitPlans = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)->whereIn('seleted_territory', $territoryList)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();

            $anStartDate = $request->start_date;
            $anEndDate = $request->end_date;

        }else{

            $employees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')->get();
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id);
            $totalEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')->count();

            $existingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $employeesList)
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->get()
            ->unique('userid')
            ->count();
            $notExistingUsers = $totalEmployee - $existingUsers;

            $recoveryEmployees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Motors Agri Machineries%')
            ->get();
            $recoveryEmployeesList = $recoveryEmployees->pluck('id')->map(fn($id) => (int) $id);
            $totalRecoveryEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Motors Agri Machineries%')
            ->count();

            $recoveryExistingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $recoveryEmployeesList)
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->get()
            ->unique('userid')
            ->count();

            $notRecoveryExistingUsers = $totalRecoveryEmployee - $recoveryExistingUsers;

            $serviceEmployees = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Service & Spare parts%')
            ->get();
            $serviceEmployeesList = $serviceEmployees->pluck('id')->map(fn($id) => (int) $id);
            $totalServiceEmployee = User::whereRaw('emp_id REGEXP "^[0-9]+$"')
            ->where('portfolio', 'LIKE', '%Service & Spare parts%')
            ->count();

            $serviceExistingUsers = Task::where('is_agenda', '2')
            ->whereIn('userid', $serviceEmployeesList)
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->get()
            ->unique('userid')
            ->count();

            $notServiceExistingUsers = $totalServiceEmployee - $serviceExistingUsers;

            $territory = Territory::all();
            $territoryList = $territory->pluck('name');

            $total_territory = Territory::count();
            $total_visited_territory = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)->select('visited_territory')
                                    ->distinct()->where('visited_territory', '!=', 'none')
                                    ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
                                    ->count('visited_territory');

            $total_not_visited_territory = $total_territory - $total_visited_territory;

            $total_planned_territory = Task::where('is_agenda','2')->whereIn('userid', $employeesList)->where('seleted_territory', '!=', 'none')
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])->count();

            $plan_wise_visits = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->get();

            $total_plan_wise_visited = 0;
            foreach($plan_wise_visits as $plan_wise_visit){
                $total_plan_wise_visited ++;
            }

            $total_not_visited_yet = $total_planned_territory - $total_plan_wise_visited;

            $territoryVisitPlans = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)->whereIn('seleted_territory', $territoryList)
            ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->get();

            $anStartDate = $formattedStartDate;
            $anEndDate = $formattedEndDate;
        }

        $employeesWithTourCount = Cache::remember('employees_tour_count_' . $anStartDate . '_' . $anEndDate, 60, function () use ($anStartDate, $anEndDate) {
            return User::where('emp_id', 'REGEXP', '^[0-9]+$') // Use a more straightforward query
                ->withCount([
                    'tasks as no_of_tours' => function ($query) use ($anStartDate, $anEndDate) {
                        $query->selectRaw('COUNT(DISTINCT date)')
                              ->where('is_agenda', '2')
                              ->whereBetween('date', [$anStartDate, $anEndDate]);
                    }
                ])
                ->orderByDesc('no_of_tours')
                ->get();
        });

        // Fetch visit counts grouped by visited_territory and user name
        $upazillaData = Task::where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id') // Join with Users table
            ->select('visited_territory', 'users.emp_name as user_name', DB::raw('COUNT(task.id) as visit_count'))
            ->where('users.emp_id', 'REGEXP', '^[0-9]+$')
            ->groupBy('visited_territory', 'users.emp_name')
            ->get()
            ->groupBy('visited_territory'); // Group by visited_territory for easier access

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');
        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4']; // Adjust property name if needed
            $visitCounts = $upazillaData->get($name) ?? collect();

            // Format visit count per user name
            $userVisitCounts = $visitCounts->mapWithKeys(function ($visit) {
                return [$visit->user_name => $visit->visit_count];
            });

            $feature['properties']['visit_counts'] = $userVisitCounts; // Store visits per user name
        }

        $data['upazilas'] = $upazilas;


        $data['totalEmployee'] = $totalEmployee;
        $data['existingUsers'] = $existingUsers;
        $data['notExistingUsers'] = $notExistingUsers;

        $data['totalRecoveryEmployee'] = $totalRecoveryEmployee;
        $data['recoveryExistingUsers'] = $recoveryExistingUsers;
        $data['notRecoveryExistingUsers'] = $notRecoveryExistingUsers;

        $data['totalServiceEmployee'] = $totalServiceEmployee;
        $data['serviceExistingUsers'] = $serviceExistingUsers;
        $data['notServiceExistingUsers'] = $notServiceExistingUsers;

        $data['total_territory'] = $total_territory;
        $data['total_visited_territory'] = $total_visited_territory;
        $data['total_not_visited_territory'] = $total_not_visited_territory;

        $data['total_planned_territory'] = $total_planned_territory;
        $data['total_plan_wise_visited'] = $total_plan_wise_visited;

        $data['employeesWithTourCount'] = $employeesWithTourCount;
        $data['territoryVisitPlans'] = $territoryVisitPlans;

        return view('admin.open_link_dashboard',$data);
    }

}
