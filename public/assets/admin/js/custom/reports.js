"use strict";

let user_id = "";
let device_id = "";
let node_id = "";
let machine_id = "";
let date = "";

$(document).ready(function () {

    loaderToggle(1);
    let $reports_dt = null;
    if ($('.reports-dt').length) {
        $reports_dt = $('.reports-dt').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            // ajax: reportUrl,
            ajax: {
                url: reportUrl,
                type: "GET",
                data: function(d) {
                    d.user_id = user_id;
                    d.device_id = device_id;
                    d.node_id = node_id;
                    d.machine_id = machine_id;
                    d.date = date;
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
                loaderToggle(0); // Close the loader after the data has loaded
            },
            columns: [
                { data: 'serial_no', name: 'serial_no', orderable: false, searchable: false, width: '5%' },
                { data: 'user', name: 'user', width: '10%' },
                { data: 'device', name: 'device', width: '10%' },
                { data: 'node', name: 'node', width: '10%' },
                { data: 'machine', name: 'machine', width: '10%' },
                { data: 'shift', name: 'shift', orderable: false, searchable: false, width: '10%' },
                { data: 'device_datetime', name: 'device_datetime', orderable: false, searchable: false, width: '15%' },
                { data: 'machine_datetime', name: 'machine_datetime', orderable: false, searchable: false, width: '15%' },
                { data: 'mode', name: 'mode', orderable: false, searchable: false, width: '5%' },
                { data: 'speed', name: 'speed', orderable: false, searchable: false, width: '5%' },
                { data: 'pick', name: 'pick', orderable: false, searchable: false, width: '5%' },
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
            data: {user_id, device_id, node_id, machine_id, date},
            beforeSend: function () {
            },
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("filter-report-modal", result.html, true, "modal-lg");
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

    $(document).on("click", "#save-report", async function(e) {
        e.preventDefault();

        let thisMain = $(this);
        thisMain.prop('disabled', true);
        await loaderToggle(1);

        user_id = $("#user_id").val();
        device_id = $("#device_id").val();
        node_id = $("#node_id").val();
        machine_id = $("#machine_id").val();
        date = $("#date").val();

        setTimeout(() => {
            $(".modal").modal("hide");
            $reports_dt.ajax.reload();
        }, 250);

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
            url: reportUrl,
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
                        populateSelect2("#node_id", data.nodeMaster);
                        populateSelect2("#machine_id", data.machineMaster);
                    } 
                    else if(thisMain.attr('name') == 'device_id') {
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

    $(document).on("click", ".reload-report", async function (e) {
        // Add spinning effect to the icon
        $(this).find('.fa-refresh').addClass('fa-spin');
        
        // Reload the DataTable
        await $reports_dt.ajax.reload();
    
        // Remove spinning effect after 1 second
        setTimeout(() => {
            $(this).find('.fa-refresh').removeClass('fa-spin');
        }, 1000);
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
        var title = "";

        if (item.name) {
            title = item.name;
        } 
        else if(item.machine_display_name) {
            title = item.machine_display_name;
        } 
        else {
            title = item.id;
        }

        $select.append('<option value="' + item.id + '">' + title + '</option>');
    });

    $select.select2({
        placeholder: "Select an option", // Customize placeholder as needed
        allowClear: true
    });
}
