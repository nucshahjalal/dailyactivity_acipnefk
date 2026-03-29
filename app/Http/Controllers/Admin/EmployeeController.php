<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeController extends Controller
{
    public function index(Request $request){
        $data['title'] = "Employee List";
        $userId = session('userId');

        $auth = auth()->user();

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
                    ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->orderByDesc('id')->get();

        $data['employees'] = $employees;
        return view('admin.employee.index',$data);
    }
    public function supervision(Request $request){
        $data['title'] = "Employee List of Supervision";
        $userId = session('userId');

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        $employees = User::where('sup_id', $userId)->get();

        $data['employees'] = $employees;
        return view('admin.employee.index',$data);
    }
    public function active(Request $request,$startDate,$endDate){
        $data['title'] = "Employee List of Active";
        $userId = session('userId');

        $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

        $employeesList = $employees->pluck('id');

        $tasks = Task::select('userid')->whereIn('userid', $employeesList)->whereBetween('date', [$startDate, $endDate])
                    ->groupBy('userid')->get();
     //   dd($tasks);

        $data['tasks'] = $tasks;
        return view('admin.employee.active',$data);
    }
    public function inactive(Request $request,$startDate,$endDate){
        $data['title'] = "Employee List of Inactive";
        $userId = session('userId');

        $employees = User::where('sup_id', $userId)->orWhere('level_2', $userId)->orWhere('level_3', $userId)
            ->orWhere('level_4', $userId)->orWhere('level_5', $userId)->get();

        $employeesList = $employees->pluck('id');

        $employees = User::whereNotIn('id', function ($query) use ($startDate, $endDate) {

                    $query->select('userid')
                        ->from('task')
                        ->whereBetween('date', [$startDate, $endDate]);
                })
                ->whereIn('id', $employeesList)
                ->get();
        $data['employees'] = $employees;
        return view('admin.employee.index',$data);
    }
}
