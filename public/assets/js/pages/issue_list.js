jQuery(document).ready(function() {
    $('#openIssueTable').DataTable({
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

    $('#closeIssueTable').DataTable({
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

});
