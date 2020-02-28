jQuery(document).ready(function() {
    var rules = {
        // Step 1
        'landlord[type]': {
            required: true
        },
        'landlord[number]': {
            required: true
        },
        'landlord[name]': {
            required: true
        },
        'landlord[address1]': {
            required: true
        },
        'landlord[city]': {
            required: true
        },
        'landlord[state]': {
            required: true
        },
        'landlord[postalCode]': {
            required: true
        },
        'landlord[country]': {
            required: true
        },
        'landlord[beeStatus]': {
            required: true
        },
        'landlord[beeExpiry]': {
            required: {
                depends: function (element) {
                    return ($("#beeStatus").val() && $("#beeStatus").val() != 1);
                }
            },
            date: {
                depends: function (element) {
                    return ($("#beeStatus").val() && $("#beeStatus").val() != 1);
                }
            }
        },
        'landlord[accountHolder]': {
            required: true
        },
        'landlord[bankName]': {
            required: true
        },
        'landlord[branchName]': {
            required: true
        },
        'landlord[branchCode]': {
            required: true
        },
        'landlord[accountNumber]': {
            required: true
        },
        'landlord[accountType]': {
            required: true
        },
        'landlord[vatNumber]': {
            required: {
                depends: function(element) {
                    return $("#vatStatus").is(":checked");
                }
            }
        },
        'landlord[documentStatus]': {
            required: true
        },
    };

    //ADD COMMENT RULES
    for (var i=0; i <= commentIndex; i++){
        rules['comments['+i+'][comment]'] = {
            required: true
        };
    }

    //Add DOCUMENT RULES
    for (var i=0; i < documentIndex; i++){
        rules['document['+i+'][type]'] = {
            required: true
        };
        rules['document['+i+'][document]'] = {
            required: true
        };
    }
    //ADD CONTACT RULES
    for (var i=0; i < contactIndex; i++){
        rules['contact['+i+'][type]'] = {
            required: true
        };
        rules['contact['+i+'][firstName]'] = {
            required: true
        };
        rules['contact['+i+'][lastName]'] = {
            required: true
        };
        rules['contact['+i+'][company]'] = {
            required: true
        };
        rules['contact['+i+'][email]'] = {
            required: true,
            email: true
        };
        rules['contact['+i+'][mobile]'] = {
            required: true
        };

        $('#kt_select2__'+i).select2({
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
                            $('.kt-select2').each(function () {
                                if($(this).find('option[value="'+data.id+'"]').length === 0){
                                    $(this).append('<option value="'+data.id+'">'+data.name+'</option>');
                                }
                            });
                            contactTypes.push({
                                id: data.id,
                                name: data.name,
                            });
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
    }

    var formEl = $('#kt_form');
    var validator = formEl.validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rules,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        }
    });

    var wizard = new KTWizard('kt_wizard_v3', {
        startStep: 1,
    });
    // Validation before going to next page
    wizard.on('beforeNext', function(wizardObj) {
        if (validator.form() !== true) {
            wizardObj.stop();  // don't go to the next step
        }

        if(wizardObj.currentStep == 2){
            $('.documentInput').each(function (index, item) {
                if($(item).val() || $(item).prev().val()){
                    $(item).parent().find('.text-danger').hide();
                }
                else{
                    $(item).parent().find('.text-danger').show();
                    wizardObj.stop();
                }
            });
        }
    });

    wizard.on('beforePrev', function(wizardObj) {
        if (validator.form() !== true) {
            wizardObj.stop();  // don't go to the next step
        }

        if(wizardObj.currentStep == 2){
            $('.documentInput').each(function (index, item) {
                if($(item).val() || $(item).prev().val()){
                    $(item).parent().find('.text-danger').hide();
                }
                else{
                    $(item).parent().find('.text-danger').show();
                    wizardObj.stop();
                }
            });
        }
    });
    // Change event
    wizard.on('change', function(wizard) {
        KTUtil.scrollTop();
    });

    //DATEPICKER
    $('#landlord_beeExpiry').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    //BEE STATUS
    $(document).on('change', '#beeStatus', function (e) {
       if($(this).val() && $(this).val() != 1){
           $('#landlord_beeExpiry').parents('.form-group').show();
       }
       else{
           $('#landlord_beeExpiry').parents('.form-group').hide();
       }
    });
    //VAT STATUS
    $(document).on('change', '#vatStatus', function (e) {
        if($(this).is(':checked')){
            $('#vatNumber').parent().show();
        }
        else{
            $('#vatNumber').parent().hide();
        }
    });

    //DOCUMENTS
    $(document).on('click', '#addDocument', function (e) {
       e.preventDefault();
        documentIndex++;

        var html = '<div class="document-item"><input type="hidden" value="" name="document['+documentIndex+'][id]">';
        html += '<div class="form-group">';
        html += '   <label>Type</label>';
        html += '   <select name="document['+documentIndex+'][type]" class="form-control" aria-describedby="document_type-error" aria-invalid="false">';
        html += '       <option value="">Select a Document Type</option>';
        documentTypes.forEach(function (item) {
            html += '       <option value="'+item.id+'">'+item.name+'</option>';
        });
        html += '   </select>';
        html += '</div>';

        html += '<div class="form-group row">';
        html += '   <label class="col-12 col-form-label">Upload File:</label>';
        html += '   <div class="col-12">';
        html += '       <div class="upload-file-container">';
        html += '           <input type="hidden" name="document['+documentIndex+'][name]" value="">';
        html += '           <input type="hidden" class="upload-file-container__fake-upload" name="document['+documentIndex+'][document]" value="">';
        html += '           <input  type="file" class="documentInput upload-file-container__custom-upload" name="document['+documentIndex+'][document]" value="" hidden="hidden">';
        html += '           <button class="upload-file-container__upload-button btn btn-sm btn-info"><i class="fas fa-plus-circle"></i> <span>Choose a file</span></button>';
        html += '           <div class="upload-file-container__file-uploaded">';
        html += '               <div class="upload-file-container__close">';
        html += '                   <i class="fa fa-times"></i>';
        html += '               </div>';
        html += '               <i class="fas fa-cloud-download-alt"></i>';
        html += '               <span class="upload-file-container__file-name"> name</span>';
        html += '               <span class="upload-file-container__file-date"> date </span>';
        html += '           </div>';
        html += '           <div class="text-danger" style="display: none;">This field is required.</div>';
        html += '       </div>';
        html += '   </div>';
        html += '</div>';

        html += '<div class="col-12 text-right">';
        html += '   <button type="button" class="btn btn-danger btn-sm removeDocument">Remove Document</button>';
        html += '</div>';

        html += '<hr></div>';

        $('#documentsForm').append(html);

        $('select[name="document['+documentIndex+'][type]"]').rules('add', { required: true });
        $('input[name="document['+documentIndex+'][document]"]').rules('add', { required: true });
    });
    $(document).on('click', '.removeDocument', function (e) {
        e.preventDefault();
        $(this).parents('.document-item').remove();
    });


    //CONTACT
    $(document).on('click', '#addContact', function (e) {
        e.preventDefault();
        contactIndex++;

        var html = '<div class="contact-item"><input type="hidden" value="" name="contact['+contactIndex+'][id]">';
        html += '<div class="form-group">';
        html += '   <label>Type</label>';
        html += '   <select name="contact['+contactIndex+'][type]" id="kt_select2__'+contactIndex+'" class="form-control kt-select2">';
        html += '       <option></option>';
        contactTypes.forEach(function (item) {
            html += '       <option value="'+item.id+'">'+item.name+'</option>';
        });
        html += '   </select>';
        html += '   <div class="invalid-feedback">This field is required.</div>';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Name</label>';
        html += '   <input type="text" class="form-control" name="contact['+contactIndex+'][firstName]" placeholder="Name" value="" aria-describedby="contact_firstName-error">';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Surname</label>';
        html += '   <input type="text" class="form-control" name="contact['+contactIndex+'][lastName]" placeholder="Surname" value="" aria-describedby="contact_lastName-error">';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Company</label>';
        html += '   <input type="text" class="form-control" name="contact['+contactIndex+'][company]" placeholder="Company" value="" aria-describedby="contact_company-error">';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Email</label>';
        html += '   <input type="text" class="form-control" name="contact['+contactIndex+'][email]" placeholder="Email" value="" aria-describedby="contact_email-error">';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Mobile</label>';
        html += '   <input type="tel" class="form-control" name="contact['+contactIndex+'][mobile]" placeholder="Mobile" value="" aria-describedby="contact_mobile-error">';
        html += '</div>';

        html += '<div class="form-group">';
        html += '   <label>Landline</label>';
        html += '   <input type="tel" class="form-control" name="contact['+contactIndex+'][landline]" placeholder="Landline" value="" aria-describedby="contact_landline-error">';
        html += '</div>';

        html += '<div class="col-12 text-right">';
        html += '   <button type="button" class="btn btn-danger btn-sm removeContact">Remove Contact</button>';
        html += '</div>';

        html += '<hr></div>';

        $('#contactsForm').append(html);

        $('select[name="contact['+contactIndex+'][type]"]').rules('add', { required: true });
        $('input[name="contact['+contactIndex+'][firstName]"]').rules('add', { required: true });
        $('input[name="contact['+contactIndex+'][lastName]"]').rules('add', { required: true });
        $('input[name="contact['+contactIndex+'][company]"]').rules('add', { required: true });
        $('input[name="contact['+contactIndex+'][email]"]').rules('add', { required: true, email: true });
        $('input[name="contact['+contactIndex+'][mobile]"]').rules('add', { required: true });

        $('#kt_select2__'+contactIndex).select2({
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
                            $('.kt-select2').each(function () {
                                if($(this).find('option[value="'+data.id+'"]').length === 0){
                                    $(this).append('<option value="'+data.id+'">'+data.name+'</option>');
                                }
                            });
                            contactTypes.push({
                                id: data.id,
                                name: data.name,
                            });
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
    });
    $(document).on('click', '.removeContact', function (e) {
        e.preventDefault();
        $(this).parents('.contact-item').remove();
    });

    //COMMENT
    $(document).on('click', '#addComment', function (e) {
        e.preventDefault();
        commentIndex++;

        var html = '<div class="comment-item">';
        html += '<input type="hidden" name="comments['+commentIndex+'][id]" value="">';
        html += '<div class="form-group">';
        html += '   <textarea class="form-control" name="comments['+commentIndex+'][comment]" placeholder="Type comment..."></textarea>';
        html += '</div>';

        html += '<div class="col-12 text-right">';
        html += '   <button type="button" class="btn btn-danger btn-sm removeComment">Remove Comment</button>';
        html += '</div>';

        html += '<hr></div>';

        $('#commentForm').append(html);

        $('textarea[name="comments['+commentIndex+'][comment]"]').rules('add', { required: true });
    });
    $(document).on('click', '.removeComment', function (e) {
        e.preventDefault();
        $(this).parents('.comment-item').remove();

    });
});


var autocomplete;
var autocomplete2;

var componentForm = {
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
    autocomplete2 = new google.maps.places.Autocomplete(
        document.getElementById('googleAddress2'), {types: ['geocode']});

    // Avoid paying for data that you don't need by restricting the set of
    // place fields that are returned to just the address components.
    autocomplete.setFields(['address_components']);
    autocomplete2.setFields(['address_components']);

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
