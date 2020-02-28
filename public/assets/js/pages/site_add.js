jQuery(document).ready(function() {

    var siteFormRules = {
        'site[number]': {
            required: true
        },
        'site[name]': {
            required: true
        },
        'site[address]': {
            required: true
        },
        'site[streetNumber]': {
            required: true
        },
        'site[street]': {
            required: true
        },
        'site[city]': {
            required: true
        },
        'site[state]': {
            required: true
        },
        'site[postalCode]': {
            required: true
        },
        'site[country]': {
            required: true
        },
        'site[erf]': {
            required: true
        },
        'site[siteStatus]': {
            required: true
        },
        'site[hoursOfAccess]': {
            required: true
        },
        'site[primaryEmergencyContact]': {
            required: true
        }
    };
    for (var i=0; i <= siteInventoryIndex; i++){
        siteFormRules['siteInventory['+i+'][category]'] = {
            required: true
        };
        siteFormRules['siteInventory['+i+'][quantity]'] = {
            required: true,
            min: 1
        };
    }

    $('#siteForm').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: siteFormRules,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        }
    });


    $(document).on('change', '.siteInventoryQuantity', function () {
        if($(this).val() <= 0){
            $(this).val(1);
        }
    });

    $(document).on('change', '.siteInventorySize, .siteInventoryHeight, .siteInventoryWeight', function () {
        if($(this).val() < 0){
            $(this).val(0);
        }
    });

    $(document).on('click', '#addSiteInventory', function (e) {
        e.preventDefault();
        siteInventoryIndex++;
        var html = '<div class="site-inventory-item">';
        html += '   <div class="form-group">';
        html += '       <label>Category</label>';
        html += '       <select class="form-control" name="siteInventory['+siteInventoryIndex+'][category]">';
        html += '           <option value="">Select a Inventory Category</option>';
        siteInventoryCategories.forEach(function (item) {
            html += '       <option value="'+item.id+'">'+item.name+'</option>';
        });
        html += '       </select>';
        html += '   </div>';
        html += '   <div class="form-group">';
        html += '       <label>Quantity</label>';
        html += '       <input type="number" name="siteInventory['+siteInventoryIndex+'][quantity]" value="1" class="form-control siteInventoryQuantity" placeholder="Quantity">';
        html += '   </div>';
        html += '   <div class="form-group">';
        html += '       <label>Size</label>';
        html += '       <input type="number" name="siteInventory['+siteInventoryIndex+'][size]" step="0.01" value="" class="form-control siteInventorySize" placeholder="Size (mm)">';
        html += '   </div>';
        html += '   <div class="form-group">';
        html += '       <label>Height</label>';
        html += '       <input type="number" name="siteInventory['+siteInventoryIndex+'][height]" step="0.01" value="" class="form-control siteInventoryHeight" placeholder="Height (m)">';
        html += '   </div>';
        html += '   <div class="form-group">';
        html += '       <label>Weight</label>';
        html += '       <input type="number" name="siteInventory['+siteInventoryIndex+'][weight]" step="0.01" value="" class="form-control siteInventoryWeight" placeholder="Weight (kg)">';
        html += '   </div>';
        html += '   <div class="form-group">';
        html += '       <label>Additional info</label>';
        html += '       <textarea class="form-control" name="siteInventory['+siteInventoryIndex+'][info]" rows="3" placeholder="Additional info"></textarea>';
        html += '   </div>';
        html += '   <div class="col-12 text-right">';
        html += '       <button type="button" class="btn btn-danger btn-sm removeSiteInventory">Remove Site Inventory</button>';
        html += '   </div>';
        html += '<hr></div>';

        $('#siteInventoryItems').append(html);

        $('select[name="siteInventory['+siteInventoryIndex+'][category]"]').rules('add', { required: true });
        $('input[name="siteInventory['+siteInventoryIndex+'][quantity]"]').rules('add', { required: true, min: 1 });
    });

    $(document).on('click', '.removeSiteInventory', function (e) {
        e.preventDefault();

        $(this).parents('.site-inventory-item').remove();
    });

    $(document).on('shown.bs.modal', '#modalCreateContact', function () {
        $('#formCreateContact').resetForm();
        $('#selectContactType').val('').change();
        $('#selectContactTypeError').hide();
    });

    $('#selectContactType').select2({
        placeholder: "Select a Content Type",
        allowClear: true,
        tags: true,
        createTag: function (tag) {
            return {
                id: tag.term,
                text: tag.term,
                isNew : true
            };
        }
    }).on("select2:select", function(e) {
        if(e.params.data.isNew){
            var el = $(this);
            $.ajax({
                type: 'POST',
                url: "/landlord/createContactType",
                data: {
                    name: e.params.data.text
                },
                success: function (data) {
                    if (data.result === true) {
                        el.find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+data.id+'">'+data.name+'</option>');
                    }
                    else{
                        if(data.message !== undefined){
                            toastr.error(data.message);
                        }
                        else{
                            toastr.error('Contact type not create');
                        }
                        el.find('[value="'+e.params.data.id+'"]').remove();
                    }
                }
            });
        }
    });


    var formCreateContactValidator = $('#formCreateContact').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: {
            'landlord': {
                required: true
            },
            'type': {
                required: true
            },
            'firstName': {
                required: true
            },
            'lastName': {
                required: true
            },
            'company': {
                required: true
            },
            'email': {
                required: true,
                email: true
            },
            'mobile': {
                required: true
            }
        }
    });

    $(document).on('change', '#selectContactType', function () {
        if($(this).val()){
            $('#selectContactTypeError').hide();
        }
        else{
            $('#selectContactTypeError').show();
        }
    });

    $(document).on('click', '#btnCreateContact', function (e) {
        e.preventDefault();
        if (formCreateContactValidator.form() === true) {
            var form = $('#formCreateContact');
            var url = form.attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serializeArray(), // serializes the form's elements.
                success: function(data)
                {
                    if (data.result === true) {
                        $('#selectPrimaryEmergencyContact').append('<option value="'+data.id+'">'+data.name+'</option>');
                        $('#selectSecondaryEmergencyContact').append('<option value="'+data.id+'">'+data.name+'</option>');
                        toastr.success("Contact has been created");
                        $('#modalCreateContact').modal('hide');
                    }
                    else if(data.message !== undefined){
                        if(typeof data.message == "object"){
                            data.message.forEach(function (error) {
                                toastr.error(error);
                            });
                        }
                        else{
                            toastr.error(data.message);
                        }
                    }
                    else{
                        toastr.error('Contact not create');
                    }
                }
            });
        }

    });

    $('#emergencyAccessUpdated').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $(document).on('change', '#emergencyAccessUpdatedManually', function () {
        if($(this).is(":checked")){
            $('#emergencyAccessUpdated').prop('disabled', false);
        }
        else{
            $('#emergencyAccessUpdated').prop('disabled', true);
        }
    });

});

var autocomplete;

var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name'
};

function initAutocomplete() {
    // Create the autocomplete object, restricting the search predictions to
    // geographical location types.
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('googleAddress'), {types: ['geocode']});

    // Avoid paying for data that you don't need by restricting the set of
    // place fields that are returned to just the address components.
    autocomplete.setFields(['address_components']);

    // When the user selects an address from the drop-down, populate the
    // address fields in the form.
    autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete.getPlace();

    for (var component in componentForm) {
        document.getElementById(component).value = '';
        $('#googleMapLabel').html('');
    }
    // Get each component of the address from the place details,
    // and then fill-in the corresponding field on the form.
    for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(addressType).value = val;
        }
    }

    var url = encodeURI('https://www.google.com/maps/search/?api=1&query='+document.getElementById('googleAddress').value);

    $('#googleMapLabel').html('<a href="'+url+'" target="_blank" id="googleMapLink">Site Google Maps link <i class="fa fa-external-link-alt"></i></a>');

}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            var circle = new google.maps.Circle(
                {center: geolocation, radius: position.coords.accuracy});
            autocomplete.setBounds(circle.getBounds());
        });
    }
}
