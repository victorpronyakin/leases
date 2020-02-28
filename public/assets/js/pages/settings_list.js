jQuery(document).ready(function() {

    //ADD
    $(document).on('click', '.btnGeneralSettingAdd', function (e) {
        e.preventDefault();
        $('#modalAddInputType').val($(this).attr('data-type'));
        $('#modalAddInputName').val('');
        $('#modalAddTitle').text($(this).text());
        $('#addModal').modal('show');
    });
    $(document).on('submit', '#modalAddForm', function (e) {
        e.preventDefault();
        var type = $('#modalAddInputType').val();
        var name = $('#modalAddInputName').val();
        if(type && name){
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: {
                    type: type,
                    name: name
                },
                success: function (data) {
                    if (data.result === true) {
                        var html = '<tr data-id="'+data.id+'" data-type="'+type+'">' +
                            '   <td class="tableItemName">'+data.name+'</td>' +
                            '   <td class="text-right">' +
                            '       <a href="#" class="btn btn-clean btn-icon btnGeneralSettingEdit">' +
                            '           <i class="la la-edit"></i>' +
                            '       </a>' +
                            '       <a href="#" class="btn btn-clean btn-icon btnGeneralSettingRemove">' +
                            '           <i class="text-danger la la-trash"></i>' +
                            '      </a>' +
                            '   </td>' +
                            '</tr>';
                        $('#table'+type).append(html);

                        var htmlMainStatus = '<label class="kt-checkbox kt-checkbox--bold kt-checkbox--brand">';
                        htmlMainStatus += '     <input data-id="'+data.id+'" type="checkbox" name="mainDashboardStatus[]" value="'+data.id+'">'+data.name;
                        htmlMainStatus += '     <span></span>';
                        htmlMainStatus += '</label>';

                        $('#mainDashboardStatuses').append(htmlMainStatus);
                        var htmlAgentStatus = '<label class="kt-checkbox kt-checkbox--bold kt-checkbox--brand">';
                        htmlAgentStatus += '     <input data-id="'+data.id+'" type="checkbox" name="mainAgentStatus[]" value="'+data.id+'">'+data.name;
                        htmlAgentStatus += '     <span></span>';
                        htmlAgentStatus += '</label>';
                        $('#mainAgentStatuses').append(htmlAgentStatus);

                        toastr.success('Successfully!');
                        $('#addModal').modal('hide');
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong. Try again later!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }
    });

    //edit
    $(document).on('click', '.btnGeneralSettingEdit', function (e) {
        e.preventDefault();
        var tr = $(this).parents('tr');
        var id = tr.attr('data-id');
        var type = tr.attr('data-type');
        var name = tr.find('.tableItemName').text();
        var title = tr.parents('table').attr('data-edit');
        if(id && type){
            $('#modalEditInputId').val(id);
            $('#modalEditInputType').val(type);
            $('#modalEditInputName').val(name);
            $('#modalEditTitle').text(title);
            $('#editModal').modal('show');
        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }
    });
    $(document).on('submit', '#modalEditForm', function (e) {
        e.preventDefault();
        var id = $('#modalEditInputId').val();
        var type = $('#modalEditInputType').val();
        var name = $('#modalEditInputName').val();
        var td = $('#table'+type).find('tr[data-id="'+id+'"]').find('td.tableItemName');
        if(id && type && name){
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: {
                    id: id,
                    type: type,
                    name: name
                },
                success: function (data) {
                    if (data.result === true) {
                        td.text(name);
                        var mainDashmoardStatusInput = $("#mainDashboardStatuses>label>input[data-id='"+id+"']");
                        if(mainDashmoardStatusInput.is(':checked')){
                            mainDashmoardStatusInput.parent().html('<input data-id="'+id+'" type="checkbox" name="mainDashboardStatus[]" checked value="'+id+'">'+name+'<span></span>');
                        }
                        else{
                            mainDashmoardStatusInput.parent().html('<input data-id="'+id+'" type="checkbox" name="mainDashboardStatus[]" value="'+id+'">'+name+'<span></span>');
                        }
                        var mainAgentStatusInput = $("#mainAgentStatuses>label>input[data-id='"+id+"']");
                        if(mainAgentStatusInput.is(':checked')){
                            mainAgentStatusInput.parent().html('<input data-id="'+id+'" type="checkbox" name="mainAgentStatus[]" checked value="'+id+'">'+name+'<span></span>');
                        }
                        else{
                            mainAgentStatusInput.parent().html('<input data-id="'+id+'" type="checkbox" name="mainAgentStatus[]" value="'+id+'">'+name+'<span></span>');
                        }

                        toastr.success('Successfully!');
                        $('#editModal').modal('hide');
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong. Try again later!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }
    });

    //REMOVE
    $(document).on('click', '.btnGeneralSettingRemove', function (e) {
        e.preventDefault();
        var tr = $(this).parents('tr');
        var id = tr.attr('data-id');
        var type = tr.attr('data-type');
        if(id && type){
            $.ajax({
                type: "POST",
                url: '/settings/remove',
                data: {
                    type: type,
                    id: id
                },
                success: function (data) {
                    if (data.result === true) {
                        tr.remove();
                        $("#mainDashboardStatuses>label>input[data-id='"+id+"']").parent().remove();
                        $("#mainAgentStatuses>label>input[data-id='"+id+"']").parent().remove();
                        toastr.success('Removed!');
                    }
                    else if(data.message !== undefined){
                        toastr.error(data.message);
                    }
                    else{
                        toastr.error('Oops... Something went wrong. Try again later!');
                    }
                }
            });
        }
        else{
            toastr.error('Oops... Something went wrong. Try again later!');
        }
    });
});
