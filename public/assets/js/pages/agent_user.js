jQuery(document).ready(function() {
    var tableUser = $('#userTable').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        bSort: true,
        aaSorting: [],
        pageLength: 25,
    });
});
