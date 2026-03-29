@extends('admin.layouts.open_link_master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #combinedMap {
            height: 600px;
            width: 100%;
        }
    </style>
@endsection

@section('contentBody')
    <div class="row mt-3">
        <div class="col-12">
            <h5>Combined Upazilla Map (Proposed + Existing)</h5>
            <div id="combinedMap"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {

            // Initialize ONE map
            const map = L.map('combinedMap').setView([23.6850, 90.3563], 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Get both upazila lists (already merged with is_proposed & is_existing)
            var proposed = {!! json_encode($proposed_upazilla) !!};
            var existing = {!! json_encode($existing_upazilla) !!};

            // We use only one dataset (proposed) because both have same geojson.
            // But to combine flags, we merge is_existing flag into the first dataset.
            var mergedGeojson = proposed;

            mergedGeojson.features.forEach(function(feature, index) {
                // If the SAME upazila exists in existing list => mark is_existing = 1
                let proposedName = feature.properties.NAME_4.toLowerCase();

                let exists = existing.features.some(f =>
                    f.properties.NAME_4.toLowerCase() === proposedName &&
                    f.properties.is_existing === 1
                );

                feature.properties.is_existing = exists ? 1 : 0;
            });

            // Add merged geojson to the map
            L.geoJSON(mergedGeojson, {

                style: function(feature) {

                    let isProposed = feature.properties.is_proposed;
                    let isExisting = feature.properties.is_existing;

                    let color = "#FFFFFF"; // default

                    if (isProposed === 1) color = "#3333ff"; // BLUE
                    if (isExisting === 1) color = "#87fc6d"; // GREEN

                    return {
                        fillColor: color,
                        fillOpacity: 0.7,
                        color: '#000000',
                        weight: 1
                    };
                },

                onEachFeature: function(feature, layer) {
                    const name = feature.properties.NAME_4;

                    let typeLabel = "Not Listed";
                    if (feature.properties.is_proposed === 1) typeLabel = "Proposed Upazilla";
                    if (feature.properties.is_existing === 1) typeLabel = "Existing Upazilla";

                    layer.bindPopup(`<b>${name}</b><br>${typeLabel}`);
                }

            }).addTo(map);
        });
    </script>
@endsection
