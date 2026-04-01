<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;

class TourController extends Controller
{
    public function my_supervision(Request $request){
        $data['title'] = "On My Supervision";
        $userId = session('userId');

        $auth = auth()->user();

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        $employees = User::where('sup_id', $userId)->get();

        $employeesList = $employees->pluck('id');

        if(isset($request->start_date) && isset($request->end_date))
        {

            // $tasks = Task::whereIn('userid', $employeesList)
            //     ->whereBetween('date', [$request->start_date , $request->end_date])
            //     ->where('is_agenda','2')
            //     ->orderByDesc('id')
            //     ->get();
             $tasks = Task::with('user')
            ->when(!empty($employeesList), function ($query) use ($employeesList) {
                return $query->whereIn('userid', $employeesList);
            })
            ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
                return $query->whereBetween('date', [$request->start_date, $request->end_date]);
            })
            ->where('is_agenda', '2')
            ->when($request->filled('n_portfolio'), function ($query) use ($request) {
                return $query->whereRelation('user', 'n_portfolio', $request->n_portfolio);
            })
            ->orderByDesc('id')
            ->get();


        }else{

            // $tasks = Task::whereIn('userid', $employeesList)
            //     ->whereBetween('date', [$formattedStartDate , $formattedEndDate])
            //     ->where('is_agenda','2')
            //     ->orderByDesc('id')
            //     ->get();
            $tasks = Task::with('user')
            ->when(!empty($employeesList), function ($query) use ($employeesList) {
                return $query->whereIn('userid', $employeesList);
            })
            ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
                return $query->whereBetween('date', [$request->start_date, $request->end_date]);
            })
            ->where('is_agenda', '2')
            ->when($request->filled('n_portfolio'), function ($query) use ($request) {
                return $query->whereRelation('user', 'n_portfolio', $request->n_portfolio);
            })
            ->orderByDesc('id')
            ->get();

        }

        $data['tasks'] = $tasks;
        $data['portfolios'] = User::whereNotNull('n_portfolio')->distinct()->pluck('n_portfolio');
        $data['searchTitle'] = 'On My Supervision';
        return view('admin.tour.index',$data);
    }
    
    public function all_employee(Request $request){
        $data['title'] = "All Employee";
        $userId = session('userId');

        $auth = auth()->user();

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
                    ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

        $employeesList = $employees->pluck('id');

        if(isset($request->start_date) && isset($request->end_date))
        {
            // $tasks = Task::whereIn('userid', $employeesList)
            //     ->whereBetween('date', [$request->start_date , $request->end_date])
            //     ->where('is_agenda','2')
            //     ->orderByDesc('id')
            //     ->get();

           $tasks = Task::with('user')
            ->when(!empty($employeesList), function ($query) use ($employeesList) {
                return $query->whereIn('userid', $employeesList);
            })
            ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
                return $query->whereBetween('date', [$request->start_date, $request->end_date]);
            })
            ->where('is_agenda', '2')
            ->when($request->filled('n_portfolio'), function ($query) use ($request) {
                return $query->whereRelation('user', 'n_portfolio', $request->n_portfolio);
            })
            ->orderByDesc('id')
            ->get();


        }else{

            // $tasks = Task::whereIn('userid', $employeesList)
            //     ->whereBetween('date', [$formattedStartDate , $formattedEndDate])
            //     ->where('is_agenda','2')
            //     ->orderByDesc('id')
            //     ->get();

            $tasks = Task::with('user')
                ->when(!empty($employeesList), function ($query) use ($employeesList) {
                    return $query->whereIn('userid', $employeesList);
                })
                ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
                    return $query->whereBetween('date', [$request->start_date, $request->end_date]);
                })
                ->where('is_agenda', '2')
                ->when($request->filled('n_portfolio'), function ($query) use ($request) {
                    return $query->whereRelation('user', 'n_portfolio', $request->n_portfolio);
                })
            ->orderByDesc('id')
            ->get();



        }

        $data['tasks'] = $tasks;
        $data['portfolios'] = User::whereNotNull('n_portfolio')->distinct()->pluck('n_portfolio');
        $data['searchTitle'] = 'All Employee';
        return view('admin.tour.index',$data);
    }
}
