$(function() {

	// Style checkboxes and radios
	$('.styled').uniform();

    $(".form-validate").validate({
        errorClass: 'validation-error-label',
        successClass: 'validation-valid-label',
        highlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        errorPlacement: function(error, element) {
            // Input with icons and Select2
            if (element.parents('div').hasClass('has-feedback') || element.hasClass('select2-hidden-accessible')) {
                error.appendTo( element.parent() );
            } else {
                error.insertAfter(element);
            }
        },
        validClass: "validation-valid-label",
        success: function(label) {
            label.addClass("validation-valid-label").text("Successfully")
        },
        rules: {
            email: {
                required: true,
                email: true,
            },
            password: {
                minlength: 5
            }
        },
        messages: {
            password: {
            	minlength: jQuery.validator.format("At least {0} characters required")
            }
        }
    });

});
