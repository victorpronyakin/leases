jQuery(document).ready(function() {
    handlerSelection();

    $(document).on('click', '.addNewCriteria', function (e) {
        e.preventDefault();
        var type = $(this).attr('data-type');
        var uuid = uuidv4();
        if(type){
            var filterRowHtml = '<tr data-type="'+type+'" data-uuid="'+uuid+'">';
            filterRowHtml += '  <td>';
            filterRowHtml +=        capitalizeFirstLetter(type);
            filterRowHtml += '      <input type="hidden" name="filters['+uuid+'][type]" value="'+type+'">';
            filterRowHtml += '  </td>';
            filterRowHtml += '  <td>';
            filterRowHtml += '      <select name="filters['+uuid+'][name]" class="form-control selectCriteriaField">';
            filterRowHtml += '          <option value="">Select a Field</option>';
            allFields[type].forEach(function (filterField) {
                filterRowHtml += '      <option value="'+filterField.key+'">'+filterField.label+'</option>';
            });
            filterRowHtml += '      </select>';
            filterRowHtml += '  </td>';
            filterRowHtml += '  <td></td>';
            filterRowHtml += '  <td></td>';
            filterRowHtml += '  <td><a href="#" class="btn btn-clean btn-icon removeCriteria"><i class="text-danger la la-times"></i></a></td>';
            filterRowHtml += '</tr>';

            $('.filter-table tbody').append(filterRowHtml);

        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }

        handlerSelection();
    });

    $(document).on('click', '.removeCriteria', function (e) {
        e.preventDefault();
        $(this).parents('tr').remove();
        handlerSelection();
    });

    $(document).on('change', '.selectCriteriaField', function () {
        var filterFieldKey = $(this).val();
        var tr = $(this).parents('tr');
        var type = tr.attr('data-type');
        var uuid = tr.attr('data-uuid');
        var tdOperatorHtml = '';
        if(type && uuid && filterFieldKey){
            var operators = [];
            var fieldType = '';
            allFields[type].forEach(function (filterField) {
                if(filterField['key'] === filterFieldKey){
                    operators = filterField.operators;
                    fieldType = filterField.type;
                }
            });
            tdOperatorHtml += '<select name="filters['+uuid+'][operator]" class="form-control selectCriteriaOperator">';
            tdOperatorHtml += '     <option value="">Select a Operator</option>';
            operators.forEach(function (operatorKey) {
                if(allOperators[operatorKey]){
                    tdOperatorHtml += ' <option value="'+operatorKey+'">'+allOperators[operatorKey]+'</option>';
                }
            });
            tdOperatorHtml += '</select>';
            tdOperatorHtml += '<input type="hidden" name="filters['+uuid+'][fieldType]" value="'+fieldType+'">';
        }

        tr.find('td:eq(2)').html(tdOperatorHtml);
        tr.find('td:eq(3)').html('');

        handlerSelection();
    });

    $(document).on('change', '.selectCriteriaOperator', function () {
        var operatorFieldKey = $(this).val();
        var tr = $(this).parents('tr');
        var type = tr.attr('data-type');
        var uuid = tr.attr('data-uuid');
        var filterFieldKey = tr.find('.selectCriteriaField').val();
        var tdValuesHtml = '';
        if(type && operatorFieldKey && filterFieldKey){
            var fieldObj = '';
            allFields[type].forEach(function (filterField) {
                if(filterField['key'] === filterFieldKey){
                    fieldObj = filterField;
                }
            });
            if(fieldObj['type']){
                switch (fieldObj['type']) {
                    case "list":
                        if(fieldObj['values']){
                            tdValuesHtml += '<div class="row text-left">';
                            tdValuesHtml += '   <div class="col-12">';
                            tdValuesHtml += '       <div class="kt-checkbox-inline">';
                            fieldObj['values'].forEach(function (value) {
                                tdValuesHtml += '           <label class="kt-checkbox kt-checkbox--bold kt-checkbox--brand">';
                                tdValuesHtml += '               <input type="checkbox" name="filters['+uuid+'][value][]" value="'+value.id+'">'+value.name;
                                tdValuesHtml += '               <span></span>';
                                tdValuesHtml += '           </label>';
                            });
                            tdValuesHtml += '       </div>';
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '</div>';
                        }
                        break;
                    case "date":
                        if(operatorFieldKey === 'BETWEEN'){
                            tdValuesHtml += '<div class="row">';
                            tdValuesHtml += '   <div class="col-6">';
                            tdValuesHtml += '       <div class="input-group date">';
                            tdValuesHtml += '           <input type="text" class="form-control fieldValueDate" name=filters['+uuid+'][value][from]" readonly placeholder="Select from date"/>';
                            tdValuesHtml += '               <div class="input-group-append">';
                            tdValuesHtml += '                   <span class="input-group-text"><i class="la la-calendar-o"></i></span>';
                            tdValuesHtml += '               </div>';
                            tdValuesHtml += '       </div>';
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '   <div class="col-6">';
                            tdValuesHtml += '       <div class="input-group date">';
                            tdValuesHtml += '           <input type="text" class="form-control fieldValueDate" name=filters['+uuid+'][value][to]" readonly placeholder="Select to date"/>';
                            tdValuesHtml += '               <div class="input-group-append">';
                            tdValuesHtml += '                   <span class="input-group-text"><i class="la la-calendar-o"></i></span>';
                            tdValuesHtml += '               </div>';
                            tdValuesHtml += '       </div>';
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '</div>';
                        }
                        else{
                            tdValuesHtml += '<div class="row">';
                            tdValuesHtml += '   <div class="col-12">';
                            tdValuesHtml += '       <div class="input-group date">';
                            tdValuesHtml += '           <input type="text" class="form-control fieldValueDate" name=filters['+uuid+'][value]" readonly placeholder="Select date"/>';
                            tdValuesHtml += '           <div class="input-group-append">';
                            tdValuesHtml += '               <span class="input-group-text"><i class="la la-calendar-o"></i></span>';
                            tdValuesHtml += '           </div>';
                            tdValuesHtml += '       </div>';
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '</div>';
                        }
                        break;
                    case "number":
                        if(operatorFieldKey === 'BETWEEN'){
                            tdValuesHtml += '<div class="row">';
                            tdValuesHtml += '   <div class="col-6">';
                            if($.inArray(fieldObj['key'], ['electricityFixed', 'depositAmount', 'proposedLease', 'targetRenewalRental', 'agentSaving', 'agentBilling']) === -1) {
                                tdValuesHtml += '       <input type="number" name="filters[' + uuid + '][value][from]" step=0.01 class="form-control" placeholder="From">';
                            }
                            else{
                                tdValuesHtml += '<div class="input-group">';
                                tdValuesHtml += '   <div class="input-group-prepend"><span class="input-group-text">'+currencySymbol+'</span></div>';
                                tdValuesHtml += '   <input type="number" name="filters[' + uuid + '][value][from]" step=0.01 class="form-control" placeholder="From">';
                                tdValuesHtml += '</div>';
                            }
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '   <div class="col-6">';
                            if($.inArray(fieldObj['key'], ['electricityFixed', 'depositAmount', 'proposedLease', 'targetRenewalRental', 'agentSaving', 'agentBilling']) === -1) {
                                tdValuesHtml += '       <input type="number" name="filters[' + uuid + '][value][to]" step=0.01 class="form-control" placeholder="To">';
                            }
                            else{
                                tdValuesHtml += '<div class="input-group">';
                                tdValuesHtml += '   <div class="input-group-prepend"><span class="input-group-text">'+currencySymbol+'</span></div>';
                                tdValuesHtml += '   <input type="number" name="filters[' + uuid + '][value][to]" step=0.01 class="form-control" placeholder="To">';
                                tdValuesHtml += '</div>';
                            }
                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '</div>';
                        }
                        else{
                            tdValuesHtml += '<div class="row">';
                            tdValuesHtml += '   <div class="col-12">';
                            if($.inArray(fieldObj['key'], ['electricityFixed', 'depositAmount', 'proposedLease', 'targetRenewalRental', 'agentSaving', 'agentBilling']) === -1) {
                                tdValuesHtml += '<input type="number" name="filters['+uuid+'][value]" step=0.01 class="form-control" placeholder="Select a value">';
                            }
                            else{
                                tdValuesHtml += '<div class="input-group">';
                                tdValuesHtml += '   <div class="input-group-prepend"><span class="input-group-text">'+currencySymbol+'</span></div>';
                                tdValuesHtml += '   <input type="number" name="filters['+uuid+'][value]" step=0.01 class="form-control" placeholder="Select a value">';
                                tdValuesHtml += '</div>';
                            }

                            tdValuesHtml += '   </div>';
                            tdValuesHtml += '</div>';
                        }
                        break;
                    case "boolean":
                        tdValuesHtml += '<div class="row text-left">';
                        tdValuesHtml += '   <div class="col-12">';
                        tdValuesHtml += '       <div class="kt-radio-inline">';
                        tdValuesHtml += '           <label class="kt-radio kt-radio--bold kt-radio--brand">';
                        tdValuesHtml += '               <input type="radio" name="filters['+uuid+'][value]" value="1"> Yes';
                        tdValuesHtml += '               <span></span>';
                        tdValuesHtml += '           </label>';
                        tdValuesHtml += '           <label class="kt-radio kt-radio--bold kt-radio--brand">';
                        tdValuesHtml += '               <input type="radio" name="filters['+uuid+'][value]" value="0"> No';
                        tdValuesHtml += '               <span></span>';
                        tdValuesHtml += '           </label>';
                        tdValuesHtml += '       </div>';
                        tdValuesHtml += '   </div>';
                        tdValuesHtml += '</div>';
                        break;
                    default:
                        tdValuesHtml += '<div class="row">';
                        tdValuesHtml += '   <div class="col-12">';
                        tdValuesHtml += '       <input type="text" name="filters['+uuid+'][value]" class="form-control" placeholder="Select a value">';
                        tdValuesHtml += '   </div>';
                        tdValuesHtml += '</div>';
                        break;
                }
            }
        }
        tr.find('td:eq(3)').html(tdValuesHtml);

        handlerSelection();
    });

    $(document).on('change', '.filter_fields_select', function () {
        handlerSelection();
    });

    $(document).on('click', '.selectAllFields', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.fields_blocks');
        var value = true;
        if($(this).hasClass('selectAllFields__deselect')){
            value = false;
        }
        parent.find('input[type="checkbox"]').each(function () {
           $(this).prop('checked', value);
        });

        handlerSelection();
    });

    $(document).on('change', '.filter_fields_select', function () {
        handlerSelection();
    });
});


