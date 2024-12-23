@extends('layouts.backend')

@section('title')
{{ $title }}
@endsection

@section('page_header')
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>
                <i class="icon-stack2 position-left"></i>
                <span class="text-semibold">Devices</span>
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
        <h5 class="panel-title">Device's list</h5>
        <div class="heading-elements">
            <button class="btn btn-theme-dark btn-labeled add-device">
                <b><i class="icon-plus-circle2"></i></b> Add Device
            </button>
            <!-- <button class="btn btn-theme-dark btn-labeled">
                <b><i class="fa fa-file-excel-o" aria-hidden="true"></i></b> Export XLSX
            </button> -->
        </div>
    </div>
    <div class="row px-4 removable-flash-messages">
        <div class="col-md-12">
            @include('layouts.include.backend.message')
        </div>
    </div>
    <div class="panel-body table-responsive">
        <table class="table table-bordered table-striped datatable-scroller devices-dt" id="devices-dt">
            <thead>
                <tr>
                    <th>Serial No.</th>
                    <th>Device Name</th>
                    <th>User</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('footer_js')
<script type="text/javascript">
	let deviceUrl = "{{ route('devices.index') }}";
</script>
<script type="text/javascript" src="{{asset('assets/backend/js/plugins/forms/styling/switchery.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/scroller.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/backend/js/plugins/forms/selects/select2.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/backend/js/custom/devices.js') }}"></script>
@endsection