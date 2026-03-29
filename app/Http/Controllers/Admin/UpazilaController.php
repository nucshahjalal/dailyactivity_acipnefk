<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UpazilaController extends Controller
{
    public function index()
    {
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $formattedStartDate = $startDate->toDateString(); // Example: '2024-02-01'
        $formattedEndDate = $endDate->toDateString();     // Example: '2024-02-29'

        // Fetch visit counts from the database
        $upazillaData = Task::where('is_agenda', '2')
            ->where('visited_territory', '<>', 'none')
            ->where('seleted_territory', '<>', 'none')
            ->whereColumn('visited_territory', 'seleted_territory')
          //  ->whereBetween('date', [$formattedStartDate, $formattedEndDate])
            ->select('visited_territory', DB::raw('COUNT(id) as visit_count'))
            ->groupBy('visited_territory')
            ->get()
            ->keyBy('visited_territory'); // Key the collection by visited_territory for easy lookup

      //  dd($upazillaData);

        // Load the GeoJSON file from storage
        $geojsonPath = public_path('map/upazilas.geojson');
        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'GeoJSON file not found'], 404);
        }

        $upazilas = json_decode(file_get_contents($geojsonPath), true);

        // Merge visit counts into the GeoJSON data
        foreach ($upazilas['features'] as &$feature) {
            $name = $feature['properties']['NAME_4']; // Replace with the correct property name
            $visitCount = $upazillaData->get($name)->visit_count ?? 0; // Get visit count or default to 0
            $feature['properties']['visit_count'] = $visitCount;
        }

        // Pass the data to the view
        return view('map', ['upazilas' => $upazilas]);
    }
}