function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function uuidv4() {
    return 'xxxxxxx-xxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 12 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(12);
    });
}

function initDatePicker() {
    $('.fieldValueDate').each(function (i, item) {
        $(item).datepicker({
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            },
            format: 'dd M yyyy',
        });
    });
}

function handlerSelection() {
    if($('.filter-table tbody tr').length === 0){
        $('.filter-table tbody').append('<tr class="no_filters"><td colspan="5" class="text-center">No filters...</td></tr>');
        $('.fields-list').append('<div class="col-12 text-center no_fields"><h5>Add filters... After which a selection of fields will be available to you.</h5></div>');
    }
    else{
        $('.filter-table .no_filters').remove();
        $('.fields-list .no_fields').remove();
    }
    for (var property in allFields) {
        if($('.filter-table tbody tr[data-type="'+property+'"]').length === 0){
            $('.fields_blocks__'+property).hide();
            $('.fields_blocks__'+property).find('.filter_fields_select').each(function () {
               $(this).prop('checked', false);
            });
        }
        else{
            $('.fields_blocks__'+property).show();
        }
    }

    var selectedFields = {};
    $('.selectCriteriaField').each(function (i, item) {
        var type = $(item).parents('tr').attr('data-type');
        if(type && $(item).val()){
            if(selectedFields[type] === undefined){
                selectedFields[type] = [];
            }
            selectedFields[type].push($(item).val());
        }
    });

    $('.selectCriteriaField').each(function (i, field) {
        var type = $(field).parents('tr').attr('data-type');
        if(type && selectedFields[type] !== undefined){
            $(field).find('option').each(function (j, fieldOption) {
                if($(fieldOption)[0].value && $.inArray($(fieldOption)[0].value, selectedFields[type]) !== -1){
                    if($(field).val() ===  $(fieldOption)[0].value){
                        $(fieldOption).prop('disabled', false);
                    }
                    else{
                        $(fieldOption).prop('disabled', true)
                    }
                }
                else{
                    $(fieldOption).prop('disabled', false);
                }
            });
        }
    });

    if($('.selectCriteriaField:visible').length > 0 && $('.filter_fields_select:visible:checked').length > 0){
        $('#filterFormsBtn').removeClass('disabled');
        $('#filterFormsBtn').prop('disabled', false);
    }
    else{
        $('#filterFormsBtn').prop('disabled', true);
        $('#filterFormsBtn').addClass('disabled');
    }

    $('.fields_blocks').each(function () {
        if($(this).find('input[type="checkbox"]').length === $(this).find('input[type="checkbox"]:checked').length){
            $(this).find('.selectAllFields').text('Deselect All');
            $(this).find('.selectAllFields').addClass('selectAllFields__deselect');
            $(this).find('.selectAllFields').removeClass('selectAllFields__select');
        }
        else{
            $(this).find('.selectAllFields').text('Select All');
            $(this).find('.selectAllFields').removeClass('selectAllFields__deselect');
            $(this).find('.selectAllFields').addClass('selectAllFields__select');
        }
    });


    initDatePicker();
}
