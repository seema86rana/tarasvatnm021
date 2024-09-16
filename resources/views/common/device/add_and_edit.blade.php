

<!-- Bootstrap Timepicker CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" rel="stylesheet">
<!-- Bootstrap Timepicker JS (Load after jQuery) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
<style>
.bootstrap-timepicker-widget {
    width: 100px;   /* Adjust the width */
    height: auto;   /* Automatically scale height */
    font-size: 14px; /* Adjust font size for a more compact appearance */
}
.bootstrap-timepicker-widget table td a {
    padding: 5px 10px;  /* Adjust padding for the time blocks */
    border-radius: 5px; /* Optional: Rounded corners */
}
.bootstrap-timepicker-widget .dropdown-menu {
    padding: 0;
    border-radius: 5px;
}
.bootstrap-timepicker-widget.dropdown-menu {
    z-index: 1051 !important; /* Ensure it's above the modal */
}
</style>

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $modal_title}}</h5>
    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form class="{{ isset($device) ? 'edit-device-form' : 'add-device-form' }}" id="{{ isset($device) ? 'edit-device-form' : 'add-device-form' }}" action="#" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @if ( isset( $device ) )
            <input type="hidden" name="id" id="id" value="{{ $device->id }}" />
        @endif
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="name">Device Name <small class="req text-danger">*</small></label>
                    <input type="text" name="name" class="form-control name" id="name" placeholder="Enter device name" tabindex="1" value="{{ isset($device) ? $device->name : old('name') }}" required autofocus />
                </div>
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="user_id">Select User <small class="req text-danger">*</small></label>
                    <select class="form-control select2" name="user_id"  id="user_id" tabindex="2" required>
                        <option value="">Select a user</option>
                        @foreach ($user as $value)
                            @if(isset($device))
                                @if ($device->user_id == $value->id)
                                    <option value="{{$value->id }}" selected>{{ $value->name }}</option>
                                @else
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endif
                            @else
                                <option value="{{$value->id}}">{{$value->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="device_id">Device ID <small class="req text-danger">*</small></label>
                    <input type="text" name="device_id" class="form-control device_id" id="device_id" placeholder="Enter device id" tabindex="3" value="{{ isset($device) ? $device->device_id : old('device_id') }}" required autofocus />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12 text-center">
                    SHIFT SECTION
                    <hr />
                </div>
            </div>
        </div>
        @if(isset($device))
            @php
                $shift = json_decode($device->shift);
            @endphp
            @foreach($shift as $key => $value)
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="col-form-label label_text text-lg-right" for="shift_name">Shift Name <small class="req text-danger">*</small></label>
                            <input type="text" value="{{ $value->shift_name }}" name="shift_name[]" class="form-control shift_name" id="shift_name" placeholder="Enter shift name" required readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="col-form-label label_text text-lg-right" for="shift_start">Shift Start <small class="req text-danger">*</small></label>
                            <input type="text" value="{{ $value->shift_start }}" name="shift_start[]" class="form-control shift_start timepicker" id="shift_start" placeholder="Enter shift_start" required />
                        </div>
                        <div class="col-md-3">
                            <label class="col-form-label label_text text-lg-right" for="shift_end">Shift End <small class="req text-danger">*</small></label>
                            <input type="text" value="{{ $value->shift_end }}" name="shift_end[]" class="form-control shift_end timepicker" id="shift_end" placeholder="Enter shift_end" required />
                        </div>
                        <div class="col-md-2">
                            <p style="margin-bottom: 5px;">&nbsp;</p>
                            @if($key != 0)
                                <button type="button" class="btn btn-theme-dark minus-shift">
                                    <b><i class="icon-minus-circle2"></i></b>
                                </button>
                            @endif
                            &nbsp;&nbsp;
                            <button type="button" class="btn btn-theme-dark add-shift">
                                <b><i class="icon-plus-circle2"></i></b>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="form-group">
                <div class="row">
                    <div class="col-md-4">
                        <label class="col-form-label label_text text-lg-right" for="shift_name">Shift Name <small class="req text-danger">*</small></label>
                        <input type="text" value="shift 1" name="shift_name[]" class="form-control shift_name" id="shift_name" placeholder="Enter shift name" required readonly />
                    </div>
                    <div class="col-md-3">
                        <label class="col-form-label label_text text-lg-right" for="shift_start">Shift Start <small class="req text-danger">*</small></label>
                        <input type="shift_start" name="shift_start[]" class="form-control shift_start timepicker" id="shift_start" placeholder="Enter shift_start" required />
                    </div>
                    <div class="col-md-3">
                        <label class="col-form-label label_text text-lg-right" for="shift_end">Shift End <small class="req text-danger">*</small></label>
                        <input type="shift_end" name="shift_end[]" class="form-control shift_end timepicker" id="shift_end" placeholder="Enter shift_end" required />
                    </div>
                    <div class="col-md-2">
                        <p style="margin-bottom: 5px;">&nbsp;</p>
                        <!-- <button type="button" class="btn btn-theme-dark minus-shift">
                            <b><i class="icon-minus-circle2"></i></b>
                        </button> -->
                        &nbsp;&nbsp;
                        <button type="button" class="btn btn-theme-dark add-shift">
                            <b><i class="icon-plus-circle2"></i></b>
                        </button>
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group mb-2 text-right">
            <button type="button" class="btn btn-theme-dark close-modal" style="float: inline-start;">
                <i class="icon-arrow-left13"></i> Back
            </button>
            <button type="button" data-id="{{ isset($device) ? $device->id : '' }}" class="btn btn-theme-dark {{ isset($device) ? 'update-device' : 'save-device' }}" id="{{ isset($device) ? 'update-device' : 'save-device' }}" style="float: inline-end;">
                <i class="icon-check"></i> {{ isset($device) ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>
</div>
<div class="modal-footer">
</div>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            placeholder: 'Select a user',
            allowClear: true,
            width: '100%'
        });

       setTimeout(() => {
        $('.timepicker').timepicker({
            showMeridian: true,  // 12-hour format
            minuteStep: 5,       // Allow selecting minutes in increments of 1
            showInputs: false,   // Prevent direct input and show dropdown
            defaultTime: 'current'  // Set current time as default
        });
       }, 500);
    });
</script>