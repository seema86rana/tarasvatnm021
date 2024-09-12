"use strict";

$(document).ready(function () {

    let $devices_dt = null;
    if ($('.devices-dt').length) {
        $devices_dt = $('.devices-dt').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            ajax: deviceUrl,
            dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
            scrollX: true,
            language: {
                search: '<span>Filter:</span> _INPUT_',
                lengthMenu: '<span>Show:</span> _MENU_',
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            },
            drawCallback: function () {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
                sw();
            },
            preDrawCallback: function () {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            columns: [
                { data: 'serial_no', name: 'serial_no', orderable: false, searchable: false, width: '5%' },
                { data: 'name', name: 'name', width: '15%' },
                { data: 'device_id', name: 'device_id', width: '15%' },
                { data: 'user', name: 'user', width: '15%' },
                { data: 'shift', name: 'shift', orderable: false, searchable: false, width: '5%' },
                { data: 'status', name: 'status', orderable: false, searchable: false, width: '10%' },
                { data: 'created_at', name: 'created_at', width: '15%' },
                { data: 'created_by', name: 'created_by', width: '10%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%' }
            ]
        });

        // Add placeholder to the datatable filter option
        $('.dataTables_filter input[type=search]').attr('placeholder', 'Type to search...');

        // Enable Select2 select for the length option
        $('.dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: 'auto'
        });
    }

    $(document).on('click', '.add-device', function (e) {
        e.preventDefault();
        loaderToggle(1);
        $.ajax({
            url: deviceUrl + '/create',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                loaderToggle(1);
            },
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("add-device-modal", result.html, true, "modal-lg");
                } else {
                    toast_error(result.message);
                }
                loaderToggle(0);
            },
            error: function (error) {
                toast_error();
            }
        });
    });

    $(document).on("click", "#save-device", function(e) {
        e.preventDefault();

        if(validation("add-device-form") == false) {
            return false;
        }
        // let formData = new FormData(document.getElementById("add-device-form"));
        let formData = new FormData($("#add-device-form").get(0));

        $.ajax({
            url: deviceUrl,
            type: 'POST',
            cache: false,
            async: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            data: formData,
            beforeSubmit: function () {
                loaderToggle(1);
            },
            complete: function (data) {

                data = data.responseJSON;
                if (data.statusCode == 1) {
                    $(".modal").modal("hide");
                    toast_success(data.message, 'Success!');
                    $devices_dt.ajax.reload();
                } else {
                    jQuery('.load-main').addClass('hidden');
                    toast_error(data.message, 'Error');
                }
                loaderToggle(0);
            },
            error: function (error) {
                loaderToggle(0);
            }
        });
    });

    $(document).on('click', '.edit-device', function (event) {
        event.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let edit_callback = function () {
                $.ajax({
                    url: deviceUrl + '/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        loaderToggle(1);
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            make_modal("edit-device-modal", result.html, true, "modal-lg");
                        } else {
                            toast_error(result.message);
                        }
                        loaderToggle(0);
                    },
                    error: function (error) {
                        toast_error();
                    }
                });
                loaderToggle();
            };
            swal_confirmation(edit_callback, 'Are you sure?', 'You won\'t be able to revert this!', 'Yes, edit it!');
        } else {
            toast_error();
        }
    });

    $(document).on("click", "#update-device", function(e) {
        e.preventDefault();

        if(!validation("edit-device-form")) {
            return false;
        }
        let id = $(this).attr('data-id');
        let formData = new FormData(document.getElementById("edit-device-form"));
        // let formData = new FormData($("#edit-device-form").get(0));

        $.ajax({
            url: deviceUrl + '/' + id,
            type: 'POST',
            cache: false,
            async: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            data: formData,
            beforeSubmit: function () {
                loaderToggle(1);
            },
            complete: function (data) {

                data = data.responseJSON;
                if (data.statusCode == 1) {
                    $(".modal").modal("hide");
                    toast_success(data.message, 'Success!');
                    $devices_dt.ajax.reload();
                } else {
                    jQuery('.load-main').addClass('hidden');
                    toast_error(data.message, 'Error');
                }
                loaderToggle(0);
            },
            error: function (error) {
                loaderToggle(0);
            }
        });
    });

    $(document).on('click', '.delete-device', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let delete_callback = function () {
                $.ajax({
                    url: deviceUrl + '/' + id,
                    type: 'DELETE',
                    dataType: 'json',
                    beforeSend: function () {
                        loaderToggle(1);
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            toast_success(result.message, 'Deleted!');
                            $devices_dt.ajax.reload();
                        } else {
                            toast_error(result.message);
                        }
                    },
                    error: function (error) {
                        toast_error();
                    }
                });
                loaderToggle();
            };
            swal_confirmation(delete_callback);
        } else {
            toast_error();
        }
    });

    $(document).on('change', '.status-device', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        let status = $(this).val();
        if (id) {
            $.ajax({
                url: deviceUrl + '/' + id,
                type: 'GET',
                data: {status},
                dataType: 'json',
                beforeSend: function () {
                    loaderToggle(1);
                },
                complete: function (response) {
                    let result = response.responseJSON;
                    if (result.statusCode) {
                        toast_success(result.message, 'Success!');
                        $devices_dt.ajax.reload();
                    } else {
                        toast_error(result.message);
                    }
                },
                error: function (error) {
                    toast_error();
                }
            });
            loaderToggle();
        } else {
            toast_error();
        }
    });

    $(document).on("click", ".add-shift", function() {
        var html = "";
        html += '<div class="form-group">';
        html += '<div class="row">';
        html += '<div class="col-md-4">';
        html += '<label class="col-form-label label_text text-lg-right" for="shift_name">Shift Name <small class="req text-danger">*</small></label>';
        html += '<input type="text" value="shift" name="shift_name[]" class="form-control shift_name" id="shift_name" placeholder="Enter shift name" required readonly />';
        html += '</div>';
        html += '<div class="col-md-3">';
        html += '<label class="col-form-label label_text text-lg-right" for="shift_start">Shift Start <small class="req text-danger">*</small></label>';
        html += '<input type="text" name="shift_start[]" class="form-control shift_start timepicker" id="shift_start" placeholder="Enter shift_start" required />';
        html += '</div>';
        html += '<div class="col-md-3">';
        html += '<label class="col-form-label label_text text-lg-right" for="shift_end">Shift End <small class="req text-danger">*</small></label>';
        html += '<input type="text" name="shift_end[]" class="form-control shift_end timepicker" id="shift_end" placeholder="Enter shift_end" required />';
        html += '</div>';
        html += '<div class="col-md-2">';
        html += '<p style="margin-bottom: 5px;">&nbsp;</p>';
        html += '<button type="button" class="btn btn-theme-dark minus-shift">';
        html += '<b><i class="icon-minus-circle2"></i></b>';
        html += '</button>';
        html += '&nbsp;&nbsp;';
        html += '<button type="button" class="btn btn-theme-dark add-shift">';
        html += '<b><i class="icon-plus-circle2"></i></b>';
        html += '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        $(this).closest(".form-group").after(html);

        // Reinitialize the timepicker for the newly added elements
        $('.timepicker').timepicker({
            showMeridian: true,  // 12-hour format
            minuteStep: 5,       // Allow selecting minutes in increments of 1
            showInputs: false,   // Prevent direct input and show dropdown
            defaultTime: 'current'  // Set current time as default
        });

        autoShiftName();
    });

    $(document).on("click", ".minus-shift", function() {
        $(this).closest(".form-group").remove();

        // Reinitialize the timepicker for the newly added elements
        $('.timepicker').timepicker({
            showMeridian: true,  // 12-hour format
            minuteStep: 5,       // Allow selecting minutes in increments of 1
            showInputs: false,   // Prevent direct input and show dropdown
            defaultTime: 'current'  // Set current time as default
        });

        autoShiftName();
    });

    $(document).on('click', '.show-shift', function (event) {
        event.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            var selectValues = $("#shift_" + id).text();
            var jsonData = JSON.stringify(selectValues);
            var parsedData = JSON.parse(selectValues);
            var html = "";
            html += '<div class="modal-header">';
            html += '<h5 class="modal-title" id="exampleModalLongTitle">Shift Detail</h5>';
            html += '<button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">';
            html += '<span aria-hidden="true">&times;</span>';
            html += '</button>';
            html += '</div>';
            html += '<div class="modal-body text-center">';
            $.each(parsedData, function(k, v) {
                html += '<p>';
                html += '<b>Shift Name: </b>'+v.shift_name;
                html += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<b>Shift Start: </b>'+v.shift_start;
                html += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                html += '<b>Shift End: </b>'+v.shift_end;
                html += '</p>';
            });
            html += '</div>';
            html += '<div class="modal-footer text-center">';
            html += '<button type="button" class="btn btn-theme-dark close-modal" style="">';
            html += '<i class="icon-arrow-left13"></i> Back';
            html += '</button>';
            html += '</div>';
            make_modal("show-shift-modal", html, true, "modal-xl");
        } else {
            toast_error();
        }
    });
});

function sw() {
    var switches = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
    switches.forEach(function (html) {
        var switchery = new Switchery(html);
    });
}

function autoShiftName() {
    $(".shift_name").each(function(k, v) {
        $(this).val("Shift " + (k + 1));
    });
}
