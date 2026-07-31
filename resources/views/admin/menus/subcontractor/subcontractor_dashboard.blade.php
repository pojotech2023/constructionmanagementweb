@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">SubContractor Dashboard</h3>
                </div>
            </div>
            <div class="row">
                @foreach ($subcontractors as $subcontractor)
                    <div class="col-sm-6 col-md-4">
                        <a href="{{ route('subcontractor.payDetailForm', ['subcontractorId' => $subcontractor->id]) }}" class="text-decoration-none">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fa fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <h4 class="card-title subcontractor-name" title="{{ $subcontractor->name }}">{{ $subcontractor->name }}</h4>
                                                @php
                                                $paidAmount = $subcontractor->subcontractor_payment_sum_payment ?? 0;
                                                $totalAmount = $subcontractor->subcontractor_service_sum_amount ?? 0;
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

            .subcontractor-name {
                font-size: 1.05rem;
                line-height: 1.3;
                margin-bottom: 10px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        </style>
    @endsection
