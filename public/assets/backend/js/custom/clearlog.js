"use strict";

let user_id = "";
let device_id = "";
let select_shift = "";
let select_shift_day = "";
let node_id = "";
let machine_id = "";
let dateRange = "";

$(document).ready(function () {
    $(document).on("click", "#delete-machine-log", async function(e) {
        e.preventDefault();
        await loaderToggle(1);

        user_id = $("#user_id").val();
        device_id = $("#device_id").val();
        select_shift = $("#select_shift").val();
        select_shift_day = $("#select_shift").find(":selected").data("shift-day");
        node_id = $("#node_id").val();
        machine_id = $("#machine_id").val();
        dateRange = $("#dateRange").val();

        var formData = {
            user_id: user_id,
            device_id: device_id,
            select_shift: select_shift,
            select_shift_day: select_shift_day,
            node_id: node_id,
            machine_id: machine_id,
            dateRange: dateRange,
            type: 'clearMachineLog'
        };

        $.ajax({
            url: clearLogUrl,
            type: 'POST',
            dataType: 'json',
            data: formData,
            beforeSubmit: function () {
            },
            complete: function (data) {
                data = data.responseJSON;
                if (data.statusCode == 1) {
                    toast_success(data.message, 'Success');
                    $('#clear-form-log').trigger('click');
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

    $(document).on("click", "#clear-form-log", async function(e) {
        console.log(e);
        
        e.preventDefault();
        await loaderToggle(1);

        user_id = "";
        device_id = "";
        select_shift = "";
        select_shift_day = "";
        node_id = "";
        machine_id = "";
        dateRange = "";

        $("#user_id").val('').trigger('change');
        $("#device_id").val('').trigger('change');
        $("#select_shift").val('').trigger('change');
        $("#node_id").val('').trigger('change');
        $("#machine_id").val('').trigger('change');
        $("#dateRange").val('');

        setTimeout(() => {
            loaderToggle(0);
        }, 1000);
    });
    
    $(document).on("change", ".onchange_function", async function(e) {
        e.preventDefault();

        let thisMain = $(this);
        thisMain.prop('disabled', true);

        var formData = new FormData();
        formData.append(thisMain.attr('name'), thisMain.val());

        await loaderToggle(1);
        $.ajax({
            url: clearLogUrl,
            type: 'POST',
            cache: false,
            async: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            data: formData,
            beforeSubmit: function () {
            },
            complete: function (data) {

                data = data.responseJSON;
                if (data.statusCode == 1) {
                    if(thisMain.attr('name') == 'user_id') {
                        populateSelect2("#device_id", data.device);
                        populateSelect2("#select_shift", data.deviceShift, 'selectedShift');
                        populateSelect2("#node_id", data.nodeMaster);
                        populateSelect2("#machine_id", data.machineMaster);
                    } 
                    else if(thisMain.attr('name') == 'device_id') {                        
                        populateSelect2("#select_shift", data.deviceShift, 'selectedShift');
                        populateSelect2("#node_id", data.nodeMaster);
                        populateSelect2("#machine_id", data.machineMaster);
                    }
                    else if(thisMain.attr('name') == 'node_id') {
                        populateSelect2("#machine_id", data.machineMaster);
                    }
                } else {
                    jQuery('.load-main').addClass('hidden');
                    toast_error(data.message, 'Error');
                }
                thisMain.prop('disabled', false);
                loaderToggle(0);
            },
            error: function (error) {
                thisMain.prop('disabled', false);
                loaderToggle(0);
            }
        });
    });
});

function populateSelect2(selector, items, type, value) {
    var $select = $(selector);
    $select.empty(); // Clear existing options
    $select.append('<option></option>'); // Add placeholder for Select2

    $.each(items, function(index, item) {

        var isShift = (type === 'selectedShift');
        var title = isShift ? item.shift_start_time + ' - ' + item.shift_end_time : (item.name || item.id);
        var dataShiftDay = isShift ? 'data-shift-day="' + item.shift_start_day + ' - ' + item.shift_end_day + '"': '';
        var optionValue = isShift ? title : item.id;

        $select.append('<option value="' + optionValue + '" ' + dataShiftDay + '>' + title + '</option>');
    });

    $select.select2({
        placeholder: "Select an option", // Customize placeholder as needed
        allowClear: true
    });
}
