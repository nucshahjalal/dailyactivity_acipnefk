<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\RatingAndReviewController;
use App\Http\Controllers\Admin\sendMailController;
use App\Http\Controllers\Admin\UpazilaController;
use App\Http\Controllers\Admin\MapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route to fetch all upazilas
Route::get('/map-slider', function () {
    return view('partials.map-slider');
})->name('map.page.slider');

Route::get('/today', [MapController::class, 'today'])->name('today');
Route::get('/yesterday', [MapController::class, 'yesterday'])->name('yesterday');
Route::get('/last_three_month', [MapController::class, 'last_three_month'])->name('last_three_month');
Route::get('/monthly', [MapController::class, 'monthly'])->name('monthly');
Route::get('/map', [MapController::class, 'main_report'])->name('main_report');
Route::get('/map_report', [MapController::class, 'map_report'])->name('map_report');
Route::get('/link', [MapController::class, 'index'])->name('map-link');
Route::get('/graphical_overview', [MapController::class, 'graphical_overview'])->name('graphical_overview');
Route::post('app_users/rating/details', [MapController::class, 'rating_details'])->name('show_appuser_rating_details');

//Route::get('/link',[DashboardController::class,'open_link'])->name('open_link_dashboard');
//Send Mail
Route::get('sendMail',[sendMailController::class,'sendMail'])->name('sendMail');

Route::group(['as' => 'admin.', 'namespace' =>'Admin'],function (){

    Route::get('/',[LoginController::class,'index'])->name('login_form');
    Route::post('login',[LoginController::class,'login'])->name('login');

    Route::middleware('auth')->group(function (){



        Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
        Route::get('dashboard/task/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'tasks'])->name('dashboard_task_list');
        Route::get('dashboard/pending/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'pending'])->name('dashboard_pending_list');
        Route::get('dashboard/processing/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'processing'])->name('dashboard_processing_list');
        Route::get('dashboard/done/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'done'])->name('dashboard_done_list');

        Route::get('dashboard/related_action/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'related_action'])->name('dashboard_related_action_list');
        Route::get('dashboard/assigned_by_others/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'assigned_by_others'])->name('dashboard_assigned_by_others_list');
        Route::get('dashboard/agenda_wise_task/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'agenda_wise_task'])->name('dashboard_agenda_wise_task');

        Route::get('dashboard/agenda_submitted/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'agenda_submitted'])->name('dashboard_agenda_submitted');

        Route::get('dashboard/planned_territory/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'planned_territory'])->name('dashboard_planned_territory');
        Route::get('dashboard/visited_territory/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'visited_territory'])->name('dashboard_visited_territory');
        Route::get('dashboard/not_visited_territory/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'not_visited_territory'])->name('dashboard_not_visited_territory');

        Route::get('dashboard/plan_wise_visited/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'plan_wise_visited'])->name('dashboard_plan_wise_visited');
        Route::get('dashboard/not_visited_yet/list/{startDate}/{endDate}/{empId?}',[DashboardController::class,'not_visited_yet'])->name('dashboard_not_visited_yet');

        Route::get('dashboard/territory/list',[DashboardController::class,'territory'])->name('dashboard_territory');

        Route::get('dashboard/of/employee/{empId}',[DashboardController::class,'employeeById'])->name('dashboard_employeeById');

        Route::get('employee/list',[EmployeeController::class,'index'])->name('employee_list');
        Route::get('employee/list/supervision',[EmployeeController::class,'supervision'])->name('employee_list_supervision');
        Route::get('employee/list/active/{startDate}/{endDate}',[EmployeeController::class,'active'])->name('employee_list_active');
        Route::get('employee/list/inactive/{startDate}/{endDate}',[EmployeeController::class,'inactive'])->name('employee_list_inactive');

        Route::get('agenda/on/my_supervision',[AgendaController::class,'my_supervision'])->name('agenda_on_my_supervision');
        Route::get('agenda/all_employee',[AgendaController::class,'all_employee'])->name('agenda_all_employee');

        Route::get('task/on/my_supervision',[TaskController::class,'my_supervision'])->name('task_on_my_supervision');
        Route::get('task/all_employee',[TaskController::class,'all_employee'])->name('task_all_employee');

        Route::get('tour/on/my_supervision',[TourController::class,'my_supervision'])->name('tour_on_my_supervision');
        Route::get('tour/all_employee',[TourController::class,'all_employee'])->name('tour_all_employee');

        Route::get('rating_review/on/my_supervision',[RatingAndReviewController::class,'my_supervision'])->name('rating_review_on_my_supervision');
        Route::get('rating_review/all_employee',[RatingAndReviewController::class,'all_employee'])->name('rating_review_all_employee');
        Route::get('rating_review/add/{employee_id}/{mydate}',[RatingAndReviewController::class,'add'])->name('rating_review_add');
        Route::post('rating_review/store',[RatingAndReviewController::class,'store'])->name('rating_review_store');

        Route::get('employee/assign/task',[RatingAndReviewController::class,'assign_task'])->name('emp_assign_task');
        Route::get('employee/assign/task/create/{employee_id}',[RatingAndReviewController::class,'create_assign_task'])->name('emp_create_assign_task');
        Route::post('employee/assign/task/store',[RatingAndReviewController::class,'assign_task_store'])->name('emp_assign_task_store');

        Route::get('todaysOverview/of/employee/{empId}/{mydate}',[RatingAndReviewController::class,'todaysOverview'])->name('todaysOverview_employeeById');
        Route::get('individual/employee/overall/rating/{employee_id}/{sDate}/{eDate}',[RatingAndReviewController::class,'in_emp_rating'])->name('in_emp_over_all_rating');

        Route::post('admin_logout',[LogoutController::class,'logout'])->name('logout');
    });
});
