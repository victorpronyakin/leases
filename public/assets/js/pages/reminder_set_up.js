jQuery(document).ready(function() {

    var rules = {
        'type': {
            required: true
        },
        'detail[leaseExpiry]': {
            required: true
        },
        'detail[leaseRenewal]': {
            required: true
        },
        'detail[leaseEscalation]': {
            required: true
        },
        'detail[issue]': {
            required: {
                depends: function (element) {
                    return !$("#issueTypeCheckbox").is(":checked");
                }
            },
        },
        'detail[landlordDocumentStatus]': {
            required: true
        },
        'detail[landlordDocumentDay]': {
            required: true
        },
        'detail[leaseDocumentStatus]': {
            required: true
        },
        'detail[leaseDocumentDay]': {
            required: true
        },
        'detail[siteStatus]': {
            required: true
        },
        'detail[siteDay]': {
            required: true
        },
        'detail[financialStatus]': {
            required: true
        },
        'detail[financialDay]': {
            required: true
        },

        'emails[users][]': {
            required: {
                depends: function (element) {
                    if($('input[name="emails[emails][]"]').val()){
                        return false;
                    }

                    return  true;
                }
            }
        },
        'emails[emails][]': {
            required: {
                depends: function (element) {
                    if($('select[name="emails[users][]"]').val().length > 0){
                        return false;
                    }

                    return  true;
                }
            },
            email: {
                depends: function (element) {
                    if($('select[name="emails[users][]"]').val().length > 0){
                        return false;
                    }

                    return  true;
                }
            }
        }
    };


    $('#reminderForm').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rules,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
        submitHandler: function(form) {
            if($('#dashboardCheckbox').is(':checked') || $('#emailStatusCheckbox').is(':checked')){
                form.submit();
            }
            else{
                toastr.error('Select how should reminder can be delivered!');
            }
        }
    });

    $(document).on('change', '#reminderTypeSelect', function () {
        showAndHideFields();
    });

    $(document).on('change', 'input[name="detail[leaseExpiry]"], input[name="detail[leaseRenewal]"], input[name="detail[leaseEscalation]"], input[name="detail[landlordDocumentDay]"], input[name="detail[leaseDocumentDay]"], input[name="detail[siteDay]"], input[name="detail[financialDay]"]', function () {
        showAndHideFields();
    });

    $(document).on('change', '#issueTypeCheckbox', function () {
        var issueTypeInput = $('#issueTypeInput');
        if($(this).is(':checked')){
            issueTypeInput.prop('disabled', true);
            issueTypeInput.val('').change();
        }
        else{
            issueTypeInput.prop('disabled', false);
        }
        showAndHideFields();
    });

    $(document).on('change', '#issueTypeInput', function () {
        showAndHideFields();
    });

    $(document).on('change', '#landlordDocumentStatusSelect', function () {
        if($(this).val()){
            $('.landlordDocumentStatusDaysDiv').show()
        }
        else{
            $('.landlordDocumentStatusDaysDiv').hide()
        }
        showAndHideFields();
    });

    $(document).on('change', '#leaseDocumentStatusSelect', function () {
        if($(this).val()){
            $('.leaseDocumentStatusDaysDiv').show()
        }
        else{
            $('.leaseDocumentStatusDaysDiv').hide()
        }
        showAndHideFields();
    });

    $(document).on('change', '#siteStatusSelect', function () {
        if($(this).val()){
            $('.siteStatusDaysDiv').show()
        }
        else{
            $('.siteStatusDaysDiv').hide()
        }
        showAndHideFields();
    });

    $(document).on('change', '#financialStatusSelect', function () {
        if($(this).val()){
            $('.financialStatusDaysDiv').show()
        }
        else{
            $('.financialStatusDaysDiv').hide()
        }
        showAndHideFields();
    });

    $('#emailUsers').select2({
        placeholder: "Select a Users",
        multiple: true,
        closeOnSelect: false
    });
    $('#emailUsers').val(emailUsers).change();

    $(document).on('change', '#emailStatusCheckbox', function () {
        if($(this).is(':checked')){
            $('.emailsDiv').show();
        }
        else{
            $('.emailsDiv').hide();
        }
    });

    $(document).on('click', '#addMoreEmails', function (e) {
        e.preventDefault();
        var html = '<div class="emailItem row">';
        html += '   <div class="col-11">';
        html += '       <input type="email" name="emails[emails][]" value="" class="form-control mb-3" placeholder="Input Email Address">';
        html += '   </div>';
        html += '   <div class="col-1">';
        html += '       <a href="#" class="text-danger removeEmails"><i class="la la-remove"></i></a>';
        html += '   </div>';
        html += '</div>';
        $('.emailsInputs').append(html);
    });

    $(document).on('click', '.removeEmails', function (e) {
        e.preventDefault();
        $(this).parents('.emailItem').remove();
    });

    function showAndHideFields(){
        var deliveryDiv = $('.deliveryDiv');
        $('.questionDiv').hide();
        deliveryDiv.hide();
        var reminderType = $('#reminderTypeSelect').val();
        if(reminderType){
            $('.questionDiv'+reminderType).show();
            switch (reminderType) {
                case '1':
                    if($('input[name="detail[leaseExpiry]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '2':
                    if($('input[name="detail[leaseRenewal]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '3':
                    if($('input[name="detail[leaseEscalation]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '4':
                    if($('#issueTypeCheckbox').is(':checked') || $('#issueTypeInput').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '5':
                    if($('#landlordDocumentStatusSelect').val() && $('input[name="detail[landlordDocumentDay]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '6':
                    if($('#leaseDocumentStatusSelect').val() && $('input[name="detail[leaseDocumentDay]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '7':
                    if($('#siteStatusSelect').val() && $('input[name="detail[siteDay]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
                case '8':
                    if($('#financialStatusSelect').val() && $('input[name="detail[financialDay]"]').val()){
                        deliveryDiv.show();
                    }
                    else{
                        deliveryDiv.hide();
                    }
                    break;
            }
        }
    }
});
