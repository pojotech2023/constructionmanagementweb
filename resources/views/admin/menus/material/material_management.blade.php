@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3 me-3">Material Details</h3>
                    <ul class="breadcrumbs mb-0">
                        <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sitemanagement.list') }}">Site</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">Material Details</a>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <a href="{{ route('material.requestList', $site->id) }}" class="btn btn-warning">
                        <i class="fa fa-list-alt me-1"></i> View Request
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMaterialTypeModal">
                        <i class="fa fa-plus me-1"></i> Add Material
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Blade alert for success -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    {{ session()->forget('success') }} {{-- Clear session --}}
                @endif

                @unless(in_array('bricks', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'bricks']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="bricks" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/bricks.webp') }}" class="w-75">
                            </div>
                            <div class="text-muted mb-3">Bricks</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['bricks']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['bricks']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('sand', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'sand']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="sand" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1">
                                <img src="{{ asset('images/sri/sand.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Sand</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['sand']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['sand']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('cement', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'cement']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="cement" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/cement1.webp') }}" class="w-75">
                            </div>
                            <div class="text-muted">Cement</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['cement']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['cement']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('electricalwire', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'electricalwire']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="electricalwire" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/electricwire.webp') }}" class="w-75">
                            </div>
                            <div class="text-muted">Electrical Wires</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['electricalwire']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['electricalwire']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('plumber', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'plumber']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="plumber" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/plumber.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Plumber</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['plumber']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['plumber']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('tea', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'tea']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="tea" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/tea.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Tea</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['tea']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['tea']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
            </div>

            <div class="row mt-5">
                @unless(in_array('watercan', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'watercan']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="watercan" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/watercan.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Watercan</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['watercan']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['watercan']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('lorrywater', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'lorrywater']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="lorrywater" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/waterlorry.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Lorry Water</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['lorrywater']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['lorrywater']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('tiles', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'tiles']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="tiles" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/tiles.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Tiles</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['tiles']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['tiles']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('granite', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'granite']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="granite" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/granite.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Granite</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['granite']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['granite']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('jally', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'jally']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="jally" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/jally.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Jally</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['jally']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['jally']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('welding', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'welding']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="welding" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/welding.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Welding</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['welding']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['welding']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
            </div>

            <div class="row mt-5">
                @unless(in_array('lift', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'lift']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="lift" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/lift.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Lift</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['lift']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['lift']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('rcconcrete', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'rcconcrete']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="rcconcrete" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/concrete.webp') }}" class="w-75">
                            </div>
                            <div class="text-muted">RC Concrete</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['rcconcrete']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['rcconcrete']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('transport', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'transport']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="transport" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/transport.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Transport</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['transport']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['transport']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('interior', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'interior']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="interior" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/interior.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Interior</div>
                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['interior']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['interior']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                @unless(in_array('painting', $sharedHiddenMaterialTypes))
                <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'painting']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="painting" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/painting.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Painting</div>

                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['painting']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['painting']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
                 @unless(in_array('steel', $sharedHiddenMaterialTypes))
                 <div class="col-6 col-sm-4 col-lg-2">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'steel']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="steel" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/steel.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Steel</div>

                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['steel']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['steel']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
           

             @unless(in_array('aggregate', $sharedHiddenMaterialTypes))
             <div class="col-6 col-sm-4 col-lg-2 mt-4">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'aggregate']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="aggregate" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/aggregate.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">AGGREGATE</div>

                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['aggregate']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['aggregate']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                
            </div>
