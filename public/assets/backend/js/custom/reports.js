"use strict";

let user_id = "";
let device_id = "";
let select_shift = "";
let select_shift_day = "";
let node_id = "";
let machine_id = "";
let dateRange = "";

$(document).ready(function () {

    loaderToggle(1);
    let $reports_dt = null;
    if ($('.reports-dt').length) {
        $reports_dt = $('.reports-dt').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            // searching: false,
            // ordering: false,
            ajax: {
                url: reportUrl,
                type: "GET",
                data: function(d) {
                    d.user_id = $("#user_id").val();
                    d.device_id = $("#device_id").val();
                    d.select_shift = $("#select_shift").val();
                    d.select_shift_day = $("#select_shift").find(":selected").data("shift-day");
                    d.node_id = $("#node_id").val();
                    d.machine_id = $("#machine_id").val();
                    d.dateRange = $("#dateRange").val();
                }
            },
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
            initComplete: function() {
                loaderToggle(0); // Turn off loader when DataTable finishes initialization
            },
            columns: [
                // orderable: true, searchable: false, width: '5%'
                { data: 'log_id', searchable: false, orderable: true, name: 'log_id', width: '5%' },
                { data: 'device', searchable: true, orderable: false, name: 'device', width: '10%' },
                { data: 'machine', searchable: true, orderable: false, name: 'machine', width: '5%' },
                { data: 'total_running', searchable: false, orderable: true, name: 'total_running', width: '5%' },
                { data: 'total_time', searchable: false, orderable: true, name: 'total_time', width: '5%' },
                { data: 'efficiency', searchable: false, orderable: true, name: 'efficiency', width: '5%' },
                { data: 'shift', searchable: false, orderable: false, name: 'shift', width: '10%' },
                { data: 'deviceDatetime', searchable: false, orderable: true, name: 'deviceDatetime', width: '10%' },
                { data: 'machineDatetime', searchable: false, orderable: true, name: 'machineDatetime', width: '10%' },
                { data: 'last_stop', searchable: false, orderable: true, name: 'last_stop', width: '5%' },
                { data: 'last_running', searchable: false, orderable: true, name: 'last_running', width: '5%' },
                { data: 'no_of_stoppage', searchable: false, orderable: true, name: 'no_of_stoppage', width: '5%' },
                { data: 'mode', searchable: false, orderable: true, name: 'mode', width: '5%' },
                { data: 'speed', searchable: false, orderable: true, name: 'speed', width: '5%' },
                { data: 'pick', searchable: false, orderable: false, name: 'pick', width: '5%' },
            ]
        });

        // Add placeholder to the datatable filter option
        $('.dataTables_filter input[type=search]').attr('placeholder', 'Type to search...');
    }

    $(document).on('click', '.filter-report', function (e) {
        e.preventDefault();
        loaderToggle(1);
        $.ajax({
            url: reportUrl + '/create',
            type: 'GET',
            dataType: 'json',
            data: {user_id, device_id, node_id, machine_id, dateRange},
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("filter-report-modal", result.html, true, "modal-lg");
                } else {
                    toast_error(result.message);
                }
                loaderToggle(0);
            },
            error: function () {
                toast_error();
                loaderToggle(0);
            }
        });
    });

    $(document).on("click", "#save-report", async function(e) {
        e.preventDefault();

        let thisMain = $(this);
        thisMain.prop('disabled', true);
        loaderToggle(1);

        // Update filter variables from inputs
        user_id = $("#user_id").val();
        device_id = $("#device_id").val();
        select_shift = $("#select_shift").val();
        select_shift_day = $("#select_shift").find(":selected").data("shift-day");
        node_id = $("#node_id").val();
        machine_id = $("#machine_id").val();
        dateRange = $("#dateRange").val();

        // Close modal and reload DataTable with a callback
        $(".modal").modal("hide");
        $reports_dt.ajax.reload(function() {
            // When reload finishes, re-enable the button and turn off loader
            thisMain.prop('disabled', false);
            loaderToggle(0);
        });
    });

    $(document).on("click", "#clear-form-report", async function(e) {
        e.preventDefault();

        let thisMain = $(this);
        thisMain.prop('disabled', true);
        loaderToggle(1);

        // Clear filter variables
        user_id = "";
        device_id = "";
        select_shift = "";
        select_shift_day = "";
        node_id = "";
        machine_id = "";
        dateRange = "";

        // Clear form inputs and trigger change if needed
        $("#user_id").val('').trigger('change');
        $("#device_id").val('').trigger('change');
        $("#select_shift").val('').trigger('change');
        $("#node_id").val('').trigger('change');
        $("#machine_id").val('').trigger('change');
        $("#dateRange").val('');

        // Close modal and reload DataTable with a callback
        $(".modal").modal("hide");
        $reports_dt.ajax.reload(function() {
            thisMain.prop('disabled', false);
            loaderToggle(0);
        });
    });

    $(document).on("change", ".onchange_function", async function(e) {
        e.preventDefault();

        let thisMain = $(this);
        thisMain.prop('disabled', true);

        var formData = new FormData();
        formData.append(thisMain.attr('name'), thisMain.val());

        loaderToggle(1);
        $.ajax({
            url: reportUrl,
            type: 'POST',
            cache: false,
            async: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            data: formData,
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
                        populateSelect2("#node_id", data.nodeMaster);
                        populateSelect2("#machine_id", data.machineMaster);
                        populateSelect2("#select_shift", data.deviceShift, 'selectedShift');
                    }
                    else if(thisMain.attr('name') == 'node_id') {
                        populateSelect2("#machine_id", data.machineMaster);
                    }
                } else {
                    $('.load-main').addClass('hidden');
                    toast_error(data.message, 'Error');
                }
                thisMain.prop('disabled', false);
                loaderToggle(0);
            },
            error: function () {
                thisMain.prop('disabled', false);
                loaderToggle(0);
            }
        });
    });

    $(document).on("click", ".reload-report", async function (e) {
        // Add spinning effect to the icon
        $(this).find('.fa-refresh').addClass('fa-spin');
        loaderToggle(1);
        
        // Reload the DataTable and then remove the spinning effect when finished
        $reports_dt.ajax.reload(function() {
            $(document).find('.fa-refresh').removeClass('fa-spin');
            loaderToggle(0);
        });
    });

    $(document).on("click", "#export-machine-log-report-button", async function(e) {
        e.preventDefault();
        await loaderToggle(1);
        
        var form = $("#export-machine-log-report-form");
    
        // Set form field values (ensure these variables are defined)
        form.find("[name='user_id']").val(user_id);
        form.find("[name='device_id']").val(device_id);
        form.find("[name='select_shift']").val(select_shift);
        form.find("[name='select_shift_day']").val(select_shift_day);
        form.find("[name='node_id']").val(node_id);
        form.find("[name='machine_id']").val(machine_id);
        form.find("[name='dateRange']").val(dateRange);
    
        // Submit the form
        form.submit();
        console.log("Form is submitting...");
        await loaderToggle(0);
    });

});

function sw() {
    var switches = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
    switches.forEach(function (html) {
        var switchery = new Switchery(html);
    });
}

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
