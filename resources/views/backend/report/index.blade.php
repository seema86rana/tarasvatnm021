@extends('layouts.backend')

@section('title')
{{ $title }}
@endsection

@section('header_css')
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
    <style>
        .small-text {
            font-size: 0.8em; /* Makes text smaller */
            color: #777; /* Optional: Gray color for a subtle look */
        }
    </style>
@endsection

@section('page_header')
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>
                <i class="icon-stack2 position-left"></i>
                <span class="text-semibold">Machine Log Report</span>
            </h4>
        </div>
    </div>

    @if ( isset( $breadcrumbs ) )
    @include('layouts.include.backend.breadcrumb', ['breadcrumbs' => $breadcrumbs])
    @endif
</div>
@endsection

@section('content')

<div class="panel panel-flat">
    <div class="panel-heading">
        <h5>Filter Machine Log Report</h5>
    </div>
    <div class="panel-body">
        <form class="filter-report-form" id="filter-report-form" action="#" method="post" autocomplete="off">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <div class="row">
                    <div class="col-md-4 mt-3">
                        <label for="user_id">Select User</label>
                        <select class="form-control select2 onchange_function" name="user_id" id="user_id">
                            <option value="">Select a user</option>
                            @foreach ($user as $value)
                                <option value="{{ $value->id }}" {{ isset($user_id) && $user_id == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="device_id">Select Device</label>
                        <select class="form-control select2 onchange_function" name="device_id" id="device_id">
                            <option value="">Select a device</option>
                            @foreach ($device as $value)
                                <option value="{{ $value->id }}" {{ isset($device_id) && $device_id == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="select_shift">Select Shift</label>
                        <select class="form-control select2 onchange_function" name="select_shift" id="select_shift">
                            <option value="">Select a Shift</option>
                        </select>
                    </div>

                    <div class="col-md-4 mt-3">
                        <label for="node_id">Select Node</label>
                        <select class="form-control select2 onchange_function" name="node_id" id="node_id">
                            <option value="">Select a node</option>
                            @foreach ($nodeMaster as $value)
                                <option value="{{ $value->id }}" {{ isset($node_id) && $node_id == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="machine_id">Select Machine</label>
                        <select class="form-control select2" name="machine_id" id="machine_id">
                            <option value="">Select a machine</option>
                            @foreach ($machineMaster as $value)
                                <option value="{{ $value->id }}" {{ isset($machine_id) && $machine_id == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="dateRange">Select Date Range</label>
                        <input type="text" class="form-control" name="dateRange" id="dateRange" placeholder="Select date range">
                    </div>

                    <div class="col-md-12 mt-3 text-right" style="float: left;">
                        <label for="button">&nbsp;</label>
                        <br>
                        <button type="button" class="btn btn-theme-dark save-report" id="save-report">
                            <i class="icon-check"></i> Submit
                        </button>
                        &nbsp;&nbsp;&nbsp;
                        <button type="reset" class="btn btn-default" id="clear-form-report">
                            <i class="icon-reset"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-heading">
        <h5 class="panel-title">Machine Log Report's list</h5>
        <div class="heading-elements d-flex">
            <!-- <button class="btn btn-theme-dark btn-labeled filter-report">
                <b><i class="fa fa-filter"></i></b> Filter
            </button> -->
            <button class="btn btn-theme-dark btn-labeled reload-report mr-2">
                <b><i class="fa fa-refresh"></i></b> Reload
            </button>
            <!-- <button class="btn btn-theme-dark btn-labeled">
                <b><i class="fa fa-file-excel-o" aria-hidden="true"></i></b> Export XLSX
            </button> -->
            <form id="export-machine-log-report-form" action="{{ route('view-reports.store') }}" method="post">
                @csrf
                <input type="hidden" name="type" value="exportMachineLog">
                <input type="hidden" name="user_id" value="">
                <input type="hidden" name="device_id" value="">
                <input type="hidden" name="select_shift" value="">
                <input type="hidden" name="select_shift_day" value="">
                <input type="hidden" name="node_id" value="">
                <input type="hidden" name="machine_id" value="">
                <input type="hidden" name="dateRange" value="">
                <button id="export-machine-log-report-button" class="btn btn-theme-dark btn-labeled">
                    <b><i class="fa fa-file-excel-o" aria-hidden="true"></i></b> Export XLSX
                </button>
            </form>
        </div>
    </div>
    <div class="row px-4 removable-flash-messages">
        <div class="col-md-12">
            @include('layouts.include.backend.message')
        </div>
    </div>
    <div class="panel-body table-responsive">
        <table class="table table-bordered table-striped datatable-scroller reports-dt" id="reports-dt">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Device</th>
                    <th>Machine</th>
                    <th>Total Running</th>
                    <th>Total Time</th>
                    <th>Efficiency</th>
                    <th>Shift Detail</th>
                    <th>Device Datetime</th>
                    <th>Machine Datetime</th>
                    <th>Last Stop</th>
                    <th>Last Running</th>
                    <th>No. of Stoppage</th>
                    <th>Mode</th>
                    <th>Speed</th>
                    <th>Pick</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('footer_js')
<!-- Moment.js (Required for Date Range Picker) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- Date Range Picker CSS & JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>

<script type="text/javascript">
	let reportUrl = "{{ route('view-reports.index') }}";

    $(document).ready(function () {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        });

        let endDate = new Date();
        let startDate = new Date();
        startDate.setHours(startDate.getHours() - 24); // Subtract 24 hours

        $('#dateRange').daterangepicker({
            autoUpdateInput: true,
            timePicker: true,
            timePicker24Hour: false,
            timePickerSeconds: false,
            startDate: startDate,
            endDate: endDate,
            locale: {
                format: 'MM/DD/YYYY h:mm A',
                cancelLabel: 'Clear' // Change default cancel button text to "Clear"
            },
            maxDate: endDate // Prevent future dates
        });

        // Event to clear input field when "Clear" button is clicked
        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val(''); // Clear the input field
        });
    });
</script>

<script type="text/javascript" src="{{asset('assets/backend/js/plugins/forms/styling/switchery.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/scroller.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/selects/select2.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/backend/js/custom/reports.js') }}"></script>
@endsection