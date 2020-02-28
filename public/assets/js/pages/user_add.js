jQuery(document).ready(function() {

    $('#userForm').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: {
            'admin[type]': {
                required: true
            },
            'admin[firstName]': {
                required: true
            },
            'admin[lastName]': {
                required: true
            },
            'admin[email]': {
                required: true,
                email: true
            },
            'admin[phone]': {
                required: true
            },
            'admin[company]': {
                required: true
            },
            'admin[designation]': {
                required: true
            },
        },
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        }
    });

    if ($('.permissionCheckbox:checked').length == $('.permissionCheckbox').length ){
        $("#permissionSelectAll").prop('checked', true);
    }

    $("#permissionSelectAll").change(function(){
        $(".permissionCheckbox").prop('checked', $(this).prop("checked"));
    });

    $('.permissionCheckbox').change(function(){
        if(false == $(this).prop("checked")){ //if this item is unchecked
            $("#permissionSelectAll").prop('checked', false); //change "select all" checked status to false
        }
        if ($('.permissionCheckbox:checked').length == $('.permissionCheckbox').length ){
            $("#permissionSelectAll").prop('checked', true);
        }
    });
});
