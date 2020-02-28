jQuery(document).ready(function() {
    var table = $('#tableIssue').DataTable({
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
            if($(this).data('col-index') == 3){
                $(this).val('Open');
                table.column($(this).data('col-index')).search('Open', false, false);
            }
            else{
                $(this).val('');
                table.column($(this).data('col-index')).search('', false, false);
            }
        });
        table.table().draw();
    });

    table.column(3).search('Open', false, false);
    table.table().draw();

});
