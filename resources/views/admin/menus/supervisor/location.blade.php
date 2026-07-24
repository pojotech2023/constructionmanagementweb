@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Supervisor Location</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('supervisor.list') }}">Supervisor</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">{{ $supervisor->name }}</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="card-title mb-0">
                                    <i class="fa fa-map-marker-alt me-2 text-danger"></i>
                                    <span class="text-primary">{{ $supervisor->name }}</span>
                                </h4>
                                <small class="text-muted">
                                    {{ $supervisor->mobile_no }}
                                    @if ($supervisor->location_updated_at)
                                        &middot; Last updated {{ $supervisor->location_updated_at->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if ($supervisor->latitude && $supervisor->longitude)
                                    <a href="#" id="openInGoogleMaps" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-external-link-alt me-1"></i> Open in Google Maps
                                    </a>
                                @endif
                                <a href="{{ route('supervisor.list') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if ($supervisor->latitude && $supervisor->longitude)
                                <div id="supervisorMap" style="height: 80vh; width: 100%;"></div>
                            @else
                                <div class="alert alert-warning m-3 mb-0">
                                    No location has been reported for {{ $supervisor->name }} yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($supervisor->latitude && $supervisor->longitude)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const lat = {{ $supervisor->latitude }};
                const lng = {{ $supervisor->longitude }};
                const name = @json($supervisor->name);

                const googleMapsUrl = 'https://www.google.com/maps/search/' +
                    encodeURIComponent(name).replace(/%20/g, '+') + '/@' + lat + ',' + lng + ',15z';
                document.getElementById('openInGoogleMaps').href = googleMapsUrl;

                const map = L.map('supervisorMap').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map).bindPopup(name).openPopup();

                setTimeout(() => map.invalidateSize(), 200);
            });
        </script>
    @endif
@endsection
