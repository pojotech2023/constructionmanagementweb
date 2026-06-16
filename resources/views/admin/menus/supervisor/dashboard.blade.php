@extends('layouts.app') {{-- or your layout name --}}
@section('content')
    <h2>Welcome Supervisor</h2>

    @if($sites->count())
        <ul>
            @foreach($sites as $site)
                <li>{{ $site->name ?? 'Unnamed Site' }} - Status: {{ $site->status }}</li>
            @endforeach
        </ul>
    @else
        <p>No sites available.</p>
    @endif
@endsection
