jQuery(document).ready(function() {


    var formRules = {
        'site': {
            required: true
        },
        'type': {
            required: true
        },
        'details': {
            required: true
        },
    };


    $('#issueForm').validate({
        // Validate only visible fields
        ignore: ":hidden",

        // Validation rules
        rules: formRules,
        // Display error
        invalidHandler: function(event, validator) {
            KTUtil.scrollTop();
        }
    });



});
