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
                                                $totalAmount = $vendor->material_orders_sum_price ?? 0;
                                                $pendingAmount = $totalAmount - $paidAmount;
                                            @endphp
                                            
                                            <div class="amount-lines">
                                                <div class="amount-line">
                                                    <span>Total Amount</span>
                                                    <span>:</span>
                                                    <strong class="text-primary">₹{{ number_format($totalAmount, 2) }}</strong>
                                                </div>
                                                <div class="amount-line">
                                                    <span>Paid Amount</span>
                                                    <span>:</span>
                                                    <strong class="text-primary">₹{{ number_format($paidAmount, 2) }}</strong>
                                                </div>
                                                <div class="amount-line">
                                                    <span>Balance Amount</span>
                                                    <span>:</span>
                                                    <strong class="text-primary">₹{{ number_format($pendingAmount, 2) }}</strong>
                                                </div>
                                            </div>
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
        <style>
            .amount-lines {
                display: grid;
                row-gap: 4px;
            }

            .amount-line {
                display: grid;
                grid-template-columns: 118px 12px auto;
                align-items: baseline;
                color: #8a8f99;
                font-size: 14px;
                line-height: 1.35;
            }
        </style>
    @endsection
