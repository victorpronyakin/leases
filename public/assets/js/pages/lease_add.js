jQuery(document).ready(function() {

    jQuery.validator.addMethod("greaterThan",
        function(value, element, params) {
            var startDate = $('#lease_startDate').datepicker('getDate');
            var endDate = $('#lease_endDate').datepicker('getDate');
            var diff = endDate-startDate;
            if(diff > 0){
                $('#endDate-error').hide();
                return true;
            }
            else{
                $('#endDate-error').show();
                return false;
            }
        },
        'Must be greater than Commencement Date.');

    var rules = {
        //STEP 1
        'lease[landlord]': {
            required: true
        },
        'lease[site]': {
            required: true
        },
        'lease[type][]': {
            required: true
        },
        'lease[startDate]': {
            required: true,
        },
        'lease[endDate]': {
            required: true,
            greaterThan: "#lease_startDate"
        },
        'lease[term]': {
            required: true,
        },
        'lease[renewal]': {
            required: {
                depends: function (element) {
                    return ($("#leaseRenewalStatus").is(":checked"));
                }
            },
        },
        'lease[terminationClause]': {
            required: {
                depends: function (element) {
                    return ($("#leaseTerminationClauseStatus").is(":checked"));
                }
            },
        },

        //STEP 2
        'lease[electricityType]': {
            required: true
        },
        'lease[electricityFixed]': {
            required: {
                depends: function (element) {
                    return ($("#electricityTypeSelect").val() && $("#electricityTypeSelect").val() == 3);
                }
            }
        },
        'lease[frequencyOfLeasePayments]': {
            required: true
        },
        'lease[annualEscalationType]': {
            required: true
        },
        'lease[annualEscalation]': {
            required: true
        },
        'lease[annualEscalationCpi]': {
            required: true
        },
        'lease[annualEscalationDate]': {
            required: true
        },
        'lease[depositType]': {
            required: {
                depends: function (element) {
                    return ($("#leaseDepositStatus").is(':checked'));
                }
            }
        },
        'lease[depositAmount]': {
            required: {
                depends: function (element) {
                    return ($("#leaseDepositStatus").is(':checked'));
                }
            }
        },

        //STEP 3
        'lease[documentStatus]': {
            required: true
        },

        //STEP 4
        'lease[terminateDate]': {
            required: {
                depends: function (element) {
                    return ($("#leaseTerminateStatus").is(':checked'));
                }
            }
        },
        'lease[allocated]': {
            required: true
        },
        'lease[fee]': {
            required: true
        },
        'lease[feeValue1]': {
            required: true
        },
        'lease[feeValue2]': {
            required: true
        },
        'lease[feeDuration]': {
            required: true
        },
        'lease[feeEscalation]': {
            required: true
        },
        'lease[proposedLease]': {
            required: true
        },
    };


    /* START STEP 1 BASIC SETTING */
    $('#kt_select2__landlord').select2({
        placeholder: "Select a Landlord",
        allowClear: true,
        tags: false
    });

    $('#kt_select2__site').select2({
        placeholder: "Select a Site",
        allowClear: true,
        tags: false
    });

    $('#kt_select2__type').select2({
        placeholder: "Select a Lease Type",
        multiple: true,
    });

    $('#kt_select2__type').val(leaseTypeValue).change();

    $('#lease_startDate').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $('#lease_endDate').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $(document).on('change', '#lease_startDate, #lease_endDate', function () {
        var startDate = $('#lease_startDate').datepicker('getDate');
        var endDate = $('#lease_endDate').datepicker('getDate');
        if(startDate && endDate){
            var month = monthDiff(startDate, endDate);
            if(month >= 0){
                $('input[name="lease[term]"]').val(month).change();
            }
            else{
                $('input[name="lease[term]"]').val('').change();
            }
        }
    });

    function monthDiff(dateFrom, dateTo) {
        var month = dateTo.getMonth() - dateFrom.getMonth() + (12 * (dateTo.getFullYear() - dateFrom.getFullYear()));
        return month;
    }

    $(document).on('change', '#leaseRenewalStatus', function () {
        if($(this).is(':checked')){
            $('#leaseRenewalOptional').show();
        }
        else{
            $('#leaseRenewalOptional').hide();
        }
    });

    $(document).on('change', '#leaseTerminationClauseStatus', function () {
        if($(this).is(':checked')){
            $('#terminationClauseOptional').show();
        }
        else{
            $('#terminationClauseOptional').hide();
        }
    });

    var oldPercentage = null;

    $(document).on('change', '#kt_select2__site', function () {
        if($(this).val()){
            var siteId = $(this).val();
            $.ajax({
                type: 'GET',
                url: "/lease/agentDetails/"+siteId,
                data: {},
                success: function (data) {
                    if (data.result === true) {
                        oldPercentage = data.oldPercentage;
                        $('#proposedLeaseInput').val(data.proposedLease).change();
                    }
                    else{
                        oldPercentage = null;
                        $('#proposedLeaseInput').val(null).change();
                    }
                }
            });
        }
        else{
            oldPercentage = null;
            $('#proposedLeaseInput').val(null).change();
        }
    });

    for (var i=0; i < notesIndex; i++){
        rules['notes['+notesIndex+'][text]'] = {
            required: true
        };
    }

    $(document).on('click', '#btnAddNotes', function (e) {
        e.preventDefault();
        notesIndex++;

        var html = '<div class="notes-item mb-3">';
        html += '   <input type="hidden" name="notes['+notesIndex+'][id]" value="">';
        html += '   <div class="form-group mb-2">';
        html += '       <textarea class="form-control" name="notes['+notesIndex+'][text]" placeholder="Type notes..."></textarea>';
        html += '   </div>';
        html += '   <div class="row"><div class="col-12 text-right">';
        html += '       <button type="button" class="btn btn-danger btn-sm removeNotes">Remove Notes</button>';
        html += '   </div></div>';
        html += '</div>';

        $('.notes-items').append(html);

        $('textarea[name="notes['+notesIndex+'][text]"]').rules('add', { required: true });
    });

    $(document).on('click', '.removeNotes', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.notes-item');
        parent.remove();
    });

    /* END STEP 1 BASIC SETTING */

    /* START STEP 2 BASIC SETTING */
    for (var i=0; i <= rentalCostIndex; i++){
        rules['leaseRentalCost['+i+'][category]'] = {
            required: true
        };
        rules['leaseRentalCost['+i+'][amount]'] = {
            required: true
        };
        rules['leaseRentalCost['+i+'][startDate]'] = {
            required: true
        };

        $('#kt_select2_leaseRentalCost_category_'+i).select2({
            placeholder: "Select a Monthly Rental Costs",
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
                    url: "/lease/createRentalCategory",
                    data: {
                        name: e.params.data.text,
                        action: 'added'
                    },
                    success: function (data) {
                        if (data.result === true) {

                            el.find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+data.id+'">'+data.name+'</option>');
                            $('.kt-select2.rentalCategory').each(function () {
                                if($(this).find('option[value="'+data.id+'"]').length === 0){
                                    $(this).append('<option value="'+data.id+'">'+data.name+'</option>');
                                }
                            });
                            rentalCostCategories.push({
                                id: data.id,
                                name: data.name,
                            });
                        }
                        else{
                            if(data.message !== undefined){
                                toastr.error(data.message);
                            }
                            else{
                                toastr.error('Rental Costs not create');
                            }
                            el.find('[value="'+e.params.data.id+'"]').remove();
                        }
                    }
                });
            }
        });
        $('#leaseRentalCost_startDate_'+i).datepicker({
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            },
            format: 'dd M yyyy',
        });
    }

    $(document).on('click', '#addLeaseRentalCost', function (e) {
        e.preventDefault();
        rentalCostIndex++;

        var html = '<div class="lease-commercial-item">';
        html += '   <input type="hidden" value="" name="leaseRentalCost['+rentalCostIndex+'][id]">';
        html += '   <div class="form-group">';
        html += '       <label>Monthly Rental Costs</label>';
        html += '       <select name="leaseRentalCost['+rentalCostIndex+'][category]" id="kt_select2_leaseRentalCost_category_'+rentalCostIndex+'" class="form-control kt-select2 rentalCategory" style="width: 100%;">';
        html += '           <option></option>';
        rentalCostCategories.forEach(function (item) {
            html += '           <option value="'+item.id+'">'+item.name+'</option>';
        });
        html += '       </select>';
        html += '       <div class="invalid-feedback">This field is required.</div>';
        html += '    </div>';
        html += '<div class="form-group rentalAmountDiv" style="display: none;">';
        html += '   <label>Rental Amount</label>';
        html += '   <div class="input-group">';
        html += '       <div class="input-group-prepend"><span class="input-group-text">'+currencySymbol+'</span></div>';
        html += '       <input type="number" class="form-control leaseRentalCostAmount" name="leaseRentalCost['+rentalCostIndex+'][amount]" placeholder="Rental Amount" value="">';
        html += '   </div>';
        html += '</div>';
        html += '    <div class="form-group rentalDateDiv" style="display: none;">';
        html += '       <label>Rental Cost Starting Date</label>';
        html += '       <div class="input-group date">';
        html += '           <input type="text" class="form-control rentalDateInput" name="leaseRentalCost['+rentalCostIndex+'][startDate]"  id="leaseRentalCost_startDate_'+rentalCostIndex+'"  value="" readonly placeholder="Select date"/>';
        html += '           <div class="input-group-append">';
        html += '               <span class="input-group-text"><i class="la la-calendar-o"></i></span>';
        html += '           </div>';
        html += '           <div class="invalid-feedback">This field is required.</div>';
        html += '       </div>';
        html += '   </div>';
        html += '   <div class="col-12 text-right">';
        html += '       <button type="button" class="btn btn-danger btn-sm removeLeaseRentalCost">Remove</button>';
        html += '   </div>';
        html += '   <hr></div>';


        $('#leaseCommercialForms').append(html);

        $('#kt_select2_leaseRentalCost_category_'+rentalCostIndex).select2({
            placeholder: "Select a Monthly Rental Costs",
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
                    url: "/lease/createRentalCategory",
                    data: {
                        name: e.params.data.text,
                        action: 'added'
                    },
                    success: function (data) {
                        if (data.result === true) {

                            el.find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+data.id+'">'+data.name+'</option>');
                            $('.kt-select2.rentalCategory').each(function () {
                                if($(this).find('option[value="'+data.id+'"]').length === 0){
                                    $(this).append('<option value="'+data.id+'">'+data.name+'</option>');
                                }
                            });
                            rentalCostCategories.push({
                                id: data.id,
                                name: data.name,
                            });
                        }
                        else{
                            if(data.message !== undefined){
                                toastr.error(data.message);
                            }
                            else{
                                toastr.error('Rental Costs not create');
                            }
                            el.find('[value="'+e.params.data.id+'"]').remove();
                        }
                    }
                });
            }
        });
        $('#leaseRentalCost_startDate_'+rentalCostIndex).datepicker({
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            },
            format: 'dd M yyyy',
            defaultDate: $('#lease_startDate').datepicker('getDate')
        });

        $('select[name="leaseRentalCost['+rentalCostIndex+'][category]"]').rules('add', { required: true });
        $('input[name="leaseRentalCost['+rentalCostIndex+'][amount]"]').rules('add', { required: true });
        $('input[name="leaseRentalCost['+rentalCostIndex+'][startDate]"]').rules('add', { required: true });
    });

    $(document).on('click', '.removeLeaseRentalCost', function (e) {
        e.preventDefault();

        if($('.lease-commercial-item').length > 1){
            $(this).parents('.lease-commercial-item').remove();

            fillAgentData();
        }
        else{
            toastr.error('You can not remove last items');
        }
    });

    $(document).on('change', '.rentalCategory', function () {
        var parent = $(this).parents('.lease-commercial-item');
        var amountDiv = parent.find('.rentalAmountDiv');
        var amountInput = parent.find('.leaseRentalCostAmount');
        var dateDiv = parent.find('.rentalDateDiv');
        var dateInput = parent.find('.rentalDateInput');
        if($(this).val()){
            amountDiv.show();
            dateDiv.show();
            if(dateInput.val()){}
            else{
                dateInput.datepicker('setDate', $('#lease_startDate').datepicker('getDate'));
            }
        }
        else{
            amountDiv.hide();
            dateDiv.hide();
            amountInput.val('').change();
            dateInput.val('').change();
        }
    });

    $(document).on('change', '.leaseRentalCostAmount', function () {
        fillAgentData();
    });

    $(document).on('change', '#electricityTypeSelect', function () {
        if($(this).val() == 3){
            $('#electricityFixedInput').show();
        }
        else{
            $('#electricityFixedInput').hide();
        }
    });

    $('#kt_select2_otherUtilityCost').select2({
        placeholder: "Select Other Utility Costs",
        multiple: true,
        closeOnSelect: false
    });
    $('#kt_select2_otherUtilityCost').val(otherUtilityCostValue).change();

    $(document).on('change', '#annualEscalationTypeSelect', function () {
        if($(this).val() == 'cpi'){
            $('#annualEscalationFixed').hide();
            $('#annualEscalationCpi').show();

        }
        else{
            $('#annualEscalationCpi').hide();
            $('#annualEscalationFixed').show();
        }

        fillAgentData();
    });

    $(document).on('change', '#annualEscalationFixed', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
        else if ($(this).val() >= 100){
            $(this).val(100);
        }

        fillAgentData()
    });

    $(document).on('change', '#annualEscalationCpiSelect', function () {
        fillAgentData()
    });

    $(document).on('change', '#leaseDepositStatus', function () {
        if($(this).is(':checked')){
            $('.deposit-fields').show();
        }
        else{
            $('.deposit-fields').hide();
        }
    });

    $(document).on('change', '#depositAmountValue', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
    });
    /* END STEP 2 BASIC SETTING */

    /* START STEP 3 BASIC SETTING */
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
        html += '           <input type="hidden"  class="upload-file-container__fake-upload" name="document['+documentIndex+'][document]" value="">';
        html += '           <input  type="file" name="document['+documentIndex+'][document]" value="" hidden="hidden" class="documentInput upload-file-container__custom-upload">';
        html += '           <button class="upload-file-container__upload-button btn btn-sm btn-info">';
        html += '               <i class="fas fa-plus-circle"></i><span>Choose a file</span>';
        html += '           </button>';
        html += '           <div class="upload-file-container__file-uploaded">';
        html += '               <div class="upload-file-container__close"><i class="fa fa-times"></i></div>';
        html += '               <i class="fas fa-cloud-download-alt"></i>';
        html += '               <span class="upload-file-container__file-name"></span>';
        html += '           <span class="upload-file-container__file-date"></span>';
        html += '           </div>';
        html += '           <div class="text-danger" style="display: none;">This field is required.</div>';
        html += '       </div>';
        html += '   </div>';
        html += '</div>';

        html += '<div class="col-12 text-right">';
        html += '   <button type="button" class="btn btn-danger btn-sm removeDocument">Remove Document</button>';
        html += '</div>';

        html += '<hr></div>';

        $('.document-items').append(html);

        $('select[name="document['+documentIndex+'][type]"]').rules('add', { required: true });
        $('input[name="document['+documentIndex+'][document]"]').rules('add', { required: true });
    });

    $(document).on('click', '.removeDocument', function (e) {
        e.preventDefault();
        $(this).parents('.document-item').remove();
    });

    for (var i=0; i < documentIndex; i++){
        rules['document['+i+'][type]'] = {
            required: true
        };
        rules['document['+i+'][document]'] = {
            required: true
        };
    }

    /* END STEP 3 BASIC SETTING */

    /* START STEP 4 BASIC SETTING */

    $('#lease_terminateDate').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $(document).on('change', '#leaseTerminateStatus', function () {
         if($(this).is(":checked")){
             $('#terminateDateInput').show();
         }
         else{
             $('#terminateDateInput').hide();
         }
    });

    $(document).on('change', '#leaseAgentStatus', function () {
        if($(this).is(":checked")){
            $('.agent_fields').show();
        }
        else{
            $('.agent_fields').hide();
        }
    });


    $(document).on('change', '#proposedLeaseInput', function () {
        fillAgentData();
    });

    $(document).on('change', '#feeTypeSelect', function () {
        if($(this).val() == 1 || $(this).val() == 2){
            $('#feeAmount').hide();
            $('#feePercentage').show();
        }
        else if($(this).val() == 3){
            $('#feePercentage').hide();
            $('#feeAmount').show();
        }
        else{
            $('#feePercentage').hide();
            $('#feeAmount').hide();
        }

        fillAgentData();
    });

    $(document).on('change', '#feePercentageInput', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
        else if ($(this).val() >= 100){
            $(this).val(100);
        }

        fillAgentData();
    });

    $(document).on('change', '#feeAmountInput', function () {
        fillAgentData();
    });

    $(document).on('change', '#targetRenewalEscalationInput', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
        else if ($(this).val() >= 100){
            $(this).val(100);
        }
    });

    $(document).on('change', '#feeDurationCheckbox', function () {
        if($(this).is(":checked")){
            $('#feeDurationInput').val('');
            $('#feeDurationInput').prop('disabled', true);
        }
        else{
            $('#feeDurationInput').prop('disabled', false);
        }
    });

    /* END STEP 4 BASIC SETTING */

    var validator = $('#kt_form').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rules,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    $(document).on('click', "#formSubmitBtn", function (e) {
        e.preventDefault();
        var siteId = $('select[name="lease[site]"]').val();
        var startDate = $('#lease_startDate').datepicker({ dateFormat: 'mm M yyyy' }).val();
        $.ajax({
            type: 'POST',
            url: "/lease/checkLease",
            data: {
                siteId: siteId,
                startDate: startDate,
            },
            success: function (data) {
                if (data.result === true) {
                    $('#checkLeaseDate').html(data.previousDate);
                    $('#modalCheckLease').modal('show');
                }
                else if(data.message !== undefined){
                    toastr.error(data.message);
                }
                else{
                    $('#kt_form').submit();
                }
            }
        });
    });

    $(document).on('click', '#checkLeaseBtn', function (e) {
        e.preventDefault();
        var previousDate = $('#checkLeaseDate').html();
        if(previousDate){
            $('#kt_form').append('<input type="hidden" name="previousDate" value="'+previousDate+'" \>');
        }

        $('#kt_form').submit();
    });

    var wizard = new KTWizard('kt_wizard_v3', {
        startStep: 1,
    });
    // Validation before going to next page
    wizard.on('beforeNext', function(wizardObj) {
        if (validator.form() !== true) {
            wizardObj.stop();  // don't go to the next step
        }

        if(wizardObj.currentStep == 5){
            $('.documentInput').each(function (index, item) {
                if($(item).val() || $(item).prev().val()){
                    $(item).next().next().next().hide();
                }
                else{
                    $(item).next().next().next().show();
                    wizardObj.stop();
                }
            });
        }
    });

    wizard.on('beforePrev', function(wizardObj) {
        if (validator.form() !== true) {
            wizardObj.stop();  // don't go to the next step
        }

        if(wizardObj.currentStep == 5){
            $('.documentInput').each(function (index, item) {
                if($(item).val() || $(item).prev().val()){
                    $(item).next().next().next().hide();
                }
                else{
                    $(item).next().next().next().show();
                    wizardObj.stop();
                }
            });
        }
    });
    // Change event
    wizard.on('change', function(wizardObj) {
        KTUtil.scrollTop();
    });

    $(document).on('change', '.input-group.date>input', function () {
        if($(this).val()){
            $(this).next().next().hide();
        }
        else{
            $(this).next().next().show();
        }
    });

    $(document).on('change', '.kt-select2', function () {
        if(typeof $(this).val() === 'object'){
            if($(this).val().length > 0){
                $(this).parent().find('.invalid-feedback').hide();
            }
            else{
                $(this).parent().find('.invalid-feedback').show();
            }
        }
        else{
            if($(this).val()){
                $(this).parent().find('.invalid-feedback').hide();
            }
            else{
                $(this).parent().find('.invalid-feedback').show();
            }
        }
    });

    $(document).on('change', '#billingValueInput', function () {
        if($(this).val() < 0){
            $(this).val(0);
        }
    });

    function getTotalCurrent() {
        var currentTotal = 0;
        $('.leaseRentalCostAmount').each(function () {
            if($(this).val()){
                currentTotal += parseFloat($(this).val());
            }
        });
        return currentTotal;
    }

    function getEscalationSaving() {
        var escalationSaving = 0;
        if(oldPercentage > 0){
            var escalationType = $('#annualEscalationTypeSelect').val();
            if(escalationType == 'percentage'){
                escalationSaving = Math.round((oldPercentage - parseFloat($('#annualEscalationFixedInput').val())) * 100) / 100;
            }
            else if(escalationType == 'cpi'){
                escalationSaving = Math.round((oldPercentage - percentage) * 100) / 100;
            }

        }
        return escalationSaving;
    }

    function fillAgentData() {
        var currentTotalCost = getTotalCurrent();
        var proposedLease = null;
        var savingAmount = null;
        var savingPercentage = null;
        var feeType = $('#feeTypeSelect').val();

        if($('#proposedLeaseInput').val() > 0){
            proposedLease = Math.round(parseFloat($('#proposedLeaseInput').val()) * 100) / 100;
            savingAmount = Math.round((proposedLease - currentTotalCost) * 100) / 100;
            savingPercentage = Math.round(((savingAmount/proposedLease)*100) * 100) / 100;
        }
        $('#savingAmountInput').val(savingAmount);
        $('#savingPercentageInput').val(savingPercentage);

        if(feeType == 1){
            var feeValue = parseFloat($('#feePercentageInput').val());
            $('#billingValueInput').val(Math.round(((currentTotalCost*feeValue)/100) * 100) / 100).change();
        }
        else if(feeType == 2){
            var feeValue = parseFloat($('#feePercentageInput').val());
            $('#billingValueInput').val(Math.round((savingAmount*feeValue)/100)).change();
            // $('#billingValueInput').val(Math.round((((savingAmount*feeValue)/100)+(((savingAmount*feeValue)/100)*(percentage/100))) * 100) / 100).change();
        }
        else if(feeType == 3){
            $('#billingValueInput').val(parseFloat($('#feeAmountInput').val())).change();
        }
        else{
            $('#billingValueInput').val('').change();
        }

        $('#escalationSavingInput').val(getEscalationSaving());

    }

});
