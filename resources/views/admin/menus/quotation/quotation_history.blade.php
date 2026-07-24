@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Quotation History</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('quotation.form') }}">Generate Quotation</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">History</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Search by Customer Mobile Number</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('quotation.history') }}" method="GET" class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <input type="text" name="mobile_no" class="form-control" placeholder="Enter 10 digit mobile number"
                                        maxlength="10" minlength="10" pattern="\d{10}"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                        value="{{ $mobileNo }}" required>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search me-1"></i> Search
                                    </button>
                                </div>
                            </form>

                            @error('mobile_no')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            @if ($searched)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    Quotations for {{ $mobileNo }}
                                    <span class="badge badge-primary ms-2">{{ $quotations->count() }}</span>
                                </h4>
                            </div>
                            <div class="card-body">
                                @if ($quotations->isEmpty())
                                    <p class="text-center mt-3">No quotations found for this mobile number.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Name</th>
                                                    <th>Mobile Number</th>
                                                    <th>Subject</th>
                                                    <th>Date</th>
                                                    <th>Total Amount</th>
                                                    <th>Quotation PDF</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($quotations as $quotation)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $quotation->name }}</td>
                                                        <td>{{ $quotation->mobile_no }}</td>
                                                        <td>{{ $quotation->subject }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($quotation->date)->format('d-m-Y') }}</td>
                                                        <td>&#8377;{{ number_format((float) $quotation->total_amount, 2) }}</td>
                                                        <td>
                                                            <a href="{{ asset('storage/quotations/quotation_' . $quotation->id . '.pdf') }}"
                                                                target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                                <i class="fa fa-file-pdf me-1"></i> View PDF
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
