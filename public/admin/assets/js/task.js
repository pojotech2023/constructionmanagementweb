//Vendor search
$(document).ready(function() {
    console.log("task.js loaded ✅");

    $('#vendor_name').on('input', function() {
        let query = $(this).val();
        if (query.length >= 1) {
            $.ajax({
                url: "{{ route('vendors.search') }}",
                type: 'GET',
                data: {
                    name: query
                },
                success: function(data) {
                    let suggestions = '';
                    data.forEach(function(vendor) {
                        suggestions +=
                            `<a href="#" class="list-group-item list-group-item-action vendor-option" data-name="${vendor.name}" data-mobile="${vendor.mobile_no}">${vendor.name}</a>`;
                    });
                    $('#vendor_suggestions').html(suggestions).show();
                }
            });
        } else {
            $('#vendor_suggestions').hide();
        }
    });

    $(document).on('click', '.vendor-option', function(e) {
        e.preventDefault();
        $('#vendor_name').val($(this).data('name'));
        $('#vendor_mobile').val($(this).data('mobile'));
        $('#vendor_suggestions').hide();
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#vendor_name, #vendor_suggestions').length) {
            $('#vendor_suggestions').hide();
        }
    });
});