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
                <span class="text-semibold">Reports</span>
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
        <h5 class="panel-title">Report's list</h5>
        <div class="heading-elements">
            <button class="btn btn-theme-dark btn-labeled filter-report">
                <b><i class="fa fa-filter"></i></b> Filter
            </button>
            <button class="btn btn-theme-dark btn-labeled reload-report">
                <b><i class="fa fa-refresh"></i></b> Reload
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
        <table class="table table-bordered table-striped datatable-scroller reports-dt" id="reports-dt">
            <thead>
                <tr>
                    <th>Serial No.</th>
                    <th>User</th>
                    <th>Device</th>
                    <th>Node</th>
                    <th>Machine</th>
                    <th>Shift</th>
                    <th>Machine Datetime</th>
                    <th>Device Datetime</th>
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
<script type="text/javascript">
	let reportUrl = "{{ route('reports.index') }}";
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