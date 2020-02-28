jQuery(document).ready(function() {
    $(document).on('click', '#search', function(e) {
        e.preventDefault();
        if($('#startMonthPicker').val() && $('#endMonthPicker').val()){
            searchAllFields();
        }
        else{
            toastr.error('Select Date Range');
        }
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
    $('#startMonthPicker').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'MM yyyy',
        autoclose: true,
        minViewMode: 1,
    });

    $('#endMonthPicker').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'MM yyyy',
        autoclose: true,
        minViewMode: 1,
    });

    function searchAllFields() {
        var input = $("<input>").attr("type", "hidden").attr("name", "limit").val($('#limitItems').val());
        $('#search_form').append(input);
        $('#search_form').submit();
    }

    $(document).on('change', 'input[name="invoiced"]', function () {
        var parent = $(this).parents('tr');
        var id = parent.attr('data-id');
        var month = $(this).attr('data-month');
        var value = $(this).is(':checked');

        if(id && month){
            $.ajax({
                type: 'POST',
                url: "/agent/billing/edit",
                data: {
                    leaseId: id,
                    month: month,
                    invoiced: value
                },
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
        }
    });

    $(document).on('change', 'input[name="paid"]', function () {
        var parent = $(this).parents('tr');
        var id = parent.attr('data-id');
        var month = $(this).attr('data-month');
        var value = $(this).is(':checked');
        if(id && month){
            $.ajax({
                type: 'POST',
                url: "/agent/billing/edit",
                data: {
                    leaseId: id,
                    month: month,
                    paid: value
                },
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
        }
    });

    $(document).on('click', '.invoicedSelectAll', function (e) {
        e.preventDefault();
        var month = $(this).attr('data-month');
        if(month){
            console.log(month);
            console.log(window.location.search);
            $.ajax({
                type: 'POST',
                url: "/agent/billing/selectAll"+window.location.search,
                data: {
                    field: 'invoiced',
                    month: month,
                },
                success: function (data) {
                    if (data.result === true) {
                        $('input[type="checkbox"][name="invoiced"][data-month="' + month + '"]').prop('checked', true);
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... something went wrong. Try again later');
                    }
                }
            });
        }
    });

    $(document).on('click', '.paidSelectAll', function (e) {
        e.preventDefault();
        var month = $(this).attr('data-month');
        if(month){
            console.log(month);
            console.log(window.location.search);
            $.ajax({
                type: 'POST',
                url: "/agent/billing/selectAll"+window.location.search,
                data: {
                    field: 'paid',
                    month: month,
                },
                success: function (data) {
                    if (data.result === true) {
                        $('input[type="checkbox"][name="paid"][data-month="' + month + '"]').prop('checked', true);
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... something went wrong. Try again later');
                    }
                }
            });
        }
    });
});
