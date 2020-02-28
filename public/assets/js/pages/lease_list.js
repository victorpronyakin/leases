jQuery(document).ready(function() {

    var table = $('#kt_table_1').DataTable({
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

    $('#kt_search').on('click', function(e) {
        e.preventDefault();
        var params = {};
        $('.kt-input').each(function() {
            var i = $(this).data('col-index');
            if (params[i]) {
                params[i] += '|' + $(this).val();
            }
            else {
                params[i] = $(this).val();
            }
        });
        $.each(params, function(i, val) {
            // apply search params to datatable
            table.column(i).search(val ? val : '', false, false);
        });
        table.table().draw();
    });

    $('#kt_reset').on('click', function(e) {
        e.preventDefault();
        $('.kt-input').each(function() {
            $(this).val('');
            table.column($(this).data('col-index')).search('', false, false);
        });
        $('#min').datepicker('clearDates');
        $('#max').datepicker('clearDates');
        table.table().draw();
    });


    $('#min').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });
    $('#max').datepicker({
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        format: 'dd M yyyy',
    });

    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var min = $('#min').datepicker('getDate');
            var max = $('#max').datepicker('getDate');
            var dateArr = data[5].split(' ');
            var startDate = new Date(dateArr[1]+'-'+dateArr[0]+'-'+dateArr[2]);
            console.log(min);
            console.log(max);
            console.log(dateArr);
            console.log(startDate);
            if (min == null && max == null){
                return true;
            }
            if (min == null && startDate <= max) {
                return true;
            }
            if (max == null && startDate >= min) {
                return true;
            }
            if (startDate <= max && startDate >= min) {
                return true;
            }
            return false;
        }
    );
});
