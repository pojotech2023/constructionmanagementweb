@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column ">
    @php
        $site = $tickets->first()->site ?? null;
    @endphp
    <div class="page-header" style="margin-top:30px">
        <h3 class="fw-bold mb-3">Tickets for Site</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('sitemanagement.list') }}">Site</a></li>
            @if($site)
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a></li>
            @endif
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Tickets</a></li>
        </ul>
    </div>

    <div class="row justify-content-center" style="width: 100%;margin-left:10px">
       @forelse ($tickets as $ticket)
    <a href="{{ route('tickets.chat', $ticket->id) }}" class="text-decoration-none text-dark">
        <div class="col-md-12 mb-3">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <p><strong>Site:</strong> {{ $ticket->site->site_name ?? 'N/A' }}</p>
                    <p><strong>Tickets Description:</strong> {{ $ticket->ticket }}</p>
                    <p class="text-muted"><small>Submitted on: {{ $ticket->created_at->format('d M Y, h:i A') }}</small></p>
                </div>
            </div>
        </div>
    </a>
@empty
    <div class="col-12 text-center">
        <p>No tickets found for this site.</p>
    </div>
@endforelse

    </div>
</div>
@endsection
