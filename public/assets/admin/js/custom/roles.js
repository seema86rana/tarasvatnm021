"use strict";

$(document).ready(function () {

    let $roles_dt = null;
    if ($('.roles-dt').length) {
        $roles_dt = $('.roles-dt').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            ajax: roleUrl,
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
                { data: 'serial_no', name: 'serial_no', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'name', width: '25%' },
                { data: 'status', name: 'status', searchable: false, orderable: false, width: '20%' },
                { data: 'created_at', name: 'created_at', width: '25%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%' }
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

    $(document).on('click', '.add-role', function (e) {
        e.preventDefault();
        loaderToggle(1);
        $.ajax({
            url: roleUrl + '/create',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                loaderToggle(1);
            },
            complete: function (response) {
                let result = response.responseJSON;
                if (result.statusCode) {
                    make_modal("add-role-modal", result.html, true, "modal-xl");
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

    $(document).on("click", "#save-role", function(e) {
        e.preventDefault();

        if(!validation("add-role-form")) {
            return false;
        }
        // let formData = new FormData(document.getElementById("add-role-form"));
        let formData = new FormData($("#add-role-form").get(0));

        $.ajax({
            url: roleUrl,
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
                    $roles_dt.ajax.reload();
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

    $(document).on('click', '.edit-role', function (event) {
        event.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let edit_callback = function () {
                $.ajax({
                    url: roleUrl + '/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        loaderToggle(1);
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            make_modal("edit-role-modal", result.html, true, "modal-xl");
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

    $(document).on("click", "#update-role", function(e) {
        e.preventDefault();

        if(!validation("edit-role-form")) {
            return false;
        }
        let id = $(this).attr('data-id');
        let formData = new FormData(document.getElementById("edit-role-form"));
        // let formData = new FormData($("#edit-role-form").get(0));

        $.ajax({
            url: roleUrl + '/' + id,
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
                    $roles_dt.ajax.reload();
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

    $(document).on('click', '.delete-role', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        if (id) {
            let delete_callback = function () {
                $.ajax({
                    url: roleUrl + '/' + id,
                    type: 'DELETE',
                    dataType: 'json',
                    beforeSend: function () {
                        loaderToggle(1);
                    },
                    complete: function (response) {
                        let result = response.responseJSON;
                        if (result.statusCode) {
                            toast_success(result.message, 'Deleted!');
                            $roles_dt.ajax.reload();
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

    $(document).on('change', '.status-role', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        let status = $(this).val();
        if (id) {
            $.ajax({
                url: roleUrl + '/' + id,
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
                        $roles_dt.ajax.reload();
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

});

function sw() {
    var switches = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
    switches.forEach(function (html) {
        var switchery = new Switchery(html);
    });
}
