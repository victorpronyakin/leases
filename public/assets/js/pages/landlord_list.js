jQuery(document).ready(function() {
    $('#kt_table_1').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        aaSorting: [],
        pageLength: 25,
        columnDefs: [
            {
                targets: -1,
                title: 'Actions',
                orderable: false,

            },
        ],
        bSort: true,
    });

    $(document).on('click', '#kt_table_1 td:not(.act):not(a)', function (e) {
        if($(this).attr('tabindex') !== '0'){
            e.preventDefault();
            var path = $(this).parent().attr('data-path');
            // if(!path && $(this).parent().hasClass('child')){
            //     path = $(this).parent().prev().attr('data-path');
            //     if(path){
            //         window.location.href = path;
            //     }
            // }

            if(path){
                window.location.href = path;
            }
        }
    });
});
