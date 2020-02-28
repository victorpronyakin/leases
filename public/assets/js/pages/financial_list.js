jQuery(document).ready(function() {

    $(document).on('click', '#search', function(e) {
        e.preventDefault();
        searchAllFields();
    });

    $(document).on('click', '#reset', function() {
        var form = $('#search_form');
        var inputs = form.find('input');
        var selects = form.find('select');
        inputs.each(function() {
            $(this).val('');
        });
        selects.each(function() {
            $(this).val('');
        });

        searchAllFields();
    });

    $(document).on('change', '#limitItems', function () {
        searchAllFields();
    });

    //DATEPICKER
    $('#monthPicker').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'MM yyyy',
        autoclose: true,
        minViewMode: 1,
    }).on('changeDate', function(){
        if($('#search_form').length > 0){
            searchAllFields();
        }
        else{
            var form = $(this).parents('form');
            form.submit();
        }
    });

    $(document).on('change', '.financialField', function () {
        var data = generateFinanceFieldsData($(this));

        $.ajax({
            type: 'POST',
            url: "/financial/edit",
            data: data,
            success: function (data) {
                if (data.result !== true) {
                    if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... something went wrong. Try again later');
                    }
                }
            }
        });
    });

    $(document).on('change', '.invoices-uploader__input', function () {
        if($(this).val()){
            var parent = $(this).parents('td');
            var formData = new FormData();
            var data = generateFinanceFieldsData($(this));
            formData.append('leaseId', data.leaseId);
            formData.append('month', data.month);
            for (var key in data.fields) {
                formData.append('fields['+key+']', data.fields[key]);
            }
            formData.append('invoice', $(this)[0].files[0]);

            $.ajax({
                type: 'POST',
                url: "/financial/edit",
                data: formData,
                contentType: false,
                enctype: 'multipart/form-data',
                processData: false,
                success: function (data) {
                    if (data.result === true) {
                        if(data.url && data.name){
                            parent.find('.invoices').append('<div class="invoices__item" data-url="'+data.url+'">\n' +
                                '   <a href="'+data.url+'" target="_blank">'+data.name+'</a>\n' +
                                '       <span class="invoices__item__remove"><i class="fa fa-times text-danger"></i></span>\n' +
                                '</div>');
                        }
                    }
                    else if (data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... something went wrong. Try again later');
                    }
                }
            });
        }
    });

    $(document).on('click', '.invoices__item__remove', function () {
        var item = $(this).parents('.invoices__item');
        var url = item.attr('data-url');
        var parent = item.parents('tr');
        var leaseId = parent.attr('data-id');
        var month = $('#monthPicker').val();

        $.ajax({
            type: 'POST',
            url: "/financial/removeInvoice",
            data: {
                'leaseId': leaseId,
                'month': month,
                'url': url,
            },
            success: function (data) {
                if (data.result === true) {
                    item.remove();
                }
                else if (data.message !== undefined){
                    toastr.error(data.message);
                }
                else{
                    toastr.error('Oops... something went wrong. Try again later');
                }
            }
        });
    });

    $(document).on('click', '.invoices-uploader__button', function (e) {
        e.preventDefault();
        $(this).parent().find('.invoices-uploader__input').trigger('click');
    });

    $(document).on('change', '.otherCostFields', function () {
        var parent = $(this).parents('td');
        var total = 0;
        parent.find('.otherUtilityCostsDiv').find('.otherCostFields').each(function () {
           if($(this).val()){
               total = total + parseFloat($(this).val());
           }
        });
        parent.find('input[name=otherCost]').val(total).change();
    });

    function generateFinanceFieldsData(item) {
        var parent = item.parents('tr');
        var leaseId = parent.attr('data-id');
        var month = $('#monthPicker').val();

        if(item.attr('name') === 'leaseCharge' || item.attr('name') === 'electricityCost' || item.attr('name') === 'otherCost'){
            var leaseCharge = parent.find('input[name=leaseCharge]').val();
            var electricityCost = parent.find('input[name=electricityCost]').val();
            var otherCost = parent.find('input[name=otherCost]').val();
            var total = parseFloat(leaseCharge) + parseFloat(electricityCost) + parseFloat(otherCost);
            parent.find('input[name=total]').val(total)
        }

        var fields = {};
        parent.find('.financialField').each(function () {
            var name = $(this).attr('name');
            fields[name] = $(this).val();
        });

        return {
            leaseId: leaseId,
            month: month,
            fields: fields,
        };
    }

    function searchAllFields() {
        var input1 = $("<input>").attr("type", "hidden").attr("name", "month").val($('#monthPicker').val());
        $('#search_form').append(input1);
        var input2 = $("<input>").attr("type", "hidden").attr("name", "limit").val($('#limitItems').val());
        $('#search_form').append(input2);
        $('#search_form').submit();
    }
});
