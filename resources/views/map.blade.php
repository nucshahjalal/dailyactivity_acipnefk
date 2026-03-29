@extends('admin.layouts.open_link_master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style type="text/css">
        #actualVisitmap {
            height: 500px;
            /* Ensure the map container has height */
            width: 100%;
        }

        #planWisemap {
            height: 500px;
            /* Ensure the map container has height */
            width: 100%;
        }
    </style>
@endsection

@section('contentBody')
    <form action="{{ route('map-link') }}" method="get" class="form-inline mt-1">
        @php
            $start_date = request()->query('start_date', null);
            $end_date = request()->query('end_date', null);
        @endphp
        <div class="form-group mb-2">
            <span class="text-dark">Employee : </span>&nbsp;
            <select name="user_id" style="height: 35px;">
                <option value="">Select Employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}"
                        {{ isset($emp_filter) && $emp_filter == $employee->id ? 'selected' : '' }}>
                        {{ $employee->emp_name }} ({{ $employee->emp_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <span>Start Date: </span>&nbsp;<input type="date" name="start_date" value="{{ $start_date }}"
                class="form-control" required>
        </div>
        <div class="form-group mx-sm-1 mb-2">
            <span>End Date: </span>&nbsp;<input type="date" name="end_date" value="{{ $end_date }}"
                class="form-control" required>
        </div>
        <button type="submit" class="btn btn-rounded btn-primary mb-2"><i class="fa fa-search"></i></button>
        <div style="margin-left: 100px;">
            <span style="font-weight:bold;font-size:20px;">
                {{ $start_date && $end_date ? "$start_date to $end_date" : 'Current Month' }}
            </span>
        </div>
    </form>
    {{-- <h4 class="text-center">{{ $title }}</h4> --}}
    <div class="row mt-2">
        <div class="col-6">
            <h5>Actual Visit
                Map({{ $total_territory > 0
                    ? number_format(($upazillaData->pluck('territory_name')->unique()->count() / $total_territory) * 100, 2) . '%'
                    : '0%' }})
            </h5>
            <div id="actualVisitmap"></div>
        </div>
        <div class="col-6">
            <h5>Plan To Visit
                Map({{ $total_territory > 0
                    ? number_format(($planData->pluck('territory_name')->unique()->count() / $total_territory) * 100, 2) . '%'
                    : '0%' }})
            </h5>
            <div id="planWisemap"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {
            // Ensure the map container exists
            if (!document.getElementById('actualVisitmap')) {
                console.error('Map container not found');
                return;
            }

            // Initialize the map
            const map = L.map('actualVisitmap').setView([23.6850, 90.3563], 7); // Center on Bangladesh

            // Add a tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Ensure upazilas data is available
            var upazilas = {!! json_encode($actual_upazilas) !!}; // Fix Laravel JSON encoding

            if (!upazilas || !upazilas.features) {
                console.error('GeoJSON data is missing or invalid');
                return;
            }

            // Add GeoJSON layer
            L.geoJSON(upazilas, {
                style: function(feature) {
                    let totalVisits = 0;
                    if (feature.properties && feature.properties.visit_counts) {
                        totalVisits = Object.values(feature.properties.visit_counts).reduce((sum,
                            count) => sum + count, 0);
                    }

                    return {
                        fillColor: totalVisits > 0 ? '#ff5c33' :
                        '#FFFFFF', // Orange if visits exist, else white
                        fillOpacity: 0.5, // Slightly higher opacity for better visibility
                        color: '#000000', // Border color black
                        weight: 1
                    };
                },
                onEachFeature: function(feature, layer) {
                    const name = feature.properties?.NAME_4 || 'Unknown';
                    const visitCounts = feature.properties?.visit_counts || {};

                    let visitDetails = Object.entries(visitCounts).map(([user, count]) =>
                        `<b>${user}</b>: ${count} visit(s)`
                    ).join('<br>');

                    visitDetails = visitDetails || 'No visits recorded';

                    const popupContent = `
                <div style="color: #000000; font-size: 14px;">
                    <b>${name}</b><br>
                    ${visitDetails}
                </div>
            `;

                    layer.bindPopup(popupContent);
                }
            }).addTo(map);
        });

        $(document).ready(function() {
            // Ensure the map container exists
            if (!document.getElementById('planWisemap')) {
                console.error('Map container not found');
                return;
            }

            // Initialize the map
            const map = L.map('planWisemap').setView([23.6850, 90.3563], 7); // Center on Bangladesh

            // Add a tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Ensure upazilas data is available
            var upazilas = {!! json_encode($plan_upazilas) !!}; // Fix Laravel JSON encoding

            if (!upazilas || !upazilas.features) {
                console.error('GeoJSON data is missing or invalid');
                return;
            }

            // Add GeoJSON layer
            L.geoJSON(upazilas, {
                style: function(feature) {
                    let totalVisits = 0;
                    if (feature.properties && feature.properties.visit_counts) {
                        totalVisits = Object.values(feature.properties.visit_counts).reduce((sum,
                            count) => sum + count, 0);
                    }

                    return {
                        fillColor: totalVisits > 0 ? '#3333ff' :
                        '#FFFFFF', // Orange if visits exist, else white
                        fillOpacity: 0.5, // Slightly higher opacity for better visibility
                        color: '#000000', // Border color black
                        weight: 1
                    };
                },
                onEachFeature: function(feature, layer) {
                    const name = feature.properties?.NAME_4 || 'Unknown';
                    const visitCounts = feature.properties?.visit_counts || {};

                    let visitDetails = Object.entries(visitCounts).map(([user, count]) =>
                        `<b>${user}</b>: ${count} visit(s)`
                    ).join('<br>');

                    visitDetails = visitDetails || 'No visits recorded';

                    const popupContent = `
                <div style="color: #000000; font-size: 14px;">
                    <b>${name}</b><br>
                    ${visitDetails}
                </div>
            `;

                    layer.bindPopup(popupContent);
                }
            }).addTo(map);
        });
    </script>
@endsection
