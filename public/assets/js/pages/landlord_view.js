jQuery(document).ready(function() {
    $('#kt_table_1').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        bSort: true,
        aaSorting: [],
        pageLength: 25,
    });
});