@endunless
			@unless(in_array('rmc', $sharedHiddenMaterialTypes))
			<div class="col-6 col-sm-4 col-lg-2 mt-4">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'rmc']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="rmc" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/rmc.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">RMC</div>

                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['rmc']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['rmc']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless
           
            @unless(in_array('gravel', $sharedHiddenMaterialTypes))
            <div class="col-6 col-sm-4 col-lg-2 mt-4">
                    <div class="card h-100 w-100 site-card position-relative"
                        data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => 'gravel']) }}"
                        onclick="redirectToDetails(event, this)">
                        <button type="button" class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 fixedRemoveBtn" style="z-index: 2;" data-slug="gravel" data-module="material" title="Remove from grid"><i class="fa fa-times"></i></button>
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                            <div class="h1 m-0">
                                <img src="{{ asset('images/sri/jally.jpg') }}" class="w-75">
                            </div>
                            <div class="text-muted">Gravel</div>

                            <div class="text-success fw-bold">
                                Qnty - {{ $materials['gravel']['units'] ?? 0 }}
                            </div>
                            <div class="text-success fw-bold">
                                Values - ₹{{ $materials['gravel']['values'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
@endunless

                @foreach ($sharedMaterialTypes as $materialType)
                    <div class="col-6 col-sm-4 col-lg-2 mt-4">
                        <div class="card h-100 w-100 site-card position-relative"
                            data-route="{{ route('material', ['siteId' => $site->id, 'materialType' => $materialType->slug]) }}"
                            onclick="redirectToDetails(event, this)">
                            <button type="button"
                                class="btn btn-link btn-danger btn-sm position-absolute top-0 end-0 deleteMaterialTypeBtn"
                                style="z-index: 2;" data-id="{{ $materialType->id }}"
                                title="Remove material type">
                                <i class="fa fa-times"></i>
                            </button>
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div class="h1 m-0">
                                    <img src="{{ asset('storage/' . $materialType->image) }}" class="w-75">
                                </div>
                                <div class="text-muted mb-3">{{ $materialType->name }}</div>
                                <div class="text-success fw-bold">
                                    Qnty - {{ $materials[$materialType->slug]['units'] ?? 0 }}
                                </div>
                                <div class="text-success fw-bold">
                                    Values - ₹{{ $materials[$materialType->slug]['values'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
</div>

            <div class="row justify-content-center mt-4 g-3">
                <div class="col-12 col-md-4 col-lg-3" id="addButton" data-bs-toggle="modal"
                    data-bs-target="#addModal" data-site-id="{{ $site->id }}" style="cursor: pointer;">
                    <div class="card border border-primary shadow" style="min-height: 140px;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-6 text-center">
                                    <img src="{{ asset('images/sri/othershand.webp') }}"
                                        style="width: 200px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="col-6 text-start">
                                    <h3 class="fw-bold mb-0">OTHERS</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Material Type Modal -->
    <div class="modal fade" id="addMaterialTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('materialType.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Material Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="materialTypeName">Material Name</label>
                            <input type="text" name="name" id="materialTypeName" class="form-control"
                                placeholder="e.g. Waterproofing" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="materialTypeImage">Image</label>
                            <input type="file" name="image" id="materialTypeImage" class="form-control"
                                accept=".jpg,.jpeg,.png,.webp" required>
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Fixed Material Card Confirm Modal -->
    <div class="modal fade" id="removeFixedMaterialModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Remove</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this card from the grid? This only hides the card; existing records remain and it can be brought back by an admin later.
                </div>
                <div class="modal-footer">
                    <form id="removeFixedMaterialForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Remove</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Material Type Confirm Modal -->
    <div class="modal fade" id="deleteMaterialTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this material type? Existing records under it will remain.
                </div>
                <div class="modal-footer">
                    <form id="deleteMaterialTypeForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Remove</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold" id="modalTitle">Others</span>
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('site.utilities', $site->id) }}" class="btn btn-info btn-sm">Others History</a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body">
                    <form id="utilityForm" action="{{ route('utilities.add') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="site_id" name="site_id">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <label for="amount">Amount</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="amount" name="amount" type="number" class="form-control no-arrow" min="0" step="0.01"
                                    placeholder="Enter Amount"
                                    oninput="document.getElementById('utility_amount_words').innerText = numberToWordsIndian(this.value);" />
                                <small id="utility_amount_words" class="form-text text-muted"></small>
                            </div>
                            @error('amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="date">Date</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="date" name="date" type="date" class="form-control" value="{{ old('date') }}" />
                            </div>
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="image">Image</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="image" name="image" type="file" class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp" />
                            </div>
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="remarks">Remarks</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea id="remarks" name="remarks" class="form-control" rows="4"></textarea>
                            </div>
                            @error('remarks')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary" id="saveButton">Add</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        //redirect to leads detail page
        function redirectToDetails(event, card) {
            event.stopPropagation(); // Prevents unintended clicks
            // Remove styles from all cards
            document.querySelectorAll('.site-card').forEach(item => {
                item.classList.remove('selected-card');
            });
            // Add active class to clicked card
            card.classList.add('selected-card');
            // Redirect after small delay
            let route = card.getAttribute('data-route');
            if (route) {
                window.location.href = route;
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            const addButton = document.getElementById('addButton');
            const siteIdInput = document.getElementById('site_id');
            const utilityForm = document.getElementById('utilityForm');
            const spinner = document.getElementById('loadingSpinner');
            const saveButton = document.getElementById('saveButton');
            const closeButton = document.querySelector('#addModal .btn-danger');

            // Set site_id when modal is opened
            if (addButton && siteIdInput) {
                addButton.addEventListener('click', function() {
                    const siteId = this.getAttribute('data-site-id');
                    siteIdInput.value = siteId;
                });
            }

            // Show spinner + disable buttons on form submit
            if (utilityForm) {
                utilityForm.addEventListener('submit', function(e) {
                    // Disable buttons
                    saveButton.disabled = true;
                    closeButton.disabled = true;

                    // Show spinner
                    if (spinner) {
                        spinner.classList.remove('d-none');
                    }
                });
            }

            // Auto-hide success alert after 3 seconds
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.add("fade");
                    successAlert.classList.remove("show");
                }, 500);
            }

            // Delete a dynamically-added material type
            document.querySelectorAll('.deleteMaterialTypeBtn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    const action = "{{ route('materialType.delete', ':id') }}".replace(':id', id);
                    document.getElementById('deleteMaterialTypeForm').setAttribute('action', action);
                    new bootstrap.Modal(document.getElementById('deleteMaterialTypeModal')).show();
                });
            });

            // Remove a fixed (built-in) material card from the grid
            document.querySelectorAll('.fixedRemoveBtn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const slug = this.getAttribute('data-slug');
                    const action = "{{ route('materialType.hideFixed', ':slug') }}".replace(':slug', slug);
                    document.getElementById('removeFixedMaterialForm').setAttribute('action', action);
                    new bootstrap.Modal(document.getElementById('removeFixedMaterialModal')).show();
                });
            });
        });
    </script>
    <style>
        .site-card {
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border: 2px solid #dee2e6;
            background: #fff;
            border-radius: 8px;
        }

        /* Hover Effect */
        .site-card:hover {
            transform: scale(1.02);
            border-color: #007bff;
            box-shadow: 0px 5px 15px rgba(0, 123, 255, 0.3);
        }

        .site-card .card-body {
            justify-content: center !important;
            gap: 6px;
        }

        .site-card .card-body > div:first-child {
            margin-bottom: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 90px;
        }

        .site-card .card-body img {
            width: auto !important;
            height: 80px !important;
            max-width: 100%;
            object-fit: contain;
        }

        .site-card .card-body .text-muted {
            margin-bottom: 0 !important;
        }
    </style>
@endsection
