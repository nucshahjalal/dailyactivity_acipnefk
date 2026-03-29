<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Models\Rate;
use App\Models\Task;
use App\Models\User;
use App\Models\Upazilla;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    public function today(Request $request){
        $data['title'] = "Today Report";
        $data['slider_type'] = 'Today';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        // ---------------- TODAY ONLY ----------------
        $formattedStartDate = Carbon::now('Asia/Dhaka')->toDateString();
        $formattedEndDate   = $formattedStartDate;
        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        $anStartDate = $formattedStartDate;
        $anEndDate = $formattedEndDate;

        // ---------------- NORMALIZE DESIGNATION ----------------
        function normalizeDesignation($d)
        {
            $d = strtolower($d);

            // Replace all unicode dashes, hyphens with space
            $d = str_replace(
                ['–', '—', '−', '―', '‐', '-', '_'],
                ' ',
                $d
            );

            // Replace dots and commas with space
            $d = str_replace(['.', ','], ' ', $d);

            // Collapse multiple spaces into one
            $d = preg_replace('/\s+/', ' ', $d);

            return trim($d);
        }

        // ---------------- GROUP KEY FUNCTION ----------------
        function groupKey($normalized)
        {
            if (str_contains($normalized, "executive")) {
                return "executive";
            }
            if (str_contains($normalized, "manager")) {
                return "manager";
            }
            if (str_contains($normalized, "director")) {
                return "director";
            }

            return $normalized; // other designations → unique group
        }

        // ---------------- RANDOM COLOR GENERATOR ----------------
        function randomColorExcept($excludeColors)
        {
            do {
                $color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
            } while (in_array(strtolower($color), $excludeColors));

            return $color;
        }

        // ---------------- FIXED GROUP COLORS ----------------
        $fixedColors = [
            'executive' => '#ff5733', // bright orange
            'manager'   => '#3399ff', // bright blue
            'director'  => '#28a745', // bright green
        ];

        // Colors to avoid in random assignment
        $exclude = array_map('strtolower', $fixedColors);

        // ---------------- FETCH DISTINCT DESIGNATIONS ----------------
        $designations = User::whereNotNull('type')
            ->whereNotNull('emp_designation')
            ->distinct()
            ->pluck('emp_designation')
            ->toArray();

        // ---------------- ASSIGN COLORS ----------------
        $designationColors = [];
        $groupColor = []; // stores color per group to ensure same color for duplicates

        foreach ($designations as $d) {

            $normalized = normalizeDesignation($d);
            $groupKey   = groupKey($normalized);

            // If group already has a color → reuse it
            if (isset($groupColor[$groupKey])) {
                $designationColors[$d] = $groupColor[$groupKey];
                continue;
            }

            // Assign fixed colors for main groups
            if ($groupKey === "executive") {
                $color = $fixedColors['executive'];
            }
            elseif ($groupKey === "manager") {
                $color = $fixedColors['manager'];
            }
            elseif ($groupKey === "director") {
                $color = $fixedColors['director'];
            }
            else {
                // All other designations → random color
                $color = randomColorExcept($exclude);
            }

            $designationColors[$d] = $color;
            $groupColor[$groupKey] = $color; // store for reuse
        }

        // ---------------- PASS TO BLADE ----------------
        $data['designation_colors'] = $designationColors;

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();
        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        // ACTUAL VISITS (with visited_territory = seleted_territory)
        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data - WITH DESIGNATION
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Store visits with both name and designation
            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                // Create a key with name and designation
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['actual_upazilas'] = $upazilas;

        // PLANNED VISITS (without visited_territory = seleted_territory condition)
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        $plan_upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge plan data with designation
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();
        $data['total_planned_visit'] = $planData->count();
        $data['total_territory'] = $total_territory;
        $data['plan_upazilas'] = $plan_upazilas;
        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('common_view',$data);
    }
    public function yesterday(Request $request){
        $data['title'] = "Yesterday Report";
        $data['slider_type'] = 'Yesterday';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        // ---------------- Yesterday ONLY ----------------
        $formattedStartDate = Carbon::yesterday('Asia/Dhaka')->toDateString();
        $formattedEndDate   = $formattedStartDate;

        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        $anStartDate = $formattedStartDate;
        $anEndDate = $formattedEndDate;

        // ---------------- NORMALIZE DESIGNATION ----------------
        function normalizeDesignation($d)
        {
            $d = strtolower($d);

            // Replace all unicode dashes, hyphens with space
            $d = str_replace(
                ['–', '—', '−', '―', '‐', '-', '_'],
                ' ',
                $d
            );

            // Replace dots and commas with space
            $d = str_replace(['.', ','], ' ', $d);

            // Collapse multiple spaces into one
            $d = preg_replace('/\s+/', ' ', $d);

            return trim($d);
        }

        // ---------------- GROUP KEY FUNCTION ----------------
        function groupKey($normalized)
        {
            if (str_contains($normalized, "executive")) {
                return "executive";
            }
            if (str_contains($normalized, "manager")) {
                return "manager";
            }
            if (str_contains($normalized, "director")) {
                return "director";
            }

            return $normalized; // other designations → unique group
        }

        // ---------------- RANDOM COLOR GENERATOR ----------------
        function randomColorExcept($excludeColors)
        {
            do {
                $color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
            } while (in_array(strtolower($color), $excludeColors));

            return $color;
        }

        // ---------------- FIXED GROUP COLORS ----------------
        $fixedColors = [
            'executive' => '#ff5733', // bright orange
            'manager'   => '#3399ff', // bright blue
            'director'  => '#28a745', // bright green
        ];

        // Colors to avoid in random assignment
        $exclude = array_map('strtolower', $fixedColors);

        // ---------------- FETCH DISTINCT DESIGNATIONS ----------------
        $designations = User::whereNotNull('type')
            ->whereNotNull('emp_designation')
            ->distinct()
            ->pluck('emp_designation')
            ->toArray();

        // ---------------- ASSIGN COLORS ----------------
        $designationColors = [];
        $groupColor = []; // stores color per group to ensure same color for duplicates

        foreach ($designations as $d) {

            $normalized = normalizeDesignation($d);
            $groupKey   = groupKey($normalized);

            // If group already has a color → reuse it
            if (isset($groupColor[$groupKey])) {
                $designationColors[$d] = $groupColor[$groupKey];
                continue;
            }

            // Assign fixed colors for main groups
            if ($groupKey === "executive") {
                $color = $fixedColors['executive'];
            }
            elseif ($groupKey === "manager") {
                $color = $fixedColors['manager'];
            }
            elseif ($groupKey === "director") {
                $color = $fixedColors['director'];
            }
            else {
                // All other designations → random color
                $color = randomColorExcept($exclude);
            }

            $designationColors[$d] = $color;
            $groupColor[$groupKey] = $color; // store for reuse
        }

        // ---------------- PASS TO BLADE ----------------
        $data['designation_colors'] = $designationColors;

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();
        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        // ACTUAL VISITS (with visited_territory = seleted_territory)
        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data - WITH DESIGNATION
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Store visits with both name and designation
            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                // Create a key with name and designation
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['actual_upazilas'] = $upazilas;

        // PLANNED VISITS (without visited_territory = seleted_territory condition)
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        $plan_upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge plan data with designation
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();
        $data['total_planned_visit'] = $planData->count();
        $data['total_territory'] = $total_territory;
        $data['plan_upazilas'] = $plan_upazilas;
        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('common_view',$data);
    }
    public function last_three_month(Request $request){
        $data['title'] = "Last Three Month Report";
        $data['slider_type'] = 'Last Three Month';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        // ---------------- LAST 3 MONTH FIX ----------------
        $startDate = Carbon::now('Asia/Dhaka')->subMonths(2)->startOfMonth();
        $endDate   = Carbon::now('Asia/Dhaka')->endOfMonth();

        $formattedStartDate = $startDate->toDateString();
        $formattedEndDate   = $endDate->toDateString();

        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        $anStartDate = $formattedStartDate;
        $anEndDate = $formattedEndDate;

        // ---------------- NORMALIZE DESIGNATION ----------------
        function normalizeDesignation($d)
        {
            $d = strtolower($d);

            // Replace all unicode dashes, hyphens with space
            $d = str_replace(
                ['–', '—', '−', '―', '‐', '-', '_'],
                ' ',
                $d
            );

            // Replace dots and commas with space
            $d = str_replace(['.', ','], ' ', $d);

            // Collapse multiple spaces into one
            $d = preg_replace('/\s+/', ' ', $d);

            return trim($d);
        }

        // ---------------- GROUP KEY FUNCTION ----------------
        function groupKey($normalized)
        {
            if (str_contains($normalized, "executive")) {
                return "executive";
            }
            if (str_contains($normalized, "manager")) {
                return "manager";
            }
            if (str_contains($normalized, "director")) {
                return "director";
            }

            return $normalized; // other designations → unique group
        }

        // ---------------- RANDOM COLOR GENERATOR ----------------
        function randomColorExcept($excludeColors)
        {
            do {
                $color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
            } while (in_array(strtolower($color), $excludeColors));

            return $color;
        }

        // ---------------- FIXED GROUP COLORS ----------------
        $fixedColors = [
            'executive' => '#ff5733', // bright orange
            'manager'   => '#3399ff', // bright blue
            'director'  => '#28a745', // bright green
        ];

        // Colors to avoid in random assignment
        $exclude = array_map('strtolower', $fixedColors);

        // ---------------- FETCH DISTINCT DESIGNATIONS ----------------
        $designations = User::whereNotNull('type')
            ->whereNotNull('emp_designation')
            ->distinct()
            ->pluck('emp_designation')
            ->toArray();

        // ---------------- ASSIGN COLORS ----------------
        $designationColors = [];
        $groupColor = []; // stores color per group to ensure same color for duplicates

        foreach ($designations as $d) {

            $normalized = normalizeDesignation($d);
            $groupKey   = groupKey($normalized);

            // If group already has a color → reuse it
            if (isset($groupColor[$groupKey])) {
                $designationColors[$d] = $groupColor[$groupKey];
                continue;
            }

            // Assign fixed colors for main groups
            if ($groupKey === "executive") {
                $color = $fixedColors['executive'];
            }
            elseif ($groupKey === "manager") {
                $color = $fixedColors['manager'];
            }
            elseif ($groupKey === "director") {
                $color = $fixedColors['director'];
            }
            else {
                // All other designations → random color
                $color = randomColorExcept($exclude);
            }

            $designationColors[$d] = $color;
            $groupColor[$groupKey] = $color; // store for reuse
        }

        // ---------------- PASS TO BLADE ----------------
        $data['designation_colors'] = $designationColors;

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();
        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        // ACTUAL VISITS (with visited_territory = seleted_territory)
        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data - WITH DESIGNATION
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Store visits with both name and designation
            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                // Create a key with name and designation
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['actual_upazilas'] = $upazilas;

        // PLANNED VISITS (without visited_territory = seleted_territory condition)
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        $plan_upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge plan data with designation
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();
        $data['total_planned_visit'] = $planData->count();
        $data['total_territory'] = $total_territory;
        $data['plan_upazilas'] = $plan_upazilas;
        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('common_view',$data);
    }
    public function monthly(Request $request){
        $data['title'] = "Monthly Report";
        $data['slider_type'] = 'Monthly Map';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString();
        $formattedEndDate = $endDate->toDateString();

        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        $anStartDate = $formattedStartDate;
        $anEndDate = $formattedEndDate;

        // ---------------- NORMALIZE DESIGNATION ----------------
        function normalizeDesignation($d)
        {
            $d = strtolower($d);

            // Replace all unicode dashes, hyphens with space
            $d = str_replace(
                ['–', '—', '−', '―', '‐', '-', '_'],
                ' ',
                $d
            );

            // Replace dots and commas with space
            $d = str_replace(['.', ','], ' ', $d);

            // Collapse multiple spaces into one
            $d = preg_replace('/\s+/', ' ', $d);

            return trim($d);
        }

        // ---------------- GROUP KEY FUNCTION ----------------
        function groupKey($normalized)
        {
            if (str_contains($normalized, "executive")) {
                return "executive";
            }
            if (str_contains($normalized, "manager")) {
                return "manager";
            }
            if (str_contains($normalized, "director")) {
                return "director";
            }

            return $normalized; // other designations → unique group
        }

        // ---------------- RANDOM COLOR GENERATOR ----------------
        function randomColorExcept($excludeColors)
        {
            do {
                $color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
            } while (in_array(strtolower($color), $excludeColors));

            return $color;
        }

        // ---------------- FIXED GROUP COLORS ----------------
        $fixedColors = [
            'executive' => '#ff5733', // bright orange
            'manager'   => '#3399ff', // bright blue
            'director'  => '#28a745', // bright green
        ];

        // Colors to avoid in random assignment
        $exclude = array_map('strtolower', $fixedColors);

        // ---------------- FETCH DISTINCT DESIGNATIONS ----------------
        $designations = User::whereNotNull('type')
            ->whereNotNull('emp_designation')
            ->distinct()
            ->pluck('emp_designation')
            ->toArray();

        // ---------------- ASSIGN COLORS ----------------
        $designationColors = [];
        $groupColor = []; // stores color per group to ensure same color for duplicates

        foreach ($designations as $d) {

            $normalized = normalizeDesignation($d);
            $groupKey   = groupKey($normalized);

            // If group already has a color → reuse it
            if (isset($groupColor[$groupKey])) {
                $designationColors[$d] = $groupColor[$groupKey];
                continue;
            }

            // Assign fixed colors for main groups
            if ($groupKey === "executive") {
                $color = $fixedColors['executive'];
            }
            elseif ($groupKey === "manager") {
                $color = $fixedColors['manager'];
            }
            elseif ($groupKey === "director") {
                $color = $fixedColors['director'];
            }
            else {
                // All other designations → random color
                $color = randomColorExcept($exclude);
            }

            $designationColors[$d] = $color;
            $groupColor[$groupKey] = $color; // store for reuse
        }

        // ---------------- PASS TO BLADE ----------------
        $data['designation_colors'] = $designationColors;

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();
        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        // ACTUAL VISITS (with visited_territory = seleted_territory)
        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);
        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data - WITH DESIGNATION
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Store visits with both name and designation
            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                // Create a key with name and designation
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['actual_upazilas'] = $upazilas;

        // PLANNED VISITS (without visited_territory = seleted_territory condition)
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);
        $plan_upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge plan data with designation
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();
        $data['total_planned_visit'] = $planData->count();
        $data['total_territory'] = $total_territory;
        $data['plan_upazilas'] = $plan_upazilas;
        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('common_view',$data);
    }

    public function main_report(Request $request){
        $data['title'] = "Main Map";
        $data['slider_type'] = 'Main Map';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString();
        $formattedEndDate = $endDate->toDateString();

        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        if(isset($request->start_date) && isset($request->end_date)) {
            $anStartDate = $request->start_date;
            $anEndDate = $request->end_date;
        } else {
            $anStartDate = $formattedStartDate;
            $anEndDate = $formattedEndDate;
        }

        // ---------------- NORMALIZE DESIGNATION ----------------
        function normalizeDesignation($d)
        {
            $d = strtolower($d);

            // Replace all unicode dashes, hyphens with space
            $d = str_replace(
                ['–', '—', '−', '―', '‐', '-', '_'],
                ' ',
                $d
            );

            // Replace dots and commas with space
            $d = str_replace(['.', ','], ' ', $d);

            // Collapse multiple spaces into one
            $d = preg_replace('/\s+/', ' ', $d);

            return trim($d);
        }

        // ---------------- GROUP KEY FUNCTION ----------------
        function groupKey($normalized)
        {
            if (str_contains($normalized, "executive")) {
                return "executive";
            }
            if (str_contains($normalized, "manager")) {
                return "manager";
            }
            if (str_contains($normalized, "director")) {
                return "director";
            }

            return $normalized; // other designations → unique group
        }

        // ---------------- RANDOM COLOR GENERATOR ----------------
        function randomColorExcept($excludeColors)
        {
            do {
                $color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
            } while (in_array(strtolower($color), $excludeColors));

            return $color;
        }

        // ---------------- FIXED GROUP COLORS ----------------
        $fixedColors = [
            'executive' => '#ff5733', // bright orange
            'manager'   => '#3399ff', // bright blue
            'director'  => '#28a745', // bright green
        ];

        // Colors to avoid in random assignment
        $exclude = array_map('strtolower', $fixedColors);

        // ---------------- FETCH DISTINCT DESIGNATIONS ----------------
        $designations = User::whereNotNull('type')
            ->whereNotNull('emp_designation')
            ->distinct()
            ->pluck('emp_designation')
            ->toArray();
      //  dd($designations);

        // ---------------- ASSIGN COLORS ----------------
        $designationColors = [];
        $groupColor = []; // stores color per group to ensure same color for duplicates

        foreach ($designations as $d) {

            $normalized = normalizeDesignation($d);
            $groupKey   = groupKey($normalized);

            // If group already has a color → reuse it
            if (isset($groupColor[$groupKey])) {
                $designationColors[$d] = $groupColor[$groupKey];
                continue;
            }

            // Assign fixed colors for main groups
            if ($groupKey === "executive") {
                $color = $fixedColors['executive'];
            }
            elseif ($groupKey === "manager") {
                $color = $fixedColors['manager'];
            }
            elseif ($groupKey === "director") {
                $color = $fixedColors['director'];
            }
            else {
                // All other designations → random color
                $color = randomColorExcept($exclude);
            }

            $designationColors[$d] = $color;
            $groupColor[$groupKey] = $color; // store for reuse
        }

        // ---------------- PASS TO BLADE ----------------
        $data['designation_colors'] = $designationColors;

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();
        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        // ACTUAL VISITS (with visited_territory = seleted_territory)
        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);
        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data - WITH DESIGNATION
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Store visits with both name and designation
            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                // Create a key with name and designation
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['actual_upazilas'] = $upazilas;

        // PLANNED VISITS (without visited_territory = seleted_territory condition)
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        $plan_upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge plan data with designation
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name));

            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                $visitName = strtolower($visit->territory_name);
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            $userVisitDetails = $visitCounts->mapWithKeys(function ($visit) {
                $userInfo = $visit->user_name . ' (' . $visit->emp_designation . ')';
                return [$userInfo => $visit->visit_count];
            });

            $feature['properties']['visit_details'] = $userVisitDetails;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();
        $data['total_planned_visit'] = $planData->count();
        $data['total_territory'] = $total_territory;
        $data['plan_upazilas'] = $plan_upazilas;
        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('main_report',$data);
    }

    public function map_report(Request $request){
        $data['slider_type'] = 'Map Report';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        $data['title'] = "Map Report";

        // Convert arrays to Laravel Collections
        $proposed_upazilla = collect([
            "Rampal","Kachua","Phultala","Chitalmari","Fakirhat","Sreebardi","Lakhai",
            "Ajmiriganj","Nalchity","Hizla","Wazirpur","Gaurnadi","LALMAI","Kuliar Char",
            "Brahmanpara","Burichang","Ramganj","Haim Char","Faridpur","Kutubdia","Savar",
            "Hathazari","Chhagalnaiya","Fulchhari","JOYDEBPUR","Kaliakair","Kaliganj",
            "Gazipur Sadar","Sulla","Balaganj","Bagherpara","Mithamain","Rupsa","Terokhada",
            "Batiaghata","Kotchandpur","Juri","Kalkini","Dhanbari","Shibalaya","Kachua",
            "Tongibari","Sonargaon","Narayanganj Sadar","Lohajang","Bandar","Muradnagar",
            "Manoharganj","Raninagar","Kalia","Araihazar","Rupganj","Gobindaganj",
            "Sadullapur","Bagha","Baliakandi","Paba","Char Rajibpur","Basail","Ashuganj",
            "Debhata","Chauhali","Damudya","Abhaynagar","Keshabpur","Nawabganj","Dohar",
            "Gabtali","Santhia","Jaintiapur","Dakshin Surma","Zakiganj","Meghna",
            "Daudkandi","Fulbaria"
        ]);

        $existing_upazilla = collect([
            "Raumari","Purbadhala","Jagannathpur","Sherpur","Bhurungamari","Tetulia",
            "Godagari","Bheramara","Sreepur","Mohammadpur","Ramgati","Dhobaura","Tarail",
            "Sakhipur","Kawkhali","Nesarabad","Dighalia","Kanthalia","Ishwardi","Atgharia",
            "Wazirpur","Khulna Sadar Thana","Dumuria","Mirzaganj","Mymensingh Sadar","Melandaha"
        ]);

        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // --- Proposed Upazilla Color Mapping ---
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(trim($name));

            $match = $proposed_upazilla->contains(function($item) use ($normalizedName) {
                return strtolower($item) == $normalizedName;
            });

            $feature['properties']['is_proposed'] = $match ? 1 : 0;
        }

        $data['proposed_upazilla'] = $upazilas;

        // --- Existing Upazilla Color Mapping ---
        $sec_upazilas = json_decode(file_get_contents($geojsonPath), true);

        foreach ($sec_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4'];
            $normalizedName = strtolower(trim($name));

            $match = $existing_upazilla->contains(function($item) use ($normalizedName) {
                return strtolower($item) == $normalizedName;
            });

            $feature['properties']['is_existing'] = $match ? 1 : 0;
        }

        $data['existing_upazilla'] = $sec_upazilas;

        return view('map_report', $data);
    }

    public function index(Request $request){
        $data['title'] = "Hire Purchase";
        $data['slider_type'] = 'hire';
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        // EMPLOYEE FILTER
        $emp_filter = $request->user_id ?? null;
        $data['emp_filter'] = $emp_filter;

        if(isset($request->start_date) && isset($request->end_date))
        {

            $anStartDate = $request->start_date;
            $anEndDate = $request->end_date;

        }else{

            $anStartDate = $formattedStartDate;
            $anEndDate = $formattedEndDate;
        }

        $employees = User::where('type','<>',NULL)->get();
        $data['employees'] = $employees;

        if ($request->filled('user_id')) {
            $employeesList = is_array($request->user_id)
                ? $request->user_id
                : [$request->user_id];   // convert single id â†’ array
        } else {
            $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $totalEmployee = User::where('type','<>',NULL)->count();

        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');
        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        $upazillaData = $this->fetchTaskUpazillaData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');

        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4']; // Adjust property name if needed

            // Normalize the GeoJSON territory name (e.g., remove "S." or other suffixes)
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name)); // Remove "S." and normalize

            // Find matching visits from $upazillaData
            $visitCounts = $upazillaData->filter(function ($visit) use ($normalizedName) {
                // Normalize the query territory name
                $visitName = strtolower($visit->territory_name);

                // Perform partial or fuzzy matching
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Format visit count per user name
            $userVisitCounts = $visitCounts->mapWithKeys(function ($visit) {
                return [$visit->user_name => $visit->visit_count];
            });

            // Store visits per user name
            $feature['properties']['visit_counts'] = $userVisitCounts;
        }

        $data['actual_upazilas'] = $upazilas;

        // Fetch plan Wise visit counts grouped by seleted_territory and user name
        $planData = $this->fetchTaskPlanData($employeesList, $anStartDate, $anEndDate, $upazillaList);

        // Load the GeoJSON file from storage

        $plan_upazilas = $upazilas;

        // Merge visit counts into the GeoJSON data
        foreach ($plan_upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4']; // Adjust property name if needed

            // Normalize the GeoJSON territory name (e.g., remove "S." or other suffixes)
            $normalizedName = strtolower(preg_replace('/\s*S\.\s*/', '', $name)); // Remove "S." and normalize

            // Find matching visits from $planData
            $visitCounts = $planData->filter(function ($visit) use ($normalizedName) {
                // Normalize the query territory name
                $visitName = strtolower($visit->territory_name);

                // Perform partial or fuzzy matching
                return stripos($visitName, $normalizedName) !== false || stripos($normalizedName, $visitName) !== false;
            });

            // Format visit count per user name
            $userVisitCounts = $visitCounts->mapWithKeys(function ($visit) {
                return [$visit->user_name => $visit->visit_count];
            });

            // Store visits per user name
            $feature['properties']['visit_counts'] = $userVisitCounts;
        }

        $data['total_tasks'] = Task::where('is_agenda', '2')
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->join('users', 'task.userid', '=', 'users.id')->where('users.type', '<>', NULL)->count();

        $data['total_actual_visit'] = $upazillaData->count();

        $data['total_planned_visit'] = $planData->count();

        $data['total_territory'] = $total_territory;

        $data['plan_upazilas'] = $plan_upazilas;

        $data['upazillaData'] = $upazillaData;
        $data['planData'] = $planData;

        return view('map',$data);
    }

    public function graphical_overview(Request $request){

        $data['title'] = "Graphical OverView";

        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        if(isset($request->start_date) && isset($request->end_date))
        {

            $anStartDate = $request->start_date;
            $anEndDate = $request->end_date;

        }else{
            $anStartDate = $formattedStartDate;
            $anEndDate = $formattedEndDate;
        }

        $employees = User::where('type','<>',NULL)->get();

        $employeesList = $employees->pluck('id')->map(fn($id) => (int) $id);
        $totalEmployee = User::where('type','<>',NULL)->count();

        $existingUsers = Task::where('is_agenda', '2')
        ->whereIn('userid', $employeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()
        ->unique('userid')
        ->count();

        $notExistingUsers = $totalEmployee - $existingUsers;


        $total_actual_visit = Task::where('is_agenda', '2')->whereIn('userid', $employeesList)
        ->where('seleted_territory', '<>', 'none')
        ->whereColumn('visited_territory', 'seleted_territory')
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()
        ->unique('userid')
        ->count();

        $total_not_actual_visit = $totalEmployee - $total_actual_visit;


        $total_planned_visit = Task::where('is_agenda','2')->whereIn('userid', $employeesList)
        ->select('seleted_territory')->distinct()->where('seleted_territory', '<>', 'none')
        ->whereBetween('date', [$anStartDate, $anEndDate])->count();

        $total_unplanned_visit = $totalEmployee - $total_planned_visit;


        $recoveryEmployees = User::where('type','2')->get();
        $recoveryEmployeesList = $recoveryEmployees->pluck('id')->map(fn($id) => (int) $id);
        $totalRecoveryEmployee = User::where('type','2')->count();

        $recoveryExistingUsers = Task::where('is_agenda','2')
        ->whereIn('userid', $recoveryEmployeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()->unique('userid')->count();

        $notRecoveryExistingUsers = $totalRecoveryEmployee - $recoveryExistingUsers;

        $serviceEmployees = User::where('type','0')->get();
        $serviceEmployeesList = $serviceEmployees->pluck('id')->map(fn($id) => (int) $id);
        $totalServiceEmployee = User::where('type','0')->count();

        $serviceExistingUsers = Task::where('is_agenda', '2')
        ->whereIn('userid', $serviceEmployeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()->unique('userid')->count();

        $notServiceExistingUsers = $totalServiceEmployee - $serviceExistingUsers;

        $marketing_SUB11_Employees = User::where('type','1')->where('sub_type','11')->get();
        $marketing_SUB11_EmployeesList = $marketing_SUB11_Employees->pluck('id')->map(fn($id) => (int) $id);
        $totalMerketing_SUB11_Employee = User::where('type','1')->where('sub_type','11')->count();

        $marketing_SUB11_ExistingUsers = Task::where('is_agenda', '2')
        ->whereIn('userid', $marketing_SUB11_EmployeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()->unique('userid')->count();

        $notMarketing_SUB11_ExistingUsers = $totalMerketing_SUB11_Employee - $marketing_SUB11_ExistingUsers;

        $marketing_SUB12_Employees = User::where('type','1')->where('sub_type','12')->get();
        $marketing_SUB12_EmployeesList = $marketing_SUB12_Employees->pluck('id')->map(fn($id) => (int) $id);
        $totalMerketing_SUB12_Employee = User::where('type','1')->where('sub_type','12')->count();

        $marketing_SUB12_ExistingUsers = Task::where('is_agenda', '2')
        ->whereIn('userid', $marketing_SUB12_EmployeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()->unique('userid')->count();

        $notMarketing_SUB12_ExistingUsers = $totalMerketing_SUB12_Employee - $marketing_SUB12_ExistingUsers;

        $marketing_SUB13_Employees = User::where('type','1')->where('sub_type','13')->get();
        $marketing_SUB13_EmployeesList = $marketing_SUB13_Employees->pluck('id')->map(fn($id) => (int) $id);
        $totalMerketing_SUB13_Employee = User::where('type','1')->where('sub_type','13')->count();

        $marketing_SUB13_ExistingUsers = Task::where('is_agenda', '2')
        ->whereIn('userid', $marketing_SUB13_EmployeesList)
        ->whereBetween('date', [$anStartDate, $anEndDate])
        ->get()->unique('userid')->count();

        $notMarketing_SUB13_ExistingUsers = $totalMerketing_SUB13_Employee - $marketing_SUB13_ExistingUsers;

        $upazillaList = Upazilla::distinct()->pluck('UpazillaName');

        $total_territory = Upazilla::distinct()->pluck('Sales & Recovery Territory Name')->count();

        $territory_wise_planned_visits = Task::where('is_agenda', '2')
            ->whereIn('userid', $employeesList)
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->where(function ($query) use ($upazillaList) {
                foreach ($upazillaList as $territory) {
                    $query->orWhere('seleted_territory', 'LIKE', "%{$territory}%");
                }
            })
            ->join('upazillas', function($join) {
                $join->on('upazillas.upazillaName', 'LIKE', DB::raw('CONCAT("%", task.seleted_territory, "%")'));
            })
            ->select(
                'upazillas.Sales & Recovery Territory Name', // Use the exact column name
                DB::raw('COUNT(*) as territory_count')
            )
            ->groupBy('upazillas.Sales & Recovery Territory Name')
            ->get();

        $total_territory_wise_planned_visit = $territory_wise_planned_visits->count();
        $total_territory_wise_not_planned_visit = $total_territory - $total_territory_wise_planned_visit;

        $territory_wise_actual_visits = Task::where('is_agenda', '2')
            ->whereIn('userid', $employeesList)
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->where(function ($query) use ($upazillaList) {
                foreach ($upazillaList as $territory) {
                    $query->orWhere('seleted_territory', 'LIKE', "%{$territory}%");
                }
            })
            ->whereColumn('visited_territory', 'seleted_territory')
            ->join('upazillas', function($join) {
                $join->on('upazillas.upazillaName', 'LIKE', DB::raw('CONCAT("%", task.seleted_territory, "%")'));
            })
            ->select(
                'upazillas.Sales & Recovery Territory Name', // Use the exact column name
                DB::raw('COUNT(*) as territory_count')
            )
            ->groupBy('upazillas.Sales & Recovery Territory Name')
            ->get();

        $total_territory_wise_actual_visit = $territory_wise_actual_visits->count();
        $total_territory_wise_not_actual_visit = $total_territory - $total_territory_wise_actual_visit;

        // Process all visit plans
        $territoryVisitPlans = Task::where('is_agenda', '2')
            ->whereIn('userid', $employeesList)
            ->whereBetween('date', [$anStartDate, $anEndDate])
            ->where(function ($query) use ($upazillaList) {
                foreach ($upazillaList as $territory) {
                    $query->orWhere('seleted_territory', 'LIKE', "%{$territory}%");
                }
            })
            ->join('upazillas', function ($join) {
                $join->on(
                    'upazillas.upazillaName',
                    'LIKE',
                    DB::raw('CONCAT("%", task.seleted_territory, "%")')
                );
            })
            ->join('users', 'users.id', '=', 'task.userid')
            ->select(
                'upazillas.Sales & Recovery Territory Name',
                'task.userid',
                'users.emp_name',
                'users.emp_id',
                'users.emp_designation',
                'task.seleted_territory',
                DB::raw('COUNT(task.id) as total_tasks'),
                DB::raw('COUNT(DISTINCT CONCAT(task.userid, "-", task.date)) as active_days'),
                DB::raw('COUNT(task.seleted_territory) as planned_visits'),
                DB::raw('COUNT(CASE WHEN task.seleted_territory = task.visited_territory THEN 1 END) as actual_visits'),
                DB::raw('MAX(task.date) as latest_date'),
                DB::raw('(SELECT details FROM task t2
                        WHERE t2.userid = task.userid
                        AND t2.seleted_territory = task.seleted_territory
                        ORDER BY t2.date DESC, t2.id DESC
                        LIMIT 1) as latest_details')
            )
            ->groupBy(
                'upazillas.Sales & Recovery Territory Name',
                'task.userid',
                'users.emp_name',
                'users.emp_id',
                'users.emp_designation',
                'task.seleted_territory'
            )
            ->get();

        // ðŸ”¹ Add performance %
        $territoryVisitPlans = $territoryVisitPlans->map(function ($plan) {
            $plan->performance = $plan->planned_visits > 0
                ? round(($plan->actual_visits / $plan->planned_visits) * 100, 2)
                : 0;
            return $plan;
        });

        // ðŸ”¹ Sort by performance and assign rank
        $rankedPlans = $territoryVisitPlans
            ->sortByDesc('performance')
            ->values()
            ->map(function ($plan, $index) {
                $plan->rank = $index + 1;

                // Optional: Add rating text
                if ($plan->performance >= 90) {
                    $plan->rating = 'Excellent';
                } elseif ($plan->performance >= 75) {
                    $plan->rating = 'Good';
                } elseif ($plan->performance >= 50) {
                    $plan->rating = 'Average';
                } else {
                    $plan->rating = 'Poor';
                }

                return $plan;
            });

        $employeesWithTourCount = Cache::remember('employees_tour_count_' . $anStartDate . '_' . $anEndDate, 60, function () use ($anStartDate, $anEndDate) {
            return User::whereNotNull('users.type')
                ->selectRaw('
                    users.id,
                    users.emp_id,
                    users.emp_name,
                    users.portfolio,
                    COUNT(DISTINCT task.date) as no_of_tours,
                    COUNT(task.seleted_territory) as selected_territory_count,
                    COUNT(CASE WHEN task.seleted_territory = task.visited_territory THEN 1 END) as visited_territory_count,
                    CASE
                        WHEN COUNT(DISTINCT task.date) > 0 THEN "Active"
                        ELSE "Inactive"
                    END as status
                ')
                ->leftJoin('task', function ($join) use ($anStartDate, $anEndDate) {
                    $join->on('users.id', '=', 'task.userid')
                            ->where('task.is_agenda', '2')
                            ->where('seleted_territory', '<>', 'none')
                            ->whereBetween('task.date', [$anStartDate, $anEndDate]);
                })
                ->groupBy('users.id', 'users.emp_id', 'users.emp_name', 'users.portfolio') // Group by all non-aggregated columns
                ->orderByDesc('no_of_tours')
                ->get();
        });

        $data['totalEmployee'] = $totalEmployee;
        $data['existingUsers'] = $existingUsers;
        $data['notExistingUsers'] = $notExistingUsers;

        $data['totalRecoveryEmployee'] = $totalRecoveryEmployee;
        $data['recoveryExistingUsers'] = $recoveryExistingUsers;
        $data['notRecoveryExistingUsers'] = $notRecoveryExistingUsers;

        $data['totalServiceEmployee'] = $totalServiceEmployee;
        $data['serviceExistingUsers'] = $serviceExistingUsers;
        $data['notServiceExistingUsers'] = $notServiceExistingUsers;

        $data['totalMerketing_SUB11_Employee'] = $totalMerketing_SUB11_Employee;
        $data['marketing_SUB11_ExistingUsers'] = $marketing_SUB11_ExistingUsers;
        $data['notMarketing_SUB11_ExistingUsers'] = $notMarketing_SUB11_ExistingUsers;

        $data['totalMerketing_SUB12_Employee'] = $totalMerketing_SUB12_Employee;
        $data['marketing_SUB12_ExistingUsers'] = $marketing_SUB12_ExistingUsers;
        $data['notMarketing_SUB12_ExistingUsers'] = $notMarketing_SUB12_ExistingUsers;

        $data['totalMerketing_SUB13_Employee'] = $totalMerketing_SUB13_Employee;
        $data['marketing_SUB13_ExistingUsers'] = $marketing_SUB13_ExistingUsers;
        $data['notMarketing_SUB13_ExistingUsers'] = $notMarketing_SUB13_ExistingUsers;

        $data['total_actual_visit'] = $total_actual_visit;
        $data['total_not_actual_visit'] = $total_not_actual_visit;

        $data['total_planned_visit'] = $total_planned_visit;
        $data['total_unplanned_visit'] = $total_unplanned_visit;

        $data['employeesWithTourCount'] = $employeesWithTourCount;
        $data['territoryVisitPlans'] = $territoryVisitPlans;
        $data['rankedPlans'] = $rankedPlans;

        $data['total_territory'] = $total_territory;
        $data['total_territory_wise_actual_visit'] = $total_territory_wise_actual_visit;
        $data['total_territory_wise_not_actual_visit'] = $total_territory_wise_not_actual_visit;

        $data['total_territory_wise_planned_visit'] = $total_territory_wise_planned_visit;
        $data['total_territory_wise_not_planned_visit'] = $total_territory_wise_not_planned_visit;

        return view('graphical_overview',$data);
    }

    public function rating_details(Request $request){
        $rates =  Rate::where('visitor_id',$request->empId)->get();
        $data = view('rating_details',compact(['rates']))->render();

        return response()->json(['options'=>$data]);
    }

    private function fetchTaskUpazillaData(array $employeesList, $anStartDate, $anEndDate, $upazillaList){
        return Task::where('is_agenda', '2')
                ->whereIn('userid', $employeesList)
                ->whereBetween('date', [$anStartDate, $anEndDate])

                /* -------- MATCH SELECTED TERRITORY (JSON + STRING) -------- */
                ->where(function ($query) use ($upazillaList) {
                    foreach ($upazillaList as $territory) {

                        $query->orWhere(function ($q) use ($territory) {

                            // JSON array case
                            $q->whereRaw("
                                JSON_VALID(task.seleted_territory)
                                AND JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(?))
                            ", [$territory])

                            // Normal string case
                            ->orWhere(function ($q2) use ($territory) {
                                $q2->whereRaw("NOT JSON_VALID(task.seleted_territory)")
                                ->where('task.seleted_territory', 'LIKE', "%{$territory}%");
                            });
                        });
                    }
                })

                /* -------- MATCH VISITED vs SELECTED (fixed for MariaDB < 10.6) -------- */
                ->where(function ($q) {

                    // JSON arrays: check if any visited_territory exists in selected_territory
                    $q->whereRaw("
                        JSON_VALID(task.seleted_territory)
                        AND JSON_VALID(task.visited_territory)
                        AND (
                            JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(JSON_UNQUOTE(JSON_EXTRACT(task.visited_territory, '$[0]'))))
                            OR JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(JSON_UNQUOTE(JSON_EXTRACT(task.visited_territory, '$[1]'))))
                            OR JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(JSON_UNQUOTE(JSON_EXTRACT(task.visited_territory, '$[2]'))))
                        )
                    ")

                    // Normal string exact match
                    ->orWhere(function ($q2) {
                        $q2->whereRaw("NOT JSON_VALID(task.seleted_territory)")
                        ->whereColumn('task.visited_territory', 'task.seleted_territory');
                    });
                })

                /* -------- JOIN WITH UPAZILLAS (JSON + STRING SAFE) -------- */
                ->join('upazillas', function ($join) {
                    $join->where(function ($q) {

                        // JSON array → upazilla name exists in array
                        $q->whereRaw("
                            JSON_VALID(task.seleted_territory)
                            AND JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(upazillas.upazillaName))
                        ")

                        // Normal string → old LIKE logic
                        ->orWhere(function ($q2) {
                            $q2->whereRaw("NOT JSON_VALID(task.seleted_territory)")
                            ->whereColumn(
                                'upazillas.upazillaName',
                                'LIKE',
                                DB::raw('CONCAT("%", task.seleted_territory, "%")')
                            );
                        });
                    });
                })

                /* -------- JOIN USERS -------- */
                ->join('users', 'users.id', '=', 'task.userid')

                /* -------- SELECT -------- */
                ->select(
                    'users.emp_name as user_name',
                    'users.emp_designation as emp_designation',
                    'task.userid',
                    'upazillas.Sales & Recovery Territory Name as territory_name',
                    DB::raw('COUNT(*) as visit_count')
                )

                /* -------- GROUP -------- */
                ->groupBy(
                    'task.userid',
                    'users.emp_name',
                    'users.emp_designation',
                    'upazillas.Sales & Recovery Territory Name'
                )
                ->get();

    }

    private function fetchTaskPlanData(array $employeesList, $anStartDate, $anEndDate, $upazillaList){
        return Task::where('is_agenda', '2')
                ->whereIn('userid', $employeesList)
                ->whereBetween('date', [$anStartDate, $anEndDate])

                /* -------- MATCH SELECTED TERRITORY (JSON + STRING) -------- */
                ->where(function ($query) use ($upazillaList) {
                    foreach ($upazillaList as $territory) {

                        $query->orWhere(function ($q) use ($territory) {

                            // JSON array case
                            $q->whereRaw("
                                JSON_VALID(task.seleted_territory)
                                AND JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(?))
                            ", [$territory])

                            // Normal string case
                            ->orWhere(function ($q2) use ($territory) {
                                $q2->whereRaw("NOT JSON_VALID(task.seleted_territory)")
                                ->where('task.seleted_territory', 'LIKE', "%{$territory}%");
                            });
                        });
                    }
                })

                /* -------- JOIN WITH UPAZILLAS (JSON + STRING SAFE) -------- */
                ->join('upazillas', function ($join) {
                    $join->where(function ($q) {

                        // JSON array → upazilla exists in selected territory
                        $q->whereRaw("
                            JSON_VALID(task.seleted_territory)
                            AND JSON_CONTAINS(task.seleted_territory, JSON_QUOTE(upazillas.upazillaName))
                        ")

                        // Normal string → old LIKE logic
                        ->orWhere(function ($q2) {
                            $q2->whereRaw("NOT JSON_VALID(task.seleted_territory)")
                            ->whereColumn(
                                'upazillas.upazillaName',
                                'LIKE',
                                DB::raw('CONCAT("%", task.seleted_territory, "%")')
                            );
                        });
                    });
                })

                /* -------- JOIN USERS -------- */
                ->join('users', 'users.id', '=', 'task.userid')

                /* -------- SELECT -------- */
                ->select(
                    'users.emp_name as user_name',
                    'users.emp_designation as emp_designation',
                    'task.userid',
                    'upazillas.Sales & Recovery Territory Name as territory_name',
                    DB::raw('COUNT(*) as visit_count')
                )

                /* -------- GROUP -------- */
                ->groupBy(
                    'task.userid',
                    'users.emp_name',
                    'users.emp_designation',
                    'upazillas.Sales & Recovery Territory Name'
                )
                ->get();

    }


}
