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


    function searchAllFields() {
        var input2 = $("<input>").attr("type", "hidden").attr("name", "limit").val($('#limitItems').val());
        $('#search_form').append(input2);
        $('#search_form').submit();
    }
});
