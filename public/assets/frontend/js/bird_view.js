
$(document).ready(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    fetchData();
    setInterval(() => {
        fetchData();
    }, 7500);

    $(document).on('click', '.machine_box', function() {
        var id = $(this).data('id');
        showModalData(id);
    });

    $('#machineModal').on('hidden.bs.modal', function (e) {
        $("#dynamicModalId").val('');
    });

});

function fetchData() {
    $.ajax({
        url: birdviewUrl,
        type: 'POST',
        data:{},
        dataType: 'json',
        beforeSend: function () {
        },
        success: function (response) {
            // console.log(response);
            if(response.status) {
                // $("#shift_name").html(response.shiftName);
                $("#shift_start_end_time").html("Shift: "+response.shiftStartEnd);
                $("#machine_data").html(response.html);

                setTimeout(() => {
                    setBirdviewHeader();
                }, 250);

                if ($("#shiftMatching").val() != response.shiftStartEnd) {
                    $("#machineModal").modal('hide');
                } else {
                    setTimeout(() => {
                        if($("#dynamicModalId").val()) {                        
                            showModalData($("#dynamicModalId").val());
                        }
                    }, 750);
                }
                $("#shiftMatching").val(response.shiftStartEnd);
            } else {
                toastError(response.message);

                const now = new Date();
                const dateFormat = (num) => String(num).padStart(2, '0');
                
                let hours = now.getHours();
                let ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12; // Convert 24-hour to 12-hour format
                
                const formattedDateTime = `${dateFormat(now.getDate())}/${dateFormat(now.getMonth() + 1)}/${now.getFullYear()} ` +
                    `${dateFormat(hours)}:${dateFormat(now.getMinutes())} ${ampm}`;
                
                $("#deviceTime").html(formattedDateTime);                
            }
        },
        error: function(xhr, status, error) {
            console.error("An error occurred while fetching the data.");
            console.error(xhr.responseText);
        }
    });
}

function setBirdviewHeader() {
    var headerData = JSON.parse($("#birdHeaderData").val());
    // console.log(headerData);
    $("#averageMachineEfficiency").html((headerData.averageMachineEfficiency < 10 ? '0'+headerData.averageMachineEfficiency : headerData.averageMachineEfficiency) + '%');
    $("#averageMachineSpeed").html(headerData.averageMachineSpeed < 10 ? '0'+headerData.averageMachineSpeed : headerData.averageMachineSpeed);
    $("#totalMachineRunning").html(headerData.totalMachineRunning < 10 ? '0'+headerData.totalMachineRunning : headerData.totalMachineRunning);
    $("#deviceTime").html(headerData.deviceTime);
    $("#totalMachineStop").html(headerData.totalMachineStop < 10 ? '0'+headerData.totalMachineStop : headerData.totalMachineStop);
    $("#totalGreenEfficiency").html(headerData.totalGreenEfficiency < 10 ? '0'+headerData.totalGreenEfficiency : headerData.totalGreenEfficiency);
    $("#totalYellowEfficiency").html(headerData.totalYellowEfficiency < 10 ? '0'+headerData.totalYellowEfficiency : headerData.totalYellowEfficiency);
    $("#totalOrangeEfficiency").html(headerData.totalOrangeEfficiency < 10 ? '0'+headerData.totalOrangeEfficiency : headerData.totalOrangeEfficiency);
    $("#totalRedEfficiency").html(headerData.totalRedEfficiency < 10 ? '0'+headerData.totalRedEfficiency : headerData.totalRedEfficiency);
}

function showModalData(id) {
    var modalData = JSON.parse($("#birdModalData"+id).val());
    $("#dynamicModalId").val(id);
    // console.log(modalData);
    $("#machine_name").html(modalData.name);
    $("#machine_titleBackground").removeClass().addClass('machine_title ' + modalData.backgroundClass);
    $("#machine_dot").removeClass().addClass('dot ' + modalData.dotBackgroundClass);
    $("#machine_efficiency").html((modalData.efficiency < 10 ? '0'+modalData.efficiency : modalData.efficiency) + '%');
    $("#machine_speed").html(modalData.speed < 10 ? '0'+modalData.speed : modalData.speed);
    $("#machine_running").html(modalData.running);
    $("#machine_stop").html(modalData.stop);
    $("#machine_total_running").html(modalData.total_running);
    $("#machine_total_time").html(modalData.total_time);
    $("#machine_totalPickThisShift").html(modalData.pickThisShift < 10 ? '0'+modalData.pickThisShift : numberFormat(modalData.pickThisShift));
    $("#machine_totalPickToday").html(modalData.pickThisDay < 10 ? '0'+modalData.pickThisDay : numberFormat(modalData.pickThisDay));
    $("#machine_stoppages").html(modalData.stoppage < 10 ? '0'+modalData.stoppage : modalData.stoppage);
    $("#machineModal").modal('show');
}

function numberFormat(number) {
    var number = parseInt(number);
    return number.toLocaleString('en-IN');
}

// Configure the base Toast instance
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

function toastSuccess(message = 'Successful...', title = 'Success!') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'success',
        showCloseButton: true,
        position: 'bottom-start'
    });
}

function toastError(message = 'Oops!, Something went wrong.', title = 'Error!') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'error',
        showCloseButton: true,
        position: 'bottom-start'
    });
}
