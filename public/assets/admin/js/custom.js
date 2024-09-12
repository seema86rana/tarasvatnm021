
var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
});

function make_modal($className, $html = null, $open_modal = false, $dialogSize = null) {
    let $modalHtml = '';
    let dialogClass = $dialogSize; // modal-sm, modal-lg, modal-xl
    $className = ($className !== '') ? $className : 'custom-modal';
    let id_name = $className.split(' ')[0];
    $modalHtml += `<div class="modal fade ${$className}" id="${id_name}" role="document" data-backdrop="static" data-keyboard="false">`;
    $modalHtml += `<div class="modal-dialog ${dialogClass} modal-dialog-centered" role="document">`;
    $modalHtml += '<div class="modal-content append-wrapper">';
    $modalHtml += ($html) ? $html : '';
    $modalHtml += '</div>';
    $modalHtml += '</div>';
    $modalHtml += '</div>';

    $('body').append($modalHtml);

    let $elem = $('body').find('#' + id_name);
    if ($open_modal) {
        $elem.modal('show');
    }
    /* On close remove the modal */
    $elem.on('hidden.bs.modal', function (event) {
        $(this).remove();
    });
    return $elem;
}

/* Loader Hide / Show */
function loaderToggle(status = 0) {
    if (status) {
        $("#loader").removeClass('hide');
    } else {
        $("#loader").addClass('hide');
    }
}

/* Toast Notifications */
function toast_success(message, title = 'Success!') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'success',
    });
}

function toast_error(message = 'Oops!, Something went wrong.', title = 'Error!') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'error',
    });
}

function toast_warning(message, title = 'Warning') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'warning',
    });
}

function toast_info(message, title = 'Info') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'info',
    });
}

function swal_confirmation(callback, title = 'Are you sure?', text = "You won't be able to revert this!", button = 'Yes, delete it!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: button,
    }).then((result) => {
        if (result.value) {
            callback();
        }
    })
}
/* Toast Notifications */

/* Form Validation */
function validation(form_id) {
    let count = 0;
    $("#"+form_id+" input").each(function(k, v) {
        $(this).css('border', '');        
        if($(this).val() == "") {
            $(this).css('border', '1px solid red');
            count++;
        }
    });
    $("#"+form_id+" select").each(function(k, v) {
        $(this).css('border', '');
        $(this).siblings('.select2-container').css('border', '');
        if($(this).val() == "") {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).siblings('.select2-container').css('border', '1px solid red');
            } else {
                $(this).css('border', '1px solid red');
            }
            count++;
        }
    });
    if(count > 0) {
        return false;
    }
    return true;
}

$(document).ready(function () {

    $(document).on("click", ".close-modal", function (e) {
        e.preventDefault();
        $(".modal").modal("hide");
    });
});













/*
const __PLACEHOLDER_IMAGE = '/assets/admin/images/placeholder.png';
const __MAX_IMAGE_FILE_SIZE = 2097152; // 2 MB
const __MAX_VIDEO_FILE_SIZE = 20097152; // 20 MB
*/


function clear_form(form = null, validation_obj) {

    if (validation_obj !== null && validation_obj !== '' && validation_obj !== undefined) {
        validation_obj.resetForm();
    }

    if (form.length && form !== null && form !== '' && form !== undefined) {
        form.find(':input:not(button)').each(function (i, e) {
            if ($(e).hasClass('select2bs4')) {
                $(e).val(['']).trigger('change');
            }
            $(e).val('');
        });
    }
}

function disable_input_toggle($elem, remove = 0) {
    if (remove) {
        $elem.removeAttr('disabled');
    } else {
        $elem.attr('disabled', 'disabled');
    }
}

if ($(document).find('.removable-flash-messages').length) {
    setTimeout(() => {
        $(document).find('.removable-flash-messages').fadeOut('slow', () => {
            $(this).remove()
        });
    }, 3000);
}

/**
 * Preview uploaded image in specific img element
 */
function preview_uploaded_image(input, $img_append_elem) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            let $image = __PLACEHOLDER_IMAGE;
            let valid_extensions = /(\.jpg|\.jpeg|\.png)$/i;
            if (typeof (input.files[0]) != 'undefined') {
                if (valid_extensions.test(input.files[0].name)) {
                    $image = e.target.result;
                } else {
                    $image = __PLACEHOLDER_IMAGE;
                }
            }
            $img_append_elem.attr('src', $image);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Validate Max file size validation
 */
$.validator.addMethod("filesize_max", function (value, element, param) {
    var isOptional = this.optional(element),
        file;

    if (isOptional) {
        return isOptional;
    }

    if ($(element).attr("type") === "file") {

        if (element.files && element.files.length) {

            file = element.files[0];
            return (file.size && file.size <= param);
        }
    }
    return false;
}, "File size is too large.");

/**
 * For Input type file styling
 */
function file_upload_style($elem) {
    if ($elem.length) {
        $elem.uniform({
            fileButtonClass: 'action btn bg-theme-dark'
        });
    }
}

/**
 * For Radio button styling
 */
function set_radio_style($elem) {
    if ($elem.length) {
        $elem.uniform({
            radioClass: 'choice'
        });
    }
}


function jGrowlAlert(message, alertType) {

    var header_msg = alertType == 'success' ? 'Success!' : 'Oh Snap!';
    $.jGrowl(message, {
        header: header_msg,
        theme: 'bg-' + alertType
    });
}