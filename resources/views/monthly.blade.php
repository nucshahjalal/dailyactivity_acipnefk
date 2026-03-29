@extends('admin.layouts.open_link_master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style type="text/css">
        #actualVisitmap,
        #planWisemap {
            height: 500px;
            width: 100%;
        }
    </style>
@endsection

@section('contentBody')
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
    <br />
    <div id="designationLegend" style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:10px;">
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {

            /* ---------------- DYNAMIC DESIGNATION COLORS FROM BACKEND ---------------- */
            const designationColors = {!! json_encode($designation_colors) !!};

            /* ---------------- NORMALIZE TEXT ---------------- */
            function normalizeText(text) {
                text = text.toLowerCase();
                text = text.replace(/[\.\,\-\_]/g, " "); // remove dots, commas, dashes, underscores
                text = text.replace(/\s+/g, " "); // multiple spaces → single
                return text.trim();
            }

            function getGroupKey(normalized) {
                if (normalized.includes("executive")) return "executive";
                if (normalized.includes("manager")) return "manager";
                if (normalized.includes("director")) return "director";
                return normalized; // other designation
            }

            /* ---------------- BUILD LEGEND HEADER ---------------- */
            function buildDesignationLegend() {
                const legendDiv = document.getElementById("designationLegend");
                legendDiv.innerHTML = "";

                const printedGroups = new Set();

                Object.entries(designationColors).forEach(([designation, color]) => {
                    const normalized = normalizeText(designation);
                    const group = getGroupKey(normalized);

                    if (printedGroups.has(group)) return; // skip duplicate
                    printedGroups.add(group);

                    let displayName = designation;
                    if (group === "executive") displayName = "Executive";
                    else if (group === "manager") displayName = "Manager";
                    else if (group === "director") displayName = "Director";

                    const item = `
                <div style="display:flex; align-items:center; gap:5px;">
                    <span style="
                        width:12px;
                        height:12px;
                        background:${color};
                        border:1px solid #000;
                        border-radius:50%;
                        display:inline-block;"></span>
                    <span style="font-size:14px; color:#000;">${displayName}</span>
                </div>
            `;
                    legendDiv.innerHTML += item;
                });
            }
            buildDesignationLegend();

            /* ---------------- HELPER FUNCTIONS ---------------- */
            function extractDesignation(text) {
                const match = text.match(/\(([^)]+)\)/);
                return match ? match[1].trim() : text;
            }

            function getDotColor(visitor) {
                const designationRaw = extractDesignation(visitor);
                const normalized = normalizeText(designationRaw);
                const group = getGroupKey(normalized);

                for (let key in designationColors) {
                    const norm = normalizeText(key);
                    if (getGroupKey(norm) === group) return designationColors[key];
                }

                return "#000"; // fallback
            }

            function offsetLatLng(center, index) {
                return [center.lat, center.lng + (0.03 * index)];
            }

            let actualMarkerGroup = L.layerGroup();
            let planMarkerGroup = L.layerGroup();
            let highlightedLayer = null;

            /* ---------------- POLYGON COLOR FUNCTIONS ---------------- */
            function getActualPolygonColor(feature) {
                let totalVisits = 0;
                if (feature.properties && feature.properties.visit_details) {
                    totalVisits = Object.values(feature.properties.visit_details)
                        .reduce((sum, count) => sum + count, 0);
                }
                return totalVisits > 0 ? "#ff5c33" : "#FFFFFF";
            }

            function getPlanPolygonColor(feature) {
                let totalVisits = 0;
                if (feature.properties && feature.properties.visit_details) {
                    totalVisits = Object.values(feature.properties.visit_details)
                        .reduce((sum, count) => sum + count, 0);
                }
                return totalVisits > 0 ? "#3333ff" : "#FFFFFF";
            }

            /* ---------------- ADD DOTS ---------------- */
            function addDotsToFeature(feature, layer, map, markerGroup) {
                if (!feature.geometry) return;

                const visitDetails = feature.properties?.visit_details || {};
                const center = layer.getBounds().getCenter();
                let index = 0;

                for (const [visitor, count] of Object.entries(visitDetails)) {
                    const color = getDotColor(visitor);
                    const newPoint = offsetLatLng(center, index);

                    let dot = L.circleMarker(newPoint, {
                        radius: 6,
                        fillColor: color,
                        color: color,
                        fillOpacity: 1,
                        weight: 2
                    }).bindTooltip(`${visitor}: ${count} visit(s)`);

                    dot.on("click", function(e) {
                        markerGroup.eachLayer(l => {
                            if (l !== dot) map.removeLayer(l);
                        });

                        if (highlightedLayer) {
                            highlightedLayer.setStyle({
                                fillColor: "#FFFFFF",
                                fillOpacity: 0.5
                            });
                        }

                        highlightedLayer = layer;
                        e.originalEvent.stopPropagation();
                    });

                    dot.addTo(markerGroup);
                    index++;
                }
            }

            /* ---------------- RESET MAP ---------------- */
            function resetMap(map, markerGroup) {
                markerGroup.eachLayer(l => {
                    if (!map.hasLayer(l)) map.addLayer(l);
                });

                if (highlightedLayer) {
                    highlightedLayer.setStyle({
                        fillColor: "#FFFFFF",
                        fillOpacity: 0.5
                    });
                    highlightedLayer = null;
                }
            }

            /* ---------------- ACTUAL VISIT MAP ---------------- */
            if (document.getElementById('actualVisitmap')) {
                const actualMap = L.map('actualVisitmap').setView([23.6850, 90.3563], 7);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(actualMap);

                const actualUpazilas = {!! json_encode($actual_upazilas) !!};

                if (actualUpazilas?.features) {
                    L.geoJSON(actualUpazilas, {
                        style: function(feature) {
                            return {
                                fillColor: getActualPolygonColor(feature),
                                fillOpacity: 0.5,
                                color: '#000',
                                weight: 1
                            };
                        },
                        onEachFeature: function(feature, layer) {
                            const name = feature.properties?.NAME_4 || 'Unknown';
                            const visitDetails = feature.properties?.visit_details || {};

                            let popupText = "";
                            for (const [visitor, count] of Object.entries(visitDetails)) {
                                popupText += `<b>${visitor}</b>: ${count} visit(s)<br>`;
                            }

                            layer.bindPopup(`
                        <div style="color:#000; font-size:14px;">
                            <b style="color:#ff5c33;">${name}</b><br><hr>${popupText || "No visits"}
                        </div>
                    `);

                            addDotsToFeature(feature, layer, actualMap, actualMarkerGroup);
                        }
                    }).addTo(actualMap);

                    actualMarkerGroup.addTo(actualMap);

                    actualMap.on("click", function() {
                        resetMap(actualMap, actualMarkerGroup);
                    });
                }
            }

            /* ---------------- PLAN WISE MAP ---------------- */
            if (document.getElementById('planWisemap')) {
                const planMap = L.map('planWisemap').setView([23.6850, 90.3563], 7);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(planMap);

                const planUpazilas = {!! json_encode($plan_upazilas) !!};

                if (planUpazilas?.features) {
                    L.geoJSON(planUpazilas, {
                        style: function(feature) {
                            return {
                                fillColor: getPlanPolygonColor(feature),
                                fillOpacity: 0.5,
                                color: '#000',
                                weight: 1
                            };
                        },
                        onEachFeature: function(feature, layer) {
                            const name = feature.properties?.NAME_4 || 'Unknown';
                            const visitDetails = feature.properties?.visit_details || {};

                            let popupText = "";
                            for (const [visitor, count] of Object.entries(visitDetails)) {
                                popupText += `<b>${visitor}</b>: ${count} visit(s)<br>`;
                            }

                            layer.bindPopup(`
                        <div style="color:#000; font-size:14px;">
                            <b style="color:#3333ff;">${name}</b><br><hr>${popupText || "No visits"}
                        </div>
                    `);

                            addDotsToFeature(feature, layer, planMap, planMarkerGroup);
                        }
                    }).addTo(planMap);

                    planMarkerGroup.addTo(planMap);

                    planMap.on("click", function() {
                        resetMap(planMap, planMarkerGroup);
                    });
                }
            }

        });
    </script>
@endsection
