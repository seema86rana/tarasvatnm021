"use strict";

$(document).ready(function () {

    loaderToggle(1);
    let $devices_dt = null;
    if ($('.machines-dt').length) {
        $devices_dt = $('.machines-dt').DataTable({
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
            initComplete: function() {
                loaderToggle(0); // Close the loader after the data has loaded
            },
            columns: [
                { data: 'serial_no', name: 'serial_no', orderable: false, searchable: false, width: '5%' },
                { data: 'name', name: 'name', width: '20%' },
                { data: 'display_name', name: 'display_name', searchable: true, width: '20%' },
                { data: 'priority', name: 'priority', searchable: false, width: '10%' },
                { data: 'status', name: 'status', searchable: false, width: '10%' },
                { data: 'created_at', name: 'created_at', width: '20%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%' }
            ]
        });

        // Add placeholder to the datatable filter option
        $('.dataTables_filter input[type=search]').attr('placeholder', 'Type to search...');

        // Enable Select2 select for the length option
        /*
        $('.dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: 'auto'
        });
        */
    }

    $(document).on('click', '.add-machine', function (e) {
        e.preventDefault();
        loaderToggle(1);
        $.ajax({
            url: deviceUrl + '/create',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
            },
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("add-machine-modal", result.html, true, "modal-lg");
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

    $(document).on("click", "#save-machine", async function(e) {
        e.preventDefault();

        if(validation("add-machine-form") == false) {
            return false;
        }
        let thisMain = $(this);
        // let formData = new FormData(document.getElementById("add-machine-form"));
        let formData = new FormData($("#add-machine-form").get(0));
        thisMain.prop('disabled', true);
        await loaderToggle(1);
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
                thisMain.prop('disabled', false);
                loaderToggle(0);
            },
            error: function (error) {
                thisMain.prop('disabled', false);
                loaderToggle(0);
            }
        });
    });

    $(document).on('click', '.edit-machine', function (event) {
        event.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let edit_callback = function () {
                loaderToggle(1);
                $.ajax({
                    url: deviceUrl + '/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            make_modal("edit-machine-modal", result.html, true, "modal-lg");
                        } else {
                            toast_error(result.message);
                        }
                        loaderToggle(0);
                    },
                    error: function (error) {
                        toast_error();
                    }
                });
                loaderToggle(0);
            };
            swal_confirmation(edit_callback, 'Are you sure?', 'You won\'t be able to revert this!', 'Yes, edit it!');
        } else {
            toast_error();
        }
    });

    $(document).on("click", "#update-machine", async function(e) {
        e.preventDefault();

        if(!validation("edit-machine-form")) {
            return false;
        }
        let thisMain = $(this);
        let id = thisMain.attr('data-id');
        let formData = new FormData(document.getElementById("edit-machine-form"));
        // let formData = new FormData($("#edit-machine-form").get(0));
        thisMain.prop('disabled', true);
        await loaderToggle(1);
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
                thisMain.prop('disabled', false);
                loaderToggle(0);
            },
            error: function (error) {
                thisMain.prop('disabled', false);
                loaderToggle(0);
            }
        });
    });

    $(document).on('click', '.delete-machine', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let delete_callback = function () {
                loaderToggle(1);
                $.ajax({
                    url: deviceUrl + '/' + id,
                    type: 'DELETE',
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            toast_success(result.message, 'Deleted!');
                            $devices_dt.ajax.reload();
                        } else {
                            toast_error(result.message);
                        }
                        loaderToggle(0);
                    },
                    error: function (error) {
                        toast_error();
                    }
                });
                loaderToggle(0);
            };
            swal_confirmation(delete_callback);
        } else {
            toast_error();
        }
    });

    $(document).on('change', '.status-machine', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        let status = $(this).val();
        if (id) {
            loaderToggle(1);
            $.ajax({
                url: deviceUrl + '/' + id,
                type: 'GET',
                data: {status},
                dataType: 'json',
                beforeSend: function () {
                },
                complete: function (response) {
                    let result = response.responseJSON;
                    if (result.statusCode) {
                        toast_success(result.message, 'Success!');
                        $devices_dt.ajax.reload();
                    } else {
                        toast_error(result.message);
                    }
                    loaderToggle(0);
                },
                error: function (error) {
                    toast_error();
                }
            });
            loaderToggle(0);
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
