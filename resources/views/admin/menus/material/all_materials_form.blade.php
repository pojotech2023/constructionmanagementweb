@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Page Title & Navigation -->
        <div class="row align-items-center mb-3">
            <div class="col-lg-11 ms-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">All Materials Management</h3>
                    <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center list-unstyled gap-2 text-muted" style="font-size: 13px;">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="text-muted"><i class="icon-home"></i></a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li><a href="{{ route('sitemanagement.list') }}" class="text-muted">Site</a></li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li><a href="{{ route('site.detail', $siteId) }}" class="text-muted">{{ $site->site_name }}</a></li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li><a href="{{ route('material.detail', $siteId) }}" class="text-muted">Material Details</a></li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="text-primary fw-bold">All Materials</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('material', ['siteId' => $siteId, 'materialType' => 'all']) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-list me-1"></i> View All Orders
                    </a>
                    <a href="{{ route('material.detail', $siteId) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Back to Materials
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Form Card matching add_order & add_request layout -->
        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-lg p-4 ms-4">

                    <form id="orderForm" action="{{ route('add.order') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="site_id" value="{{ $siteId }}">
                            <input type="hidden" name="vendor_id" id="order_vendor_id">

                            <!-- Vendor Name -->
                            <div class="row align-items-center mt-3">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_vendor_name" class="fw-bold">Vendor Name</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group position-relative mb-0">
                                        <input type="text" id="order_vendor_name" name="vendor_name" class="form-control"
                                            placeholder="Type Vendor Name..." autocomplete="off" required>
                                        <div id="order_vendor_suggestions" class="list-group position-absolute w-100 shadow"
                                            style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor Mobile -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_vendor_mobile" class="fw-bold">Vendor Mobile No</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="text" id="order_vendor_mobile" name="vendor_mobile" class="form-control"
                                            placeholder="Auto-filled on selecting vendor" maxlength="10" minlength="10" pattern="\d{10}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" readonly required>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor Address -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_vendor_address" class="fw-bold">Vendor Address</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <textarea id="order_vendor_address" name="vendor_address" class="form-control" rows="2"
                                            placeholder="Auto-filled on selecting vendor" readonly required></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor GST -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_vendor_gstin" class="fw-bold">Vendor GST</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input id="order_vendor_gstin" name="vendor_gstin" type="text" class="form-control"
                                            placeholder="Auto-filled on selecting vendor" readonly />
                                    </div>
                                </div>
                            </div>

                            <!-- Material Multi-Select Dropdown -->
                            <div class="row align-items-center mt-4" id="order_mattype_row">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_mattype_btn" class="fw-bold">Material</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group position-relative mb-0">
                                        <input type="hidden" name="material_type" id="order_material_type" value="">
                                        <div class="custom-chk-dropdown-wrapper" id="order_mattype_wrapper">
                                            <button type="button" class="form-control custom-chk-dropdown-btn text-start d-flex justify-content-between align-items-center bg-white" id="order_mattype_btn">
                                                <span id="order_mattype_btn_text" class="text-truncate text-muted">Select Material</span>
                                                <i class="fas fa-chevron-down ms-2 dropdown-arrow" style="font-size: 11px; color: #495057;"></i>
                                            </button>
                                            <div class="custom-chk-dropdown-menu" id="order_mattype_menu" style="display: none;">
                                                <div class="custom-chk-list" id="order_mattype_list">
                                                    <!-- Injected dynamically via JS: Bricks / Blocks, Cement, Steel, etc. -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_date" class="fw-bold">Date</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="date" class="form-control" name="date" id="order_date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Subtype Dropdowns Container (One per selected material type) -->
                            <div id="order_subtypes_container">
                                <!-- Injected dynamically for each checked material type -->
                            </div>


                            <!-- Quantity -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_quantity" class="fw-bold">Quantity</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="number" step="any" class="form-control" name="quantity" id="order_quantity" placeholder="Enter quantity" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Supervisor Name -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_supervisor_name" class="fw-bold">Supervisor Name</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="supervisor_name" id="order_supervisor_name"
                                            placeholder="Enter supervisor name" value="{{ old('supervisor_name', $supervisor->name ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Supervisor Phone -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_supervisor_phone" class="fw-bold">Supervisor Phone No</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="supervisor_phone" id="order_supervisor_phone"
                                            placeholder="Enter phone number" maxlength="10" minlength="10" pattern="\d{10}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                            value="{{ old('supervisor_phone', $supervisor->mobile_no ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Total Price -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_price" class="fw-bold">Total Price</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input id="order_price" name="price" type="number" class="form-control no-arrow"
                                            min="0" step="0.01" placeholder="Enter total price" required
                                            oninput="document.getElementById('order_price_words').innerText = numberToWordsIndian(this.value);" />
                                        <small id="order_price_words" class="form-text text-muted"></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice -->
                            <div class="row align-items-center mt-4">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="order_attachment" class="fw-bold">Invoice</label>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <input type="file" name="attachment" id="order_attachment" class="form-control" accept="image/*,.pdf">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row justify-content-center mt-5">
                                <div class="col-lg-4">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-primary w-100">Inward Order</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div id="statusModal" class="custom-status-modal d-none">
        <div class="custom-status-backdrop"></div>
        <div class="custom-status-dialog">
            <div class="custom-status-icon">✓</div>
            <h4 id="statusModalTitle" class="custom-status-title">Success</h4>
            <p id="statusModalMessage" class="custom-status-message"></p>
            <button type="button" id="statusModalButton" class="btn btn-primary px-4">OK</button>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        const sharedUnits = @json($sharedUnits ?? []);

        function showStatusModal(message, options = {}) {
            const modal = document.getElementById('statusModal');
            const title = document.getElementById('statusModalTitle');
            const messageBox = document.getElementById('statusModalMessage');
            const button = document.getElementById('statusModalButton');
            const icon = modal.querySelector('.custom-status-icon');

            title.textContent = options.title || 'Success';
            messageBox.textContent = message;
            button.textContent = options.buttonText || 'OK';

            modal.classList.remove('d-none', 'is-error');

            if (options.type === 'error') {
                modal.classList.add('is-error');
                icon.textContent = '!';
            } else {
                modal.classList.remove('is-error');
                icon.textContent = '✓';
            }

            button.onclick = function() {
                modal.classList.add('d-none');
                if (typeof options.onConfirm === 'function') {
                    options.onConfirm();
                }
            };
        }

        function numberToWordsIndian(num) {
            num = parseFloat(num);
            if (isNaN(num) || num <= 0) return '';

            const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
            const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function inWords(n) {
                let str = '';
                if (n > 19) {
                    str += b[Math.floor(n / 10)] + ' ' + a[n % 10];
                } else {
                    str += a[n];
                }
                return str;
            }

            let integerPart = Math.floor(num);
            let decimalPart = Math.round((num - integerPart) * 100);

            let crore = Math.floor(integerPart / 10000000);
            integerPart %= 10000000;
            let lakh = Math.floor(integerPart / 100000);
            integerPart %= 100000;
            let thousand = Math.floor(integerPart / 1000);
            integerPart %= 1000;
            let hundred = Math.floor(integerPart / 100);
            integerPart %= 100;
            let remaining = integerPart;

            let result = '';
            if (crore > 0) result += inWords(crore) + 'Crore ';
            if (lakh > 0) result += inWords(lakh) + 'Lakh ';
            if (thousand > 0) result += inWords(thousand) + 'Thousand ';
            if (hundred > 0) result += inWords(hundred) + 'Hundred ';
            if (remaining > 0) result += inWords(remaining);

            result = result.trim() + ' Rupees';
            if (decimalPart > 0) {
                result += ' and ' + inWords(decimalPart) + 'Paise';
            }
            return result + ' Only';
        }

        function buildUnitOptions(selectedVal = '') {
            let opts = '<option value="">Select Unit</option>';
            sharedUnits.forEach(function(u) {
                opts += `<option value="${u}" ${u === selectedVal ? 'selected' : ''}>${u}</option>`;
            });
            return opts;
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        const materialDefinitions = {
            'bricks': {
                title: 'Bricks / Blocks',
                label: 'Brick / Block Type',
                placeholder: 'Select Brick / Block Type',
                defaultUnit: 'Nos',
                items: [
                    'Red Clay Brick',
                    'Fly Ash Bricks',
                    'AAC Blocks',
                    'Solid Concrete Blocks',
                    'Hollow Concrete Blocks',
                    'Wire Cut Bricks',
                    'Clay Paver Bricks',
                    'Stone'
                ]
            },
            'cement': {
                title: 'Cement',
                label: 'Cement Type',
                placeholder: 'Select Cement Type',
                defaultUnit: 'Bags',
                items: [
                    'OPC 53 Grade',
                    'OPC 43 Grade',
                    'PPC (Portland Pozzolana)',
                    'White Cement',
                    'Waterproof Cement',
                    'Rapid Hardening Cement',
                    'Slag Cement'
                ]
            },
            'steel': {
                title: 'Steel',
                label: 'Steel Type',
                placeholder: 'Select Steel Type',
                defaultUnit: 'Ton',
                items: [
                    'TMT 8mm',
                    'TMT 10mm',
                    'TMT 12mm',
                    'TMT 16mm',
                    'TMT 20mm',
                    'TMT 25mm',
                    'Binding Wire',
                    'MS Flat / Angle',
                    'Structural Steel'
                ]
            },
            'sand': {
                title: 'Sand',
                label: 'Sand Type',
                placeholder: 'Select Sand Type',
                defaultUnit: 'Units',
                items: [
                    'M-Sand (Manufactured Sand)',
                    'P-Sand (Plastering Sand)',
                    'River Sand',
                    'Pit Sand / Filling Sand'
                ]
            },
            'aggregate': {
                title: 'Aggregate',
                label: 'Aggregate Type',
                placeholder: 'Select Aggregate Type',
                defaultUnit: 'Units',
                items: [
                    '10mm Aggregate',
                    '20mm Aggregate',
                    '40mm Aggregate',
                    'GSB (Granular Sub Base)',
                    'Quarry Dust',
                    'Stone Boulder'
                ]
            },
            'tiles': {
                title: 'Tiles',
                label: 'Tiles Type',
                placeholder: 'Select Tiles Type',
                defaultUnit: 'Sq.Ft',
                items: [
                    'Vitrified Floor Tiles (2x2)',
                    'Vitrified Floor Tiles (4x2)',
                    'Ceramic Wall Tiles',
                    'Anti-Skid Bathroom Tiles',
                    'Parking / Exterior Tiles',
                    'Kitchen Dado Tiles',
                    'Step & Riser Tiles'
                ]
            },
            'granite': {
                title: 'Granite / Marble',
                label: 'Granite Type',
                placeholder: 'Select Granite Type',
                defaultUnit: 'Sq.Ft',
                items: [
                    'Black Galaxy Granite',
                    'Tan Brown Granite',
                    'Jet Black Granite',
                    'Kashmir White Granite',
                    'Steel Grey Granite',
                    'Ruby Red Granite',
                    'Indian Marble Slabs'
                ]
            },
            'rmc': {
                title: 'RMC / Concrete',
                label: 'RMC Grade',
                placeholder: 'Select RMC Grade',
                defaultUnit: 'Cum',
                items: [
                    'M15 Concrete',
                    'M20 Concrete',
                    'M25 Concrete',
                    'M30 Concrete',
                    'M35 Concrete',
                    'M40 Concrete'
                ]
            },
            'plumber': {
                title: 'Plumbing & Pipes',
                label: 'Plumbing Item',
                placeholder: 'Select Plumbing Item',
                defaultUnit: 'Nos',
                items: [
                    'CPVC Pipes & Fittings',
                    'UPVC Pipes & Fittings',
                    'PVC Drainage Pipes',
                    'Water Storage Tank',
                    'Sanitaryware / WC',
                    'Taps, Valves & Faucets'
                ]
            },
            'electricalwire': {
                title: 'Electrical & Wire',
                label: 'Electrical Item',
                placeholder: 'Select Electrical Item',
                defaultUnit: 'Coil',
                items: [
                    '1.0 sq mm Wire',
                    '1.5 sq mm Wire',
                    '2.5 sq mm Wire',
                    '4.0 sq mm Wire',
                    'PVC Conduit Pipes',
                    'Modular Switches & Sockets',
                    'MCB & Distribution Board',
                    'LED Lights & Fixtures'
                ]
            },
            'painting': {
                title: 'Painting',
                label: 'Painting Item',
                placeholder: 'Select Paint Item',
                defaultUnit: 'Litre',
                items: [
                    'Wall Putty',
                    'Interior Primer',
                    'Exterior Primer',
                    'Interior Emulsion',
                    'Exterior Apex Emulsion',
                    'Enamel Paint',
                    'Waterproofing Chemical'
                ]
            },
            'wood': {
                title: 'Wood / Timber',
                label: 'Wood / Timber Type',
                placeholder: 'Select Wood / Timber Type',
                defaultUnit: 'Cft',
                items: [
                    'Teak Wood',
                    'Sal Wood',
                    'Plywood (Commercial)',
                    'Plywood (Marine / BWP)',
                    'Flush Doors',
                    'MDF / Particle Board'
                ]
            },
            'others': {
                title: 'Others / Hardware',
                label: 'Other Item Type',
                placeholder: 'Select Item Type',
                defaultUnit: 'Nos',
                items: [
                    'Hardware & Fasteners',
                    'Door Locks & Hinges',
                    'Safety Helmets & Nets',
                    'Scaffolding Pipes',
                    'Waterproofing Tape',
                    'Miscellaneous Material'
                ]
            }
        };

        let orderSelectedMaterials = [];
        let orderSelectedItems = {};

        // Helper to normalize mode to element ID prefix: 'order' or 'req'
        function getModePrefix(mode) {
            return (mode === 'order') ? 'order' : 'req';
        }

        // Initialize Tier 1 Material Type Checkbox Dropdown
        function initMaterialTypeDropdown(mode) {
            const pfx = getModePrefix(mode);
            const listEl = $(`#${pfx}_mattype_list`);
            const selectedMats = pfx === 'order' ? orderSelectedMaterials : reqSelectedMaterials;

            let html = '';
            Object.keys(materialDefinitions).forEach(function(matKey) {
                const def = materialDefinitions[matKey];
                const isChecked = selectedMats.includes(matKey);
                const chkId = `${pfx}_mat_${matKey}`;
                html += `
                <div class="custom-chk-item ${isChecked ? 'checked' : ''}" data-mode="${pfx}" data-material-key="${matKey}">
                    <input type="checkbox" class="custom-chk-input mat-type-chk" id="${chkId}" value="${matKey}" ${isChecked ? 'checked' : ''}>
                    <label class="custom-chk-label" for="${chkId}">${escapeHtml(def.title)}</label>
                </div>`;
            });

            listEl.html(html);
            updateMaterialTypeTriggerText(pfx);
        }

        // Update Tier 1 Button Trigger Text
        function updateMaterialTypeTriggerText(mode) {
            const pfx = getModePrefix(mode);
            const selectedMats = pfx === 'order' ? orderSelectedMaterials : reqSelectedMaterials;
            const btnTextEl = $(`#${pfx}_mattype_btn_text`);
            const hiddenMatEl = $(`#${pfx}_material_type`);

            if (selectedMats.length === 0) {
                btnTextEl.text('Select Material').addClass('text-muted');
                hiddenMatEl.val('');
            } else if (selectedMats.length === 1) {
                const title = (materialDefinitions[selectedMats[0]] || {}).title || selectedMats[0];
                btnTextEl.text(title).removeClass('text-muted');
                hiddenMatEl.val(selectedMats[0]);
            } else if (selectedMats.length === 2) {
                const title1 = (materialDefinitions[selectedMats[0]] || {}).title || selectedMats[0];
                const title2 = (materialDefinitions[selectedMats[1]] || {}).title || selectedMats[1];
                btnTextEl.text(`${title1}, ${title2}`).removeClass('text-muted');
                hiddenMatEl.val(selectedMats[0]);
            } else {
                const title1 = (materialDefinitions[selectedMats[0]] || {}).title || selectedMats[0];
                const title2 = (materialDefinitions[selectedMats[1]] || {}).title || selectedMats[1];
                btnTextEl.text(`${selectedMats.length} materials selected (${title1}, ${title2}...)`).removeClass('text-muted');
                hiddenMatEl.val(selectedMats[0]);
            }
        }

        // Synchronize Tier 2 Dynamic Sub-Type Dropdowns (One block per checked material type)
        function syncSubtypeDropdowns(mode) {
            const pfx = getModePrefix(mode);
            const container = $(`#${pfx}_subtypes_container`);
            const selectedMats = pfx === 'order' ? orderSelectedMaterials : reqSelectedMaterials;
            const selectedItemsDict = pfx === 'order' ? orderSelectedItems : reqSelectedItems;

            // 1. Remove blocks for materials no longer selected
            container.find('.subtype-material-block').each(function() {
                const cat = $(this).data('category');
                if (!selectedMats.includes(cat)) {
                    $(this).remove();
                }
            });

            // 2. Add or retain blocks for currently selected materials
            selectedMats.forEach(function(matKey) {
                const def = materialDefinitions[matKey] || materialDefinitions['others'];
                let blockEl = $(`#${pfx}_matblock_${matKey}`);

                if (!blockEl.length) {
                    let itemsHtml = '';
                    def.items.forEach(function(item, idx) {
                        const isChecked = selectedItemsDict[item] !== undefined;
                        const chkId = `${pfx}_subchk_${matKey}_${idx}`;
                        itemsHtml += `
                        <div class="custom-chk-item ${isChecked ? 'checked' : ''}" data-mode="${pfx}" data-category="${matKey}" data-item="${escapeHtml(item)}" data-default-unit="${escapeHtml(def.defaultUnit)}">
                            <input type="checkbox" class="custom-chk-input sub-item-chk" id="${chkId}" value="${escapeHtml(item)}" ${isChecked ? 'checked' : ''}>
                            <label class="custom-chk-label" for="${chkId}">${escapeHtml(item)}</label>
                        </div>`;
                    });

                    const blockHtml = `
                    <div class="subtype-material-block mb-3" id="${pfx}_matblock_${matKey}" data-category="${matKey}">
                        <!-- Dropdown row -->
                        <div class="row align-items-center mt-3 subtype-group-row" id="${pfx}_subgroup_${matKey}">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="fw-bold" for="${pfx}_subbtn_${matKey}">${escapeHtml(def.label)}</label>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group position-relative mb-0">
                                    <div class="custom-chk-dropdown-wrapper" id="${pfx}_subwrapper_${matKey}">
                                        <button type="button" class="form-control custom-chk-dropdown-btn text-start d-flex justify-content-between align-items-center bg-white sub-trigger-btn" id="${pfx}_subbtn_${matKey}" data-mode="${pfx}" data-category="${matKey}">
                                            <span id="${pfx}_subbtn_text_${matKey}" class="text-truncate">${escapeHtml(def.placeholder)}</span>
                                            <i class="fas fa-chevron-down ms-2 dropdown-arrow" style="font-size: 11px; color: #495057;"></i>
                                        </button>
                                        <div class="custom-chk-dropdown-menu" id="${pfx}_submenu_${matKey}" style="display: none;">
                                            <div class="custom-chk-list" id="${pfx}_sublist_${matKey}">
                                                ${itemsHtml}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Per-material table directly underneath this material dropdown -->
                        <div class="row mt-2 subtype-table-row" id="${pfx}_table_wrapper_${matKey}" style="display: none;">
                            <div class="col-lg-12">
                                <div class="card border border-light-subtle shadow-sm mb-2" style="border-radius: 8px; overflow: hidden;">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-secondary" style="font-size: 13px;">
                                            <i class="fa fa-list-check me-1 text-primary"></i> ${escapeHtml(def.label)} Items (<span id="${pfx}_items_count_${matKey}">0</span>)
                                        </span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle mb-0" id="${pfx}_table_${matKey}">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 32%;">Item / Material</th>
                                                        <th style="width: 16%;">Quantity <span class="text-danger">*</span></th>
                                                        <th style="width: 16%;">Unit</th>
                                                        <th style="width: 16%;">${pfx === 'order' ? 'Price / Item (₹)' : 'Est. Rate (₹)'}</th>
                                                        <th style="width: 16%;">${pfx === 'order' ? 'Total (₹)' : 'Est. Total (₹)'}</th>
                                                        <th style="width: 4%; text-align: center;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="${pfx}_tbody_${matKey}">
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-light fw-bold" style="font-size: 13px;">
                                                        <td>Subtotal (${escapeHtml(def.label)})</td>
                                                        <td id="${pfx}_subtotal_qty_${matKey}">0</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td id="${pfx}_subtotal_price_${matKey}">₹ 0.00</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    container.append(blockHtml);
                }

                updateSubtypeTriggerText(pfx, matKey);
            });

            if (pfx === 'order') {
                renderOrderItemsTable();
            } else {
                renderRequestItemsTable();
            }
        }

        // Update Tier 2 Sub-Type Trigger Text for a given category
        function updateSubtypeTriggerText(mode, matKey) {
            const pfx = getModePrefix(mode);
            const def = materialDefinitions[matKey] || materialDefinitions['others'];
            const btnTextEl = $(`#${pfx}_subbtn_text_${matKey}`);
            if (!btnTextEl.length) return;

            const selectedDict = pfx === 'order' ? orderSelectedItems : reqSelectedItems;
            const catItems = Object.keys(selectedDict).filter(k => selectedDict[k].category === matKey);

            if (catItems.length === 0) {
                btnTextEl.text(def.placeholder).addClass('text-muted');
            } else if (catItems.length === 1) {
                btnTextEl.text(catItems[0]).removeClass('text-muted');
            } else if (catItems.length === 2) {
                btnTextEl.text(catItems.join(', ')).removeClass('text-muted');
            } else {
                btnTextEl.text(`${catItems.length} items selected (${catItems.slice(0, 2).join(', ')}...)`).removeClass('text-muted');
            }
        }

        // Toggle dropdown open/close (Tier 1 & Tier 2)
        $(document).on('click', '#order_mattype_btn, #req_mattype_btn, .sub-trigger-btn', function(e) {
            e.stopPropagation();
            const wrapper = $(this).closest('.custom-chk-dropdown-wrapper');
            const menu = wrapper.find('.custom-chk-dropdown-menu');
            const wasOpen = menu.is(':visible');

            $('.custom-chk-dropdown-menu').hide();
            $('.custom-chk-dropdown-wrapper').removeClass('open');

            if (!wasOpen) {
                menu.show();
                wrapper.addClass('open');
            }
        });

        // Click outside closes any open dropdown
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-chk-dropdown-wrapper').length) {
                $('.custom-chk-dropdown-menu').hide();
                $('.custom-chk-dropdown-wrapper').removeClass('open');
            }
        });

        // Prevent clicking inside dropdown menu from closing it
        $(document).on('click', '.custom-chk-dropdown-menu', function(e) {
            e.stopPropagation();
        });

        // Click on custom-chk-item row toggles the checkbox
        $(document).on('click', '.custom-chk-item', function(e) {
            if ($(e.target).is('input[type="checkbox"]')) {
                return;
            }
            e.preventDefault();
            const chk = $(this).find('.custom-chk-input');
            chk.prop('checked', !chk.prop('checked')).trigger('change');
        });

        // Tier 1 Material Type checkbox change
        $(document).on('change', '.mat-type-chk', function(e) {
            e.stopPropagation();
            const row = $(this).closest('.custom-chk-item');
            const mode = row.data('mode');
            const pfx = getModePrefix(mode);
            const matKey = row.data('material-key');
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                row.addClass('checked');
                if (pfx === 'order') {
                    if (!orderSelectedMaterials.includes(matKey)) orderSelectedMaterials.push(matKey);
                } else {
                    if (!reqSelectedMaterials.includes(matKey)) reqSelectedMaterials.push(matKey);
                }
            } else {
                row.removeClass('checked');
                if (pfx === 'order') {
                    orderSelectedMaterials = orderSelectedMaterials.filter(m => m !== matKey);
                    // Remove any items selected under this material type
                    Object.keys(orderSelectedItems).forEach(function(k) {
                        if (orderSelectedItems[k].category === matKey) {
                            delete orderSelectedItems[k];
                        }
                    });
                } else {
                    reqSelectedMaterials = reqSelectedMaterials.filter(m => m !== matKey);
                    Object.keys(reqSelectedItems).forEach(function(k) {
                        if (reqSelectedItems[k].category === matKey) {
                            delete reqSelectedItems[k];
                        }
                    });
                }
            }

            updateMaterialTypeTriggerText(pfx);
            syncSubtypeDropdowns(pfx);
        });

        // Tier 2 Sub-Item checkbox change
        $(document).on('change', '.sub-item-chk', function(e) {
            e.stopPropagation();
            const itemRow = $(this).closest('.custom-chk-item');
            const mode = itemRow.data('mode');
            const pfx = getModePrefix(mode);
            const catKey = itemRow.data('category');
            const itemName = itemRow.data('item');
            const defaultUnit = itemRow.data('default-unit') || 'Nos';
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                itemRow.addClass('checked');
            } else {
                itemRow.removeClass('checked');
            }

            if (pfx === 'order') {
                if (isChecked) {
                    if (!orderSelectedItems[itemName]) {
                        orderSelectedItems[itemName] = {
                            category: catKey,
                            quantity: '',
                            unit: defaultUnit,
                            rate: '',
                            price: ''
                        };
                    }
                } else {
                    delete orderSelectedItems[itemName];
                }
                updateSubtypeTriggerText('order', catKey);
                renderOrderItemsTable();
            } else {
                if (isChecked) {
                    if (!reqSelectedItems[itemName]) {
                        reqSelectedItems[itemName] = {
                            category: catKey,
                            quantity: '',
                            unit: defaultUnit,
                            rate: '',
                            price: ''
                        };
                    }
                } else {
                    delete reqSelectedItems[itemName];
                }
                updateSubtypeTriggerText('req', catKey);
                renderRequestItemsTable();
            }
        });

        // Render Inward Order Items Tables (One table per material type directly below its field)
        function renderOrderItemsTable() {
            // First save any current user inputs from the DOM into orderSelectedItems
            $('.order-item-tr').each(function() {
                const item = $(this).data('item');
                if (orderSelectedItems[item]) {
                    orderSelectedItems[item].quantity = $(this).find('.item-qty').val();
                    orderSelectedItems[item].unit = $(this).find('.item-unit').val();
                    orderSelectedItems[item].rate = $(this).find('.item-rate').val();
                    orderSelectedItems[item].price = $(this).find('.item-price').val();
                }
            });

            let globalIndex = 0;

            orderSelectedMaterials.forEach(function(matKey) {
                const tbody = $(`#order_tbody_${matKey}`);
                const tableWrapper = $(`#order_table_wrapper_${matKey}`);
                const countBadge = $(`#order_items_count_${matKey}`);

                tbody.empty();

                const catItems = Object.keys(orderSelectedItems).filter(k => orderSelectedItems[k].category === matKey);
                countBadge.text(catItems.length);

                if (catItems.length === 0) {
                    tableWrapper.hide();
                } else {
                    tableWrapper.show();
                    catItems.forEach(function(item) {
                        const data = orderSelectedItems[item];
                        const unitSelect = buildUnitOptions(data.unit);
                        const row = `
                        <tr class="order-item-tr" data-item="${escapeHtml(item)}" data-category="${matKey}">
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 13px;">${escapeHtml(item)}</span>
                                <input type="hidden" name="items[${globalIndex}][name]" value="${escapeHtml(item)}">
                                <input type="hidden" name="items[${globalIndex}][category]" value="${escapeHtml(matKey)}">
                            </td>
                            <td>
                                <input type="number" step="any" min="0" class="form-control form-control-sm item-qty"
                                       name="items[${globalIndex}][quantity]"
                                       placeholder="Qty *"
                                       value="${data.quantity || ''}" required>
                            </td>
                            <td>
                                <select class="form-select form-select-sm item-unit" name="items[${globalIndex}][unit]">
                                    ${unitSelect}
                                </select>
                            </td>
                            <td>
                                <input type="number" step="any" min="0" class="form-control form-control-sm item-rate"
                                       name="items[${globalIndex}][rate]"
                                       placeholder="₹ / unit"
                                       value="${data.rate || ''}">
                            </td>
                            <td>
                                <input type="number" step="any" min="0" class="form-control form-control-sm item-price"
                                       name="items[${globalIndex}][price]"
                                       placeholder="Total ₹"
                                       value="${data.price || ''}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm p-1 px-2 remove-row-btn" data-mode="order" data-item="${escapeHtml(item)}" title="Remove">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>`;
                        tbody.append(row);
                        globalIndex++;
                    });
                }
            });

            calculateOrderTotals();
        }

        // Calculate Order Totals across all per-material tables
        function calculateOrderTotals() {
            let totalQty = 0;
            let totalPrice = 0;
            let totalItemCount = 0;

            orderSelectedMaterials.forEach(function(matKey) {
                let catQty = 0;
                let catPrice = 0;

                $(`#order_tbody_${matKey} tr`).each(function() {
                    const item = $(this).data('item');
                    const qtyVal = parseFloat($(this).find('.item-qty').val()) || 0;
                    const rateVal = parseFloat($(this).find('.item-rate').val()) || 0;
                    let priceVal = parseFloat($(this).find('.item-price').val());

                    if (isNaN(priceVal) || priceVal === 0) {
                        if (qtyVal > 0 && rateVal > 0) {
                            priceVal = qtyVal * rateVal;
                            $(this).find('.item-price').val(priceVal.toFixed(2));
                        } else {
                            priceVal = 0;
                        }
                    }

                    if (orderSelectedItems[item]) {
                        orderSelectedItems[item].quantity = $(this).find('.item-qty').val();
                        orderSelectedItems[item].unit = $(this).find('.item-unit').val();
                        orderSelectedItems[item].rate = $(this).find('.item-rate').val();
                        orderSelectedItems[item].price = $(this).find('.item-price').val();
                    }

                    catQty += qtyVal;
                    catPrice += priceVal;
                });

                $(`#order_subtotal_qty_${matKey}`).text(catQty > 0 ? catQty : '0');
                $(`#order_subtotal_price_${matKey}`).text('₹ ' + catPrice.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                totalQty += catQty;
                totalPrice += catPrice;
                totalItemCount += $(`#order_tbody_${matKey} tr`).length;
            });

            if (totalItemCount === 0) {
                $('#order_quantity').prop('readonly', false).val('');
                $('#order_price').prop('readonly', false).val('');
                $('#order_price_words').text('');
                $('#order_calc_badge_qty, #order_calc_badge_price').remove();
            } else {
                $('#order_quantity').prop('readonly', true).val(totalQty > 0 ? totalQty : '');
                $('#order_price').prop('readonly', true).val(totalPrice > 0 ? totalPrice.toFixed(2) : '');
                $('#order_price_words').text(totalPrice > 0 ? numberToWordsIndian(totalPrice) : '');
                if (!$('#order_calc_badge_qty').length) {
                    $('#order_quantity').after('<small id="order_calc_badge_qty" class="form-text text-success fw-semibold"><i class="fa fa-calculator me-1"></i>Auto-calculated from selected items</small>');
                }
                if (!$('#order_calc_badge_price').length) {
                    $('#order_price').after('<small id="order_calc_badge_price" class="form-text text-success fw-semibold"><i class="fa fa-calculator me-1"></i>Auto-calculated from selected items</small>');
                }
            }
        }

        // Input calculations in Order tables
        $(document).on('input', '.order-item-tr .item-qty, .order-item-tr .item-rate', function() {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.item-qty').val()) || 0;
            const rate = parseFloat(row.find('.item-rate').val()) || 0;
            if (qty > 0 && rate > 0) {
                row.find('.item-price').val((qty * rate).toFixed(2));
            }
            calculateOrderTotals();
        });

        $(document).on('input', '.order-item-tr .item-price', function() {
            calculateOrderTotals();
        });

        $(document).on('change', '.order-item-tr .item-unit', function() {
            const row = $(this).closest('tr');
            const item = row.data('item');
            if (orderSelectedItems[item]) {
                orderSelectedItems[item].unit = row.find('.item-unit').val();
            }
        });

        // Remove row button from tables
        $(document).on('click', '.remove-row-btn', function() {
            const item = $(this).data('item');
            const catKey = orderSelectedItems[item] ? orderSelectedItems[item].category : null;
            delete orderSelectedItems[item];
            if (catKey) {
                const chkRow = $(`#order_sublist_${catKey} .custom-chk-item[data-item="${item}"]`);
                chkRow.removeClass('checked').find('.custom-chk-input').prop('checked', false);
                updateSubtypeTriggerText('order', catKey);
            }
            renderOrderItemsTable();
        });

        $(document).ready(function() {
            const initialMat = "{{ $selectedMaterial }}";
            orderSelectedMaterials = initialMat ? [initialMat] : [];

            initMaterialTypeDropdown('order');
            syncSubtypeDropdowns('order');

            // Price in words when typed directly into #order_price (when no items table active)
            $('#order_price').on('input', function() {
                if (Object.keys(orderSelectedItems).length === 0) {
                    const val = parseFloat($(this).val());
                    $('#order_price_words').text(val > 0 ? numberToWordsIndian(val) : '');
                }
            });

            // Vendor Search - Inward Order
            $('#order_vendor_name').on('input', function() {
                let query = $(this).val().trim();
                if (query.length >= 1) {
                    $.ajax({
                        url: "{{ route('vendors.search') }}",
                        type: 'GET',
                        data: { name: query },
                        success: function(data) {
                            let suggestions = '';
                            if (data && data.length > 0) {
                                data.forEach(function(vendor) {
                                    const safeName = escapeHtml(vendor.name || '');
                                    const safeMobile = escapeHtml(vendor.mobile_no || '');
                                    const safeAddress = escapeHtml(vendor.address || '');
                                    const safeGst = escapeHtml(vendor.gst || '');
                                    suggestions += `
                                        <a href="#" class="list-group-item list-group-item-action order-vendor-opt"
                                            data-id="${vendor.id}"
                                            data-name="${safeName}"
                                            data-mobile="${safeMobile}"
                                            data-address="${safeAddress}"
                                            data-gst="${safeGst}">
                                            <strong>${safeName}</strong>${safeMobile ? ` - <small class="text-muted">${safeMobile}</small>` : ''}
                                        </a>`;
                                });
                            } else {
                                suggestions = `<div class="list-group-item text-muted small fst-italic">No vendors found matching "${escapeHtml(query)}"</div>`;
                            }
                            $('#order_vendor_suggestions').html(suggestions).show();
                        },
                        error: function(err) {
                            console.error('Vendor search error:', err);
                        }
                    });
                } else {
                    $('#order_vendor_suggestions').hide().empty();
                }
            });

            $('#order_vendor_name').on('focus', function() {
                if ($(this).val().trim().length >= 1 && $('#order_vendor_suggestions').children().length > 0) {
                    $('#order_vendor_suggestions').show();
                }
            });

            $(document).on('click', '.order-vendor-opt', function(e) {
                e.preventDefault();
                $('#order_vendor_name').val($(this).data('name'));
                $('#order_vendor_mobile').val($(this).data('mobile') || '');
                $('#order_vendor_id').val($(this).data('id'));
                $('#order_vendor_address').val($(this).data('address') || '');
                $('#order_vendor_gstin').val($(this).data('gst') || '');
                $('#order_vendor_suggestions').hide().empty();
            });

            // Hide suggestions on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#order_vendor_name, #order_vendor_suggestions').length) {
                    $('#order_vendor_suggestions').hide();
                }
            });

            // Inward Order Submit
            $('#orderForm').on('submit', function(e) {
                e.preventDefault();
                $('#loadingSpinner').removeClass('d-none');
                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#loadingSpinner').addClass('d-none');
                        if (response.status === 'success') {
                            showStatusModal(response.message, {
                                title: 'Order Added',
                                buttonText: 'OK',
                                onConfirm: function() {
                                    form[0].reset();
                                    const enteredDate = formData.get('date');
                                    let url = "{{ route('material', ['siteId' => $siteId, 'materialType' => 'all']) }}";
                                    if (enteredDate) {
                                        url += "?month=" + enteredDate.slice(0, 7);
                                    }
                                    window.location.href = url;
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loadingSpinner').addClass('d-none');
                        let message = "Something went wrong!";
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(e => e[0]).join("\n");
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showStatusModal(message, {
                            title: 'Error',
                            type: 'error',
                            buttonText: 'Close'
                        });
                    }
                });
            });
        });
    </script>

    <style>
        .custom-status-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-status-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
        }

        .custom-status-dialog {
            position: relative;
            width: min(420px, calc(100vw - 32px));
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            text-align: center;
            border: 1px solid #dbeafe;
            animation: modalPop 0.2s ease-out;
        }

        .custom-status-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            font-size: 30px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(34, 197, 94, 0.25);
        }

        .custom-status-modal.is-error .custom-status-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 12px 24px rgba(239, 68, 68, 0.25);
        }

        .custom-status-title {
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 700;
        }

        .custom-status-message {
            margin-bottom: 20px;
            color: #475569;
            font-size: 14px;
            white-space: pre-line;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Custom Checkbox Dropdown matching media_1790143989140.png */
        .custom-chk-dropdown-wrapper {
            position: relative;
            width: 100%;
        }

        .custom-chk-dropdown-btn {
            min-height: 40px;
            height: 40px;
            background-color: #ffffff !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
            padding: 8px 14px !important;
            font-size: 14px !important;
            color: #495057 !important;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .custom-chk-dropdown-btn:hover {
            border-color: #adb5bd !important;
        }

        .custom-chk-dropdown-btn:focus,
        .custom-chk-dropdown-wrapper.open .custom-chk-dropdown-btn {
            border-color: #80bdff !important;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
        }

        .custom-chk-dropdown-btn .dropdown-arrow {
            transition: transform 0.2s ease;
        }

        .custom-chk-dropdown-wrapper.open .custom-chk-dropdown-btn .dropdown-arrow {
            transform: rotate(180deg);
        }

        .custom-chk-dropdown-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            background: #ffffff;
            border: 1px solid #ced4da;
            border-radius: 6px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            z-index: 1050;
            padding: 6px 0;
        }

        .custom-chk-list {
            max-height: 250px;
            overflow-y: auto;
            padding: 2px 0;
            scrollbar-width: thin;
            scrollbar-color: #64748b #f8f9fa;
        }

        .custom-chk-list::-webkit-scrollbar {
            width: 6px;
        }

        .custom-chk-list::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }

        .custom-chk-list::-webkit-scrollbar-thumb {
            background: #64748b;
            border-radius: 3px;
        }

        .custom-chk-item {
            display: flex;
            align-items: center;
            padding: 8px 14px;
            margin: 2px 8px;
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s ease;
        }

        .custom-chk-item:hover,
        .custom-chk-item.checked {
            background-color: #f0f4f9;
        }

        .custom-chk-input {
            width: 17px;
            height: 17px;
            margin: 0;
            margin-right: 12px;
            border: 1.5px solid #6c757d;
            border-radius: 3px;
            accent-color: #1572e8;
            cursor: pointer;
            flex-shrink: 0;
        }

        .custom-chk-label {
            margin: 0;
            font-size: 14px;
            color: #333333;
            font-weight: 400;
            cursor: pointer;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #order_items_table_wrapper table thead th,
        #req_items_table_wrapper table thead th {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            vertical-align: middle;
            padding: 8px 10px;
        }

        #order_items_table_wrapper table tbody td,
        #req_items_table_wrapper table tbody td {
            vertical-align: middle;
            padding: 6px 10px;
        }

        #order_items_table_wrapper table tfoot td,
        #req_items_table_wrapper table tfoot td {
            font-size: 13px;
            padding: 8px 10px;
        }
    </style>
@endsection
