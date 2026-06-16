@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">Vendor Dashboard</h3>
                </div>
            </div>
            <div class="row">
                @foreach ($vendors as $vendor)
                    <div class="col-sm-6 col-md-4">
                        <a href="{{ route('vendor.payDetailForm', ['vendorId' => $vendor->id]) }}" class="text-decoration-none">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fa fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <h4 class="card-title">{{ $vendor->name }}</h4>
                                                @php
                                                $paidAmount = $vendor->vendor_payment_sum_payment ?? 0;
                                                $pendingAmount = $vendor->vendorPayDetail->balance_amount ?? 0;
                                                $totalAmount = $paidAmount + $pendingAmount;
                                            @endphp
                                            
                                            <p class="card-category">
                                                <strong>Total Amount:</strong> ₹<strong class="text-primary">{{ number_format($totalAmount, 2) }}</strong>
                                            </p>
                                            <p class="card-category">
                                                <strong>Paid Amount:</strong> ₹<strong class="text-primary">{{ number_format($paidAmount, 2) }}</strong>
                                            </p>
                                            <p class="card-category">
                                                <strong>Pending Amount:</strong> ₹<strong class="text-primary">{{ number_format($pendingAmount, 2) }}</strong>
                                            </p>
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endsection
