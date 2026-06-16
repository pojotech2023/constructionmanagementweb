@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column ">
    <h3 class="fw-bold mb-4 text-center"style="margin-top:30px">Tickets for Site</h3>

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
