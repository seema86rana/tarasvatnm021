"use strict";

$(document).ready(function () {

    let $users_dt = null;
    if ($('.users-dt').length) {
        $users_dt = $('.users-dt').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            ajax: userUrl,
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
                { data: 'name', name: 'name', width: '10%' },
                { data: 'role', name: 'role', width: '5%' },
                { data: 'phone_number', name: 'phone_number', width: '10%' },
                { data: 'email', name: 'email', width: '15%' },
                { data: 'status', name: 'status', searchable: false, orderable: false, width: '10%' },
                { data: 'created_at', name: 'created_at', width: '10%' },
                { data: 'created_by', name: 'created_by', width: '10%' },
                { data: 'verified_at', name: 'verified_at', width: '10%' },
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

    $(document).on('click', '.add-user', function (e) {
        e.preventDefault();
        loaderToggle(1);
        $.ajax({
            url: userUrl + '/create',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
            },
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("add-user-modal", result.html, true, "modal-xl");
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

    $(document).on("click", "#save-user", async function(e) {
        e.preventDefault();

        if(!validation("add-user-form")) {
            return false;
        }
        let thisMain = $(this);
        // let formData = new FormData(document.getElementById("add-user-form"));
        let formData = new FormData($("#add-user-form").get(0));
        thisMain.prop('disabled', true);
        await loaderToggle(1);
        $.ajax({
            url: userUrl,
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
                    $users_dt.ajax.reload();
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

    $(document).on('click', '.edit-user', function (event) {
        event.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let edit_callback = function () {
                loaderToggle(1);
                $.ajax({
                    url: userUrl + '/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            make_modal("edit-user-modal", result.html, true, "modal-xl");
                        } else {
                            toast_error(result.message);
                        }
                        loaderToggle(0);
                    },
                    error: function (error) {
                        toast_error();
                    }
                });
            };
            swal_confirmation(edit_callback, 'Are you sure?', 'You won\'t be able to revert this!', 'Yes, edit it!');
        } else {
            toast_error();
        }
    });

    $(document).on("click", "#update-user", async function(e) {
        e.preventDefault();

        if(!validation("edit-user-form")) {
            return false;
        }
        let thisMain = $(this);
        let id = thisMain.attr('data-id');
        let formData = new FormData(document.getElementById("edit-user-form"));
        // let formData = new FormData($("#edit-user-form").get(0));
        thisMain.prop('disabled', true);
        await loaderToggle(1);
        $.ajax({
            url: userUrl + '/' + id,
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
                    $users_dt.ajax.reload();
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

    $(document).on('click', '.delete-user', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let delete_callback = function () {
                loaderToggle(1);
                $.ajax({
                    url: userUrl + '/' + id,
                    type: 'DELETE',
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            toast_success(result.message, 'Deleted!');
                            $users_dt.ajax.reload();
                        } else {
                            toast_error(result.message);
                        }
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

    $(document).on('change', '.status-user', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        let status = $(this).val();
        if (id) {
            loaderToggle(1);
            $.ajax({
                url: userUrl + '/' + id,
                type: 'GET',
                data: {status},
                dataType: 'json',
                beforeSend: function () {
                },
                complete: function (response) {
                    let result = response.responseJSON;
                    if (result.statusCode) {
                        toast_success(result.message, 'Success!');
                        $users_dt.ajax.reload();
                    } else {
                        toast_error(result.message);
                    }
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
