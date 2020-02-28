jQuery(document).ready(function() {

    var rules = {
        'site': {
            required: true
        },
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
        }
    };

    $('#selectSite').select2({
        placeholder: "Select a Site",
        allowClear: true,
        tags: false
    });

    $(document).on('change', '#billingValueInput', function () {
        if($(this).val() < 0){
            $(this).val(0);
        }
    });

    var currentTotalCost = null;
    var percentage = null;
    $(document).on('change', '#proposedLeaseInput', function () {
        var savingAmount = null;
        var savingPercentage = null;
        if($(this).val() > 0){
            var proposedLease = Math.round(parseFloat($(this).val()) * 100) / 100;
            savingAmount = Math.round((proposedLease - currentTotalCost) * 100) / 100;
            savingPercentage = Math.round(((savingAmount/proposedLease)*100) * 100) / 100;
            if($('#feeTypeSelect').val() == 2){
                var feeValue = parseFloat($('#feePercentageInput').val());
                $('#billingValueInput').val(Math.round((savingAmount*feeValue)/100)).change();
            }
        }
        $('#savingAmountInput').val(savingAmount);
        $('#savingPercentageInput').val(savingPercentage);
    });

    $(document).on('change', '#selectSite', function () {
        var siteStatusValue = null;
        var siteStatusUpdated = null;
        var leaseId = $(this).val();
        var notes = [];
        var proposedLease = null;
        var escalationSaving = null;
        if($(this).val()){
            leaseData.forEach(function (item) {
               if(item.leaseId == leaseId){
                   siteStatusValue = item.siteStatusId;
                   siteStatusUpdated = item.siteStatusUpdated;
                   notes = item.notes;
                   if(item.proposedLease > 0){
                       proposedLease = item.proposedLease;
                   }
                   currentTotalCost = item.currentTotalCost;
                   percentage = item.percentage;
                   if(item.escalationSaving > 0){
                       escalationSaving = item.escalationSaving;
                   }
               }
            });
            $('.agent-fields').show();
        }
        else{
            $('.agent-fields').hide();
        }

        $('#siteStatusSelect').val(siteStatusValue).change();
        $('#siteStatusUpdatedSpan').html(siteStatusUpdated);
        if(escalationSaving){
            $('#escalationSavingInput').val(escalationSaving+'%');
        }
        else{
            $('#escalationSavingInput').val(escalationSaving);
        }
        $('#proposedLeaseInput').val(proposedLease).change();

        var feeType = $('#feeTypeSelect').val();
        if(feeType == 1){
            var feeValue = parseFloat($('#feePercentageInput').val());
            $('#billingValueInput').val(Math.round(((currentTotalCost*feeValue)/100) * 100) / 100).change();
        }
        else if(feeType == 3){
            $('#billingValueInput').val(parseFloat($('#feeAmountInput').val())).change();
        }
        else if(!feeType){
            $('#billingValueInput').val('');
        }

        notesIndex = notes.length;
        if(notes.length>0){
            notes.forEach(function (note) {
                addNotes(note);
            });
        }
        else{
            $('.notes-items').html('');
        }
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
            $('#billingValueInput').val(Math.round((savingAmount*feeValue)/100)).change();
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
            $('#billingValueInput').val(Math.round((savingAmount*feeValue)/100)).change();
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
        rules['notes['+notesIndex+']'] = {
            required: true
        };
    }

    $(document).on('click', '#btnAddNotes', function (e) {
        e.preventDefault();

        addNotes();
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

    var validator = $('#agentForm').validate({
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

    function addNotes(note=[]) {
        notesIndex++;
        var id = '';
        if(note.id !== undefined){
            id = note.id;
        }
        var text = '';
        if(note.text !== undefined){
            text = note.text;
        }
        var updated = '';
        if(note.updated !== undefined){
            updated = note.updated;
        }

        var html = '<div class="notes-item">';
        html += '   <input type="hidden" name="notes['+notesIndex+'][id]" value="'+id+'">';
        html += '   <div class="form-group">';
        html += '       <textarea class="form-control" name="notes['+notesIndex+'][text]" placeholder="Type notes...">'+text+'</textarea>';
        html += '   </div>';

        if(updated){
            html += '<div class="row">';
            html += '   <div class="col-sm-6">';
            html += '       <label class="btn-label">';
            html += '           Date of note:';
            html += '           <span class="font-weight-bold">'+updated+'</span>';
            html += '       </label>';
            html += '   </div>';
            html += '   <div class="col-sm-6 text-right">';
            html += '       <button type="button" class="btn btn-danger btn-sm removeNotes">Remove Notes</button>';
            html += '   </div>';
            html += '</div>';
        }
        else{
            html += '   <div class="col-12 text-right">';
            html += '       <button type="button" class="btn btn-danger btn-sm removeNotes">Remove Notes</button>';
            html += '   </div>';
        }


        html += '   <hr>';
        html += '</div>';


        $('.notes-items').append(html);

        $('textarea[name="notes['+notesIndex+'][text]"]').rules('add', { required: true });
    }
});
