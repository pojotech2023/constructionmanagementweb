@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Supervisor Locations</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('supervisor.list') }}">Supervisor</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Locations</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">
                                <i class="fa fa-map-marker-alt me-2 text-danger"></i>
                                All Supervisor Locations
                            </h4>
                            <a href="{{ route('supervisor.list') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left me-1"></i> Back
                            </a>
                        </div>

                        <div class="card-body p-0">
                            <div id="allSupervisorsMap" style="height: 75vh; width: 100%;"></div>
                        </div>

                        @php
                            $withoutLocation = $supervisors->filter(fn($s) => is_null($s->latitude) || is_null($s->longitude));
                        @endphp

                        @if ($withoutLocation->isNotEmpty())
                            <div class="card-footer">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle me-1"></i>
                                    No location reported yet for:
                                    {{ $withoutLocation->pluck('name')->implode(', ') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .supervisor-name-tooltip {
            font-weight: 600;
            font-size: 13px;
            padding: 3px 8px;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const supervisors = @json($supervisorsForMap);

            const withLocation = supervisors.filter(s => s.lat !== null && s.lng !== null);

            const map = L.map('allSupervisorsMap');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            if (withLocation.length === 0) {
                // Default view (India) when nobody has reported a location yet.
                map.setView([20.5937, 78.9629], 5);
            } else {
                const markers = [];
                withLocation.forEach(function(s) {
                    const marker = L.marker([s.lat, s.lng]).addTo(map);
                    const googleMapsUrl = 'https://www.google.com/maps/search/' +
                        encodeURIComponent(s.name).replace(/%20/g, '+') + '/@' + s.lat + ',' + s.lng + ',15z';
                    marker.bindTooltip(s.name, {
                        permanent: true,
                        direction: 'top',
                        offset: [0, -10],
                        className: 'supervisor-name-tooltip'
                    });
                    marker.bindPopup(
                        '<strong>' + s.name + '</strong><br>' +
                        (s.mobile_no ? s.mobile_no + '<br>' : '') +
                        (s.updated ? '<small class="text-muted">Last updated ' + s.updated + '</small><br>' : '') +
                        '<a href="' + googleMapsUrl + '" target="_blank" rel="noopener">Open in Google Maps</a>'
                    );
                    markers.push(marker);
                });

                if (markers.length === 1) {
                    map.setView(markers[0].getLatLng(), 15);
                } else {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.2));
                }
            }

            setTimeout(() => map.invalidateSize(), 200);
        });
    </script>
@endsection
