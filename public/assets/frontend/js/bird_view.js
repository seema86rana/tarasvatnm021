
$(document).ready(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    fetchData();
    setInterval(() => {
        fetchData();
    }, 5000);

    $(document).on('click', '.machine_box', function() {
        var id = $(this).data('id');
        var modalData = JSON.parse($("#birdModalData"+id).val());
        // console.log(modalData);
        $("#machine_name").html(modalData.name);
        $("#machine_titleBackground").removeClass().addClass('machine_title ' + modalData.backgroundClass);
        $("#machine_dot").removeClass().addClass('dot ' + modalData.dotBackgroundClass);
        $("#machine_efficiency").html(modalData.efficiency < 10 ? '0'+modalData.efficiency : modalData.efficiency);
        $("#machine_speed").html(modalData.speed < 10 ? '0'+modalData.speed : modalData.speed);
        $("#machine_running").html(modalData.running);
        $("#machine_stop").html(modalData.stop);
        $("#machine_totalPickThisShift").html(modalData.pickThisShift < 10 ? '0'+modalData.pickThisShift : modalData.pickThisShift);
        $("#machine_totalPickToday").html(modalData.pickThisDay < 10 ? '0'+modalData.pickThisDay : modalData.pickThisDay);
        $("#machine_stoppages").html(modalData.stoppage < 10 ? '0'+modalData.stoppage : modalData.stoppage);
        $("#machineModal").modal('show');
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
                $("#shift_name").html(response.shiftName);
                $("#shift_start_end_time").html(response.shiftStartEnd);
                $("#machine_data").html(response.html);
                setTimeout(() => {
                    setBirdviewHeader();
                }, 250);
            } else {
                toastError(response.message);
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
    $("#averageMachineEfficiency").html(headerData.averageMachineEfficiency < 10 ? '0'+headerData.averageMachineEfficiency : headerData.averageMachineEfficiency);
    $("#averageMachineSpeed").html(headerData.averageMachineSpeed < 10 ? '0'+headerData.averageMachineSpeed : headerData.averageMachineSpeed);
    $("#totalMachineRunning").html(headerData.totalMachineRunning < 10 ? '0'+headerData.totalMachineRunning : headerData.totalMachineRunning);
    $("#totalMachineStop").html(headerData.totalMachineStop < 10 ? '0'+headerData.totalMachineStop : headerData.totalMachineStop);
    $("#totalGreenEfficiency").html(headerData.totalGreenEfficiency < 10 ? '0'+headerData.totalGreenEfficiency : headerData.totalGreenEfficiency);
    $("#totalYellowEfficiency").html(headerData.totalYellowEfficiency < 10 ? '0'+headerData.totalYellowEfficiency : headerData.totalYellowEfficiency);
    $("#totalOrangeEfficiency").html(headerData.totalOrangeEfficiency < 10 ? '0'+headerData.totalOrangeEfficiency : headerData.totalOrangeEfficiency);
    $("#totalRedEfficiency").html(headerData.totalRedEfficiency < 10 ? '0'+headerData.totalRedEfficiency : headerData.totalRedEfficiency);
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
    });
}

function toastError(message = 'Oops!, Something went wrong.', title = 'Error!') {
    Toast.fire({
        title: title,
        text: message,
        icon: 'error',
    });
}
