<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agenda;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgendaController extends Controller
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
      //  dd($employees);

        $employeesList = $employees->pluck('id');

        if(isset($request->start_date) && isset($request->end_date))
        {
            $agendas = Agenda::whereIn('userid', $employeesList)
                ->whereBetween('date', [$request->start_date , $request->end_date])
                ->groupBy('userid')
                ->selectRaw('userid, MAX(id) as id')
                ->orderByDesc('id')
                ->get();

            $data['anStartDate'] = $request->start_date;
            $data['anEndDate'] = $request->end_date;

        }else{
            $agendas = Agenda::whereIn('userid', $employeesList)
                ->whereBetween('date', [$formattedStartDate , $formattedEndDate])
                ->groupBy('userid')
                ->selectRaw('userid, MAX(id) as id')
                ->orderByDesc('id')
                ->get();

            $data['anStartDate'] = $formattedStartDate;
            $data['anEndDate'] = $formattedEndDate;

        }

        $data['agendas'] = $agendas;
        $data['searchTitle'] = 'On My Supervision';
        return view('admin.agenda.index',$data);
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
            $agendas =  Agenda::whereIn('userid', $employeesList)
                ->whereBetween('date', [$request->start_date , $request->end_date])
                ->groupBy('userid')
                ->selectRaw('userid, MAX(id) as id')
                ->orderByDesc('id')
                ->get();

            $data['anStartDate'] = $request->start_date;
            $data['anEndDate'] = $request->end_date;

        }else{

            $agendas =  Agenda::whereIn('userid', $employeesList)
                ->whereBetween('date', [$formattedStartDate , $formattedEndDate])
                ->groupBy('userid')
                ->selectRaw('userid, MAX(id) as id')
                ->orderByDesc('id')
                ->get();

            $data['anStartDate'] = $formattedStartDate;
            $data['anEndDate'] = $formattedEndDate;
        }

        $data['agendas'] = $agendas;
        $data['searchTitle'] = 'All Employee';
        return view('admin.agenda.index',$data);
    }
}
