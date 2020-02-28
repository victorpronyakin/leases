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

    /* START STEP 1 BASIC SETTING */
    var rulesStep1 = {
        'site[siteStatus]': {
            required: true
        },
        'lease[landlord]': {
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
            required: true
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

        'lease[terminateDate]': {
            required: {
                depends: function (element) {
                    return ($("#leaseTerminateStatus").is(':checked'));
                }
            }
        },
    };

    //ADD COMMENT RULES
    for (var i=0; i < notesIndex; i++){
        rulesStep1['notes['+notesIndex+'][text]'] = {
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
        html += '   <div class="col-12 text-right">';
        html += '       <button type="button" class="btn btn-danger btn-sm removeNotes">Remove Notes</button>';
        html += '   </div>';
        html += '</div>';


        $('.notes-items').append(html);

        $('textarea[name="notes['+notesIndex+'][text]"]').rules('add', { required: true });
    });

    $(document).on('click', '.removeNotes', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.notes-item');
        parent.remove();
    });

    $('#formStep1').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesStep1,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
    });

    $('#kt_select2__landlord').select2({
        placeholder: "Select a Landlord",
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

    $('#lease_terminateDate').datepicker({
        todayHighlight: true,
        orientation: "top left",
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
    /* END STEP 1 BASIC SETTING */

    /* START STEP 2 */

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
    });

    $(document).on('change', '#annualEscalationFixed', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
        else if ($(this).val() >= 100){
            $(this).val(100);
        }
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

    var rulesStep2 = {
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
    };

    $('#formStep2').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesStep2,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
    });
    /* END STEP 2*/


    /* START STEP 4 */

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

    var rulesStep4 = {
        'lease[documentStatus]': {
            required: true
        },
    };

    for (var i=0; i < documentIndex; i++){
        rulesStep4['document['+i+'][type]'] = {
            required: true
        };
        rulesStep4['document['+i+'][document]'] = {
            required: true
        };
    }

    $('#formStep4').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesStep4,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
        submitHandler: function(form){
            var error = true;
            $('.documentInput').each(function (index, item) {
                if($(item).val() || $(item).prev().val()){
                    $(item).next().next().next().hide();
                }
                else{
                    $(item).next().next().next().show();
                    error = false;
                }
            });

            if(error){
                form.submit();
            }
        }
    });

    /* END STEP 4 */

    /* START STEP 5 */

    var tableOpenIssue = $('#tableIssueOpen').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        bSort: true,
        aaSorting: [],
        pageLength: 25,
        columnDefs: [
            {
                targets: -1,
                title: 'Actions',
                orderable: false,

            },
        ]
    });

    var tableCloseIssue = $('#tableIssueClose').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        bSort: true,
        aaSorting: [],
        pageLength: 25
    });



    $(document).on('click', '.doneIssueBtn', function (e) {
        e.preventDefault();
        var el = $(this);
        var parent = el.parents('tr');
        var id = parent.attr('data-id');
        if(id){
            $.ajax({
                type: 'POST',
                url: "/issue/change_status/"+id,
                data: {
                    status: false
                },
                success: function (data) {
                    if (data.result === true) {
                        toastr.success('Issue has been closed');
                        var row = tableOpenIssue.row(parent);
                        var rowNode = row.node();
                        row.remove().draw();
                        $(rowNode).find('td.text-center').remove();
                        tableCloseIssue.row.add(rowNode).draw();
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong. Try again later!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }
    });

    /* END STEP 5 */

    /* START STEP 6 */

    var rulesStep6Inventory = {};

    for (var i=0; i <= siteInventoryIndex; i++){
        rulesStep6Inventory['siteInventory['+i+'][category]'] = {
            required: true
        };
        rulesStep6Inventory['siteInventory['+i+'][quantity]'] = {
            required: true,
            min: 1
        };
    }

    $('#formStep6Inventory').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesStep6Inventory,
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
        html += '   <input type="hidden" value="" name="siteInventory['+siteInventoryIndex+'][id]">';
        html += '   <div class="form-group row ">';
        html += '       <label class="col-2 col-form-label">Category</label>';
        html += '       <div class="col-10"><select class="form-control" name="siteInventory['+siteInventoryIndex+'][category]">';
        html += '           <option value="">Select a Inventory Category</option>';
        siteInventoryCategories.forEach(function (item) {
            html += '       <option value="'+item.id+'">'+item.name+'</option>';
        });
        html += '       </select></div>';
        html += '   </div>';
        html += '   <div class="form-group row">';
        html += '       <label class="col-2 col-form-label">Quantity</label>';
        html += '       <div class="col-10"><input type="number" name="siteInventory['+siteInventoryIndex+'][quantity]" value="1" class="form-control siteInventoryQuantity" placeholder="Quantity"></div>';
        html += '   </div>';
        html += '   <div class="form-group row">';
        html += '       <label class="col-2 col-form-label">Size</label>';
        html += '       <div class="col-10"><input type="number" name="siteInventory['+siteInventoryIndex+'][size]" step="0.01" value="" class="form-control siteInventorySize" placeholder="Size (mm)"></div>';
        html += '   </div>';
        html += '   <div class="form-group row">';
        html += '       <label class="col-2 col-form-label">Height</label>';
        html += '       <div class="col-10"><input type="number" name="siteInventory['+siteInventoryIndex+'][height]" step="0.01" value="" class="form-control siteInventoryHeight" placeholder="Height (m)"></div>';
        html += '   </div>';
        html += '   <div class="form-group row">';
        html += '       <label class="col-2 col-form-label">Weight</label>';
        html += '       <div class="col-10"><input type="number" name="siteInventory['+siteInventoryIndex+'][weight]" step="0.01" value="" class="form-control siteInventoryWeight" placeholder="Weight (kg)"></div>';
        html += '   </div>';
        html += '   <div class="form-group row">';
        html += '       <label class="col-2 col-form-label">Additional info</label>';
        html += '       <div class="col-10"><textarea class="form-control" name="siteInventory['+siteInventoryIndex+'][info]" rows="3" placeholder="Additional info"></textarea></div>';
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

    var rulesStep6EmergencyAccess = {
        'site[hoursOfAccess]': {
            required: true,
        },
        'site[primaryEmergencyContact]': {
            required: true
        }
    };

    $('#formStep6EmergencyAccess').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesStep6EmergencyAccess,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        }
    });

    $('#site_emergencyAccessUpdated').datepicker({
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
            $('#site_emergencyAccessUpdated').prop('disabled', false);
        }
        else{
            $('#site_emergencyAccessUpdated').prop('disabled', true);
        }
    });



    $(document).on('shown.bs.modal', '#modalAddContact', function () {
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
            $(this).parents('div').find('.invalid-feedback').hide();
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

                        $('#modalAddContact').modal('hide');
                        toastr.success('Contact has been added');
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

    /* END STEP 6 */

    /* START Addendum */
    $('#leaseRentalCost_category').select2({
        placeholder: "Select a Rental Costs Category",
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
                    action: 'editable'
                },
                success: function (data) {
                    if (data.result === true) {

                        el.find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+data.id+'">'+data.name+'</option>');
                        $('#leaseRentalCostEdit_category').append('<option value="'+data.id+'">'+data.name+'</option>');
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

    $('#leaseRentalCost_startDate').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $(document).on('click', '#openModalAddAddendum', function () {
        $('#leaseRentalCost_category').val('').change();
        $('#leaseRentalCost_amount').val('').change();
        $('#leaseRentalCost_startDate').datepicker("setDate", $('#lease_startDate').datepicker('getDate'));
        $('#modalAddAddendum').modal('show');
    });

    var rulesAddAddendum = {
        'category': {
            required: true
        },
        'amount': {
            required: true
        },
        'startDate': {
            required: true
        },
    };

    $('#formAddAddendum').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesAddAddendum,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
        submitHandler: function(form) {
            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: $(form).serializeArray(),
                success: function (data) {
                    if (data.result === true) {
                        var html = '<tr data-id="'+data.id+'">';
                        html += '   <td data-category-id="'+data.category.id+'">'+data.category.name+'</td>';
                        html += '   <td>'+currencySymbol+' '+data.amount+'</td>';
                        html += '   <td>'+data.startDate+'</td>';
                        html += '   <td>'+currencySymbol+' '+data.currentAmount+'</td>';
                        html += '   <td>';
                        html += '       <a href="#" class="btn btn-clean btn-sm btn-icon btn-icon-sm editAddendumBtn"><i class="fa fa-pencil-alt"></i></a>';
                        html += '       <a href="#" class="btn btn-clean btn-sm btn-icon btn-icon-sm removeAddendumBtn"><i class="text-danger fa fa-times"></i></a>';
                        html += '   </td>';
                        html += '</tr>';
                        $('#totalMonthlyRentalCostTR').before(html);

                        if(data.totalStatus == true){
                            var monthlyTotal = getMonthlyTotal();
                            monthlyTotal = monthlyTotal + parseFloat(data.amount);
                            setMonthlyTotal(monthlyTotal);

                            var monthlyCurrentTotal = getCurrentMonthlyTotal();
                            monthlyCurrentTotal = monthlyCurrentTotal + parseFloat(data.currentAmount);
                            setCurrentMonthlyTotal(monthlyCurrentTotal);
                        }

                        $('#modalAddAddendum').modal('hide');
                        toastr.success('Addendum has been added!');
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Addendum has not been added!');
                    }
                }
            });
        }
    });

    $('#leaseRentalCostEdit_category').select2({
        placeholder: "Select a Rental Costs Category",
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
                    action: 'editable'
                },
                success: function (data) {
                    if (data.result === true) {

                        el.find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+data.id+'">'+data.name+'</option>');
                        $('#leaseRentalCost_category').append('<option value="'+data.id+'">'+data.name+'</option>');
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

    $('#leaseRentalCostEdit_startDate').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $(document).on('click', '.editAddendumBtn', function (e) {
        e.preventDefault();
        var parent = $(this).parents('tr');
        var addendumId = parent.attr('data-id');
        var tds = parent.find('td');

        $('#formEditAddendum_Id').val(addendumId).change();
        $('#leaseRentalCostEdit_category').val($(tds[0]).attr('data-category-id')).change();
        $('#leaseRentalCostEdit_amount').val($(tds[1]).html().substring(2)).change();
        $('#leaseRentalCostEdit_startDate').datepicker("setDate", $(tds[2]).html());

        $('#modalEditAddendum').modal('show');
    });

    $('#formEditAddendum').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: rulesAddAddendum,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        },
        submitHandler: function(form) {
            var addendumId = $('#formEditAddendum_Id').val();
            if(addendumId){
                $.ajax({
                    type: 'POST',
                    url: '/lease/addendum/edit/'+$('#formEditAddendum_Id').val(),
                    data: $(form).serializeArray(),
                    success: function (data) {
                        if (data.result === true) {
                            var tr = $('#tableRentalCost').find('tr[data-id="'+addendumId+'"]');
                            var tds = tr.find('td');
                            var oldAmount = parseFloat($(tds[1]).html().substring(2));
                            var oldCurrentAmount = parseFloat($(tds[3]).html().substring(2));

                            $(tds[0]).attr('data-category-id', data.category.id);
                            $(tds[0]).html(data.category.name);
                            $(tds[1]).html(currencySymbol+' '+data.amount);
                            $(tds[2]).html(data.startDate);
                            $(tds[3]).html(currencySymbol+' '+data.currentAmount);

                            if(tr.attr('data-initial') == 1){
                                var initialTotal = getInitialTotal();
                                var currentInitialTotal = getCurrentInitialTotal();
                                if(data.totalStatus == true){
                                    initialTotal = initialTotal + parseFloat(data.amount);
                                    currentInitialTotal = currentInitialTotal + parseFloat(data.currentAmount);
                                }
                                if(data.oldTotalStatus == true){
                                    initialTotal = initialTotal - oldAmount;
                                    currentInitialTotal = currentInitialTotal - oldCurrentAmount;
                                }
                                setInitialTotal(initialTotal);
                                setCurrentInitialTotal(currentInitialTotal);
                            }

                            var monthlyTotal = getMonthlyTotal();
                            var monthlyCurrentTotal = getCurrentMonthlyTotal();
                            if(data.totalStatus == true){
                                monthlyTotal = monthlyTotal + parseFloat(data.amount);
                                monthlyCurrentTotal = monthlyCurrentTotal + parseFloat(data.currentAmount);
                            }
                            if(data.oldTotalStatus == true){
                                monthlyTotal = monthlyTotal - oldAmount;
                                monthlyCurrentTotal = monthlyCurrentTotal - oldCurrentAmount;
                            }
                            setMonthlyTotal(monthlyTotal);
                            setCurrentMonthlyTotal(monthlyCurrentTotal);

                            $('#modalEditAddendum').modal('hide');
                            toastr.success('Addendum has been edit!');
                        }
                        else if (data.message !== undefined){
                            toastr.error(data.message);
                        }
                        else{
                            toastr.error('Addendum has not been edited!');
                        }
                    }
                });
            }

        }
    });

    $(document).on('click', '.removeAddendumBtn', function (e) {
        e.preventDefault();
        var parent = $(this).parents('tr');
        var addendumId = parent.attr('data-id');
        if(addendumId){
            $.ajax({
                type: 'POST',
                url: '/lease/addendum/remove/'+addendumId,
                data: {},
                success: function (data) {
                    if (data.result === true) {
                        var tds = parent.find('td');
                        var amount = parseFloat($(tds[1]).html().substring(2));
                        var currentAmount = parseFloat($(tds[3]).html().substring(2));

                        if(parent.attr('data-initial') == 1){
                            var initialTotal = getInitialTotal();
                            var currentInitialTotal = getCurrentInitialTotal();
                            if(data.oldTotalStatus == true){
                                initialTotal = initialTotal - amount;
                                currentInitialTotal = currentInitialTotal - currentAmount;
                            }
                            setInitialTotal(initialTotal);
                            setCurrentInitialTotal(currentInitialTotal);
                        }

                        var monthlyTotal = getMonthlyTotal();
                        var monthlyCurrentTotal = getCurrentMonthlyTotal();
                        if(data.oldTotalStatus == true){
                            monthlyTotal = monthlyTotal - amount;
                            monthlyCurrentTotal = monthlyCurrentTotal - currentAmount  ;
                        }
                        setMonthlyTotal(monthlyTotal);
                        setCurrentMonthlyTotal(monthlyCurrentTotal);

                        parent.remove();
                        toastr.success('Addendum has been removed!');
                    }
                    else if (data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Addendum has not been removed!');
                    }
                }
            });
        }
        else{
            toastr.error('Addendum has not been removed!');
        }
    });

    function getInitialTotal() {
        return parseFloat($('#initialTotal').html())
    }

    function setInitialTotal(initialTotal) {
        $('#initialTotal').html(Math.round((parseFloat(initialTotal) * 100)) / 100);
    }

    function getCurrentInitialTotal() {
        return parseFloat($('#initialCurrentTotal').html())
    }

    function setCurrentInitialTotal(currentInitialTotal) {
        $('#initialCurrentTotal').html(Math.round((parseFloat(currentInitialTotal) * 100)) / 100);
    }

    function getMonthlyTotal() {
        return parseFloat($('#monthlyTotal').html())
    }

    function setMonthlyTotal(monthlyTotal) {
        $('#monthlyTotal').html(Math.round((parseFloat(monthlyTotal) * 100)) / 100);
    }

    function getCurrentMonthlyTotal() {
        return parseFloat($('#monthlyCurrentTotal').html())
    }

    function setCurrentMonthlyTotal(monthlyCurrentTotal) {
        $('#monthlyCurrentTotal').html(Math.round((parseFloat(monthlyCurrentTotal) * 100)) / 100);
    }

    /* END ADDENDUM */
});
