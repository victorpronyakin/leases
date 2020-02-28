jQuery(document).ready(function() {
    var chart = AmCharts.makeChart("leaseSpendChart", {
        "rtl": KTUtil.isRTL(),
        "type": "serial",
        "theme": "light",
        "dataProvider": [],
        "valueAxes": [{
            "gridColor": "#FFFFFF",
            "gridAlpha": 0.2,
            "dashLength": 0,
            "title": currencySymbol
        }],
        "gridAboveGraphs": true,
        "startDuration": 1,
        "graphs": [{
            "balloonText": "[[category]]: <b>R [[value]]</b>",
            "fillAlphas": 0.8,
            "lineAlpha": 0.2,
            "type": "column",
            "valueField": "total"
        }],
        "chartCursor": {
            "categoryBalloonEnabled": false,
            "cursorAlpha": 0,
            "zoomable": false
        },
        "categoryField": "month",
        "categoryAxis": {
            "gridPosition": "start",
            "gridAlpha": 0,
            "tickPosition": "start",
            "tickLength": 20
        },
        "export": {
            "enabled": true
        }

    });

    $.ajax({
        type: 'GET',
        url: "/dashboardChart",
        data: {},
        success: function (data) {
            if (data.result === true) {
                chart.dataProvider = data.dataProvider;
                chart.validateData();
            }
        }
    });

    var activeRemindersTable = $('#activeRemindersTable').DataTable({
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

    var historicalRemindersTable = $('#historicalRemindersTable').DataTable({
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        bSort: true,
        aaSorting: [],
        pageLength: 25
    });

    var shoozeRemindersTable = $('#snoozeRemindersTable').DataTable({
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

    $(document).on('click', '#activeRemindersTable tbody tr td:not(.act), #historicalRemindersTable tbody tr td:not(.act), #snoozeRemindersTable tbody tr td:not(.act)', function (e) {
        e.preventDefault();
        var path = $(this).parents('tr').attr('data-path');
        if(path){
            window.location.href = path;
        }
    });

    $(document).on('click', '.doneActualReminder', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var parent = $(this).parents('tr');
        if(id){
            $.ajax({
                type: "POST",
                url: '/reminder/actual/edit/'+id,
                data: {
                    'status': 'done'
                },
                success: function(data)
                {
                    if (data.result == true) {
                        toastr.success("Reminder has been done");
                        var row = activeRemindersTable.row(parent);
                        var rowNode = row.node();
                        row.remove().draw();
                        $(rowNode).find('td.act').remove();

                        historicalRemindersTable.row.add(rowNode).draw();
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong! Try again!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong! Try again!');
        }
    });

    $(document).on('click', '.unSnoozeActualReminder', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var parent = $(this).parents('tr');
        var issue_btn = parent.attr('data-issue-btn');
        if(id){
            $.ajax({
                type: "POST",
                url: '/reminder/actual/edit/'+id,
                data: {
                    'status': 'active'
                },
                success: function(data)
                {
                    if (data.result == true) {
                        toastr.success("Successfully!");
                        var row = shoozeRemindersTable.row(parent);
                        var rowNode = row.node();
                        row.remove().draw();
                        $(rowNode).find('td.act').remove();
                        var html = '<td class="text-center act">' +
                            '   <button data-id="'+id+'" class="btn btn-clean btn-icon doneActualReminder">' +
                            '       <i class="text-success la la-check-circle"></i>' +
                            '   </button>' +
                            '   <button data-id="'+id+'" class="btn btn-clean btn-icon snoozeActualReminder">' +
                            '       <i class="text-warning la la-clock-o"></i>' +
                            '   </button>';
                        if(issue_btn == 1){
                            html += '<button data-id="'+id+'" class="btn btn-primary btn-sm closeIssue">Close Issue</button>';
                        }
                        html += '</td>';

                        $(rowNode).append(html);
                        activeRemindersTable.row.add(rowNode).draw();

                        enableDaterangepicker();

                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong! Try again!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong! Try again!');
        }
    });

    $(document).on('click', '.closeIssue', function (e) {
        e.preventDefault();
        var btn  = $(this);
        var id = btn.attr('data-id');
        var parent = btn.parents('tr');
        if(id){
            $.ajax({
                type: "POST",
                url: '/reminder/actual/edit/'+id,
                data: {
                    'action': 'closeIssue'
                },
                success: function(data)
                {
                    if (data.result == true) {
                        toastr.success("Issue has been closed");
                        parent.attr('data-issue-btn', 0);
                        btn.remove();
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong! Try again!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong! Try again!');
        }

    });

    enableDaterangepicker();

    function enableDaterangepicker() {
        var snoozePicker = $('.snoozeActualReminder');
        if(snoozePicker.data('daterangepicker')){
            snoozePicker.data('daterangepicker').remove();
        }
        snoozePicker.daterangepicker({
            buttonClasses: ' btn',
            applyClass: 'btn-primary',
            cancelClass: 'btn-secondary',
            singleDatePicker: true,
            drops: "up",
            opens: "left",
            showCustomRangeLabel: false,
            alwaysShowCalendars: true,
            minDate: moment().add(1, 'days').format('MM/DD/YYYY'),
            startDate: moment().add(1, 'days').format('MM/DD/YYYY'),
            endDate: moment().add(1, 'days').format('MM/DD/YYYY'),
            ranges: {
                '1 Day': [moment().add(1, 'days'), moment().add(1, 'days')],
                '1 Week': [moment().add(1, 'week'), moment().add(1, 'week')],
                '1 Month': [moment().add(1, 'months'), moment().add(1, 'months')],
            }
        }).on('apply.daterangepicker', function(ev, picker) {

            var id = $(this).attr('data-id');
            var parent = $(this).parents('tr');
            var snoozeDate = picker.startDate.format('YYYY-MM-DD');
            if(id && snoozeDate){
                $.ajax({
                    type: "POST",
                    url: '/reminder/actual/edit/'+id,
                    data: {
                        'snoozeDate': snoozeDate
                    },
                    success: function(data)
                    {
                        if (data.result == true) {
                            toastr.success("Reminder has been snooze!");
                            var row = activeRemindersTable.row(parent);
                            var rowNode = row.node();
                            row.remove().draw();
                            $(rowNode).find('td.act').remove();
                            $(rowNode).append('<td class="text-center act">' +
                                '   <button data-id="'+id+'" class="btn btn-clean btn-icon unSnoozeActualReminder">' +
                                '       <i class="text-success la la-reply"></i>'+
                                '   </button>' +
                                '</td>');

                            shoozeRemindersTable.row.add(rowNode).draw();
                        }
                        else if(data.message !== undefined){
                            toastr.error(data.message);
                        }
                        else{
                            toastr.error('Oops... Something went wrong! Try again!');
                        }
                    }
                });
            }
            else{
                toastr.error('Oops... Something went wrong! Try again!');
            }
        });
    }
});
