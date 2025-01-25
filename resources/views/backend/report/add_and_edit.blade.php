
<!-- Bootstrap Datepicker CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
<!-- Bootstrap Datepicker JS (Load after jQuery) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $modal_title}}</h5>
    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php
    // echo "<pre>";
    // print_r($user_id);
    // print_r($device_id);
    // print_r($node_id);
    // print_r($machine_id);
    // print_r($date);
    // die;
?>
<div class="modal-body">
    <form class="filter-report-form" id="filter-report-form" action="#" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="user_id">Select User</label>
                    <select class="form-control select2 onchange_function" name="user_id"  id="user_id" tabindex="1">
                        <option value="">Select a user</option>
                        @foreach ($user as $value)
                            @if(isset($user_id))
                                @if ($user_id == $value->id)
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
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="device_id">Select Device</label>
                    <select class="form-control select2 onchange_function" name="device_id"  id="device_id" tabindex="2">
                        <option value="">Select a device</option>
                        @foreach ($device as $value)
                            @if(isset($device_id))
                                @if ($device_id == $value->id)
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
            <div class="row mt-5">
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="node_id">Select Node</label>
                    <select class="form-control select2 onchange_function" name="node_id"  id="node_id" tabindex="3">
                        <option value="">Select a node</option>
                        @foreach ($nodeMaster as $value)
                            @if(isset($node_id))
                                @if ($node_id == $value->id)
                                    <option value="{{$value->id }}" selected>{{ $value->name }}</option>
                                @else
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endif
                            @else
                                <option value="{{$value->id}}">{{ $value->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="machine_id">Select Machine</label>
                    <select class="form-control select2" name="machine_id"  id="machine_id" tabindex="4">
                        <option value="">Select a machine</option>
                        @foreach ($machineMaster as $value)
                            @if(isset($machine_id))
                                @if ($machine_id == $value->id)
                                    <option value="{{$value->id }}" selected>{{ $value->n }}</option>
                                @else
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endif
                            @else
                                <option value="{{$value->id}}">{{ $value->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="node_id">Select Date</label>
                    <input type="text" class="form-control datepicker" value="{{ isset($date) ? date('m/d/Y', strtotime($date)) : '' }}" name="date" id="date" placeholder="Select date">
                </div>
            </div>
        </div>
        <div class="form-group mb-2 text-right">
            <button type="button" class="btn btn-theme-dark close-modal" style="float: inline-start;">
                <i class="icon-arrow-left13"></i> Back
            </button>
            <button type="button" class="btn btn-theme-dark save-report" id="save-report" style="float: inline-end;">
                <i class="icon-check"></i> Submit
            </button>
        </div>
    </form>
</div>
<div class="modal-footer">
</div>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        });

        setTimeout(() => {
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',     // Customize the date format as needed
                autoclose: true,          // Close the picker automatically after selection
                todayHighlight: true,     // Highlight today's date
                endDate: new Date(),      // Set the maximum selectable date to today
            });
        }, 500);
    });
</script>