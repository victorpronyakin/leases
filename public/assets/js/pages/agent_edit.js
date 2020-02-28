jQuery(document).ready(function() {

    var rules = {
        'siteStatus': {
            required: true
        },
        'allocated': {
            required: true
        },
        'fee': {
            required: true
        },
        'feeValue': {
            required: true
        },
        'feeDuration': {
            required: true
        },
        'feeEscalation': {
            required: true
        },
        'proposedLease': {
            required: true
        },
    };

    $(document).on('change', '#billingValueInput', function () {
        if($(this).val() < 0){
            $(this).val(0);
        }
    });

    $(document).on('change', '#proposedLeaseInput', function () {
        var savingAmount = null;
        var savingPercentage = null;
        if($(this).val() > 0){
            var proposedLease = Math.round(parseFloat($(this).val()) * 100) / 100;
            savingAmount = Math.round((proposedLease - currentTotalCost) * 100) / 100;
            savingPercentage = Math.round(((savingAmount/proposedLease)*100) * 100) / 100;
            if($('#feeTypeSelect').val() == 2){
                var feeValue = parseFloat($('#feePercentageInput').val());
                $('#billingValueInput').val(Math.round((((savingAmount*feeValue)/100)+(((savingAmount*feeValue)/100)*(percentage/100))) * 100) / 100).change();
            }
        }
        $('#savingAmountInput').val(savingAmount);
        $('#savingPercentageInput').val(savingPercentage);
    });

    $(document).on('change', '#feeTypeSelect', function () {
        if($(this).val() == 1){
            $('#feeAmount').hide();
            $('#feePercentage').show();

            var feeValue = parseFloat($('#feePercentageInput').val());
            $('#billingValueInput').val(Math.round(((currentTotalCost*feeValue)/100) * 100) / 100).change();
        }
        else if($(this).val() == 2){
            $('#feeAmount').hide();
            $('#feePercentage').show();

            var feeValue = parseFloat($('#feePercentageInput').val());
            var savingAmount = parseFloat($('#savingAmountInput').val());
            $('#billingValueInput').val(Math.round((((savingAmount*feeValue)/100)+(((savingAmount*feeValue)/100)*(percentage/100))) * 100) / 100).change();
        }
        else if($(this).val() == 3){
            $('#feePercentage').hide();
            $('#feeAmount').show();

            $('#billingValueInput').val(parseFloat($('#feeAmountInput').val())).change();
        }
        else{
            $('#feePercentage').hide();
            $('#feeAmount').hide();

            $('#billingValueInput').val('').change();
        }
    });

    $(document).on('change', '#feePercentageInput', function () {
        var feeValue = $(this).val();
        if(feeValue <= 0){
            feeValue = 0;
        }
        else if (feeValue >= 100){
            feeValue =100;
        }
        $(this).val(feeValue);

        var feeType = $('#feeTypeSelect').val();
        if(feeType == 1){
            $('#billingValueInput').val(Math.round(((currentTotalCost*feeValue)/100) * 100) / 100).change();
        }
        else if (feeType == 2){
            var savingAmount = parseFloat($('#savingAmountInput').val());
            $('#billingValueInput').val(Math.round((((savingAmount*feeValue)/100)+(((savingAmount*feeValue)/100)*(percentage/100))) * 100) / 100).change();
        }
    });

    $(document).on('change', '#feeAmountInput', function () {
        if($('#feeTypeSelect').val() == 3){
            $('#billingValueInput').val(parseFloat($(this).val())).change();
        }
    });

    $(document).on('change', '#targetRenewalEscalationInput', function () {
        if($(this).val() <= 0){
            $(this).val(0);
        }
        else if ($(this).val() >= 100){
            $(this).val(100);
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

        var html = '<div class="notes-item">';
        html += '   <input type="hidden" name="notes['+notesIndex+'][id]" value="">';
        html += '   <div class="form-group">';
        html += '       <textarea class="form-control" name="notes['+notesIndex+'][text]" placeholder="Type notes..."></textarea>';
        html += '   </div>';
        html += '   <div class="col-12 text-right">';
        html += '       <button type="button" class="btn btn-danger btn-sm removeNotes">Remove Notes</button>';
        html += '   </div>';
        html += '   <hr>';
        html += '</div>';


        $('.notes-items').append(html);

        $('textarea[name="notes['+notesIndex+'][text]"]').rules('add', { required: true });
    });

    $(document).on('click', '.removeNotes', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.notes-item');
        parent.remove();
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

    $(document).on('change', '#proposedLeaseCheckbox', function () {
        if($(this).is(':checked')){
            $('#proposedLeaseInput').prop('readonly', false);
        }
        else{
            $('#proposedLeaseInput').prop('readonly', true);
        }
    });

    $('#agentForm').validate({
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
});
