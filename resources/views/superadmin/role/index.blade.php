@extends('layout.main')

@section('title')
{{ $title }}
@endsection

@section('page_header')
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>
                <i class="icon-menu6 position-left"></i>
                <span class="text-semibold">Roles</span>
            </h4>
        </div>
    </div>

    @if ( isset( $breadcrumbs ) )
    @include('layout.include.breadcrumb', ['breadcrumbs' => $breadcrumbs])
    @endif
</div>
@endsection

@section('content')
<div class="panel panel-flat">
    <div class="panel-heading">
        <h5 class="panel-title">Role's list</h5>
        <div class="heading-elements">
            <button class="btn btn-theme-dark btn-labeled add-role">
                <b><i class="icon-plus-circle2"></i></b> Add Role
            </button>
            <!-- <button class="btn btn-theme-dark btn-labeled">
                <b><i class="fa fa-file-excel-o" aria-hidden="true"></i></b> Export XLSX
            </button> -->
        </div>
    </div>
    <div class="row px-4 removable-flash-messages">
        <div class="col-md-12">
            @include('layout.include.message')
        </div>
    </div>
    <div class="panel-body table-responsive">
        <table class="table table-bordered table-striped datatable-scroller roles-dt" id="roles-dt">
            <thead>
                <tr>
                    <th>Serial No.</th>
                    <th>Role Name</th>
                    <th>Status</th>
                    <th>Created At</th>
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
	let roleUrl = "{{ route('roles.index') }}";
</script>
<script type="text/javascript" src="{{asset('assets/admin/js/plugins/forms/styling/switchery.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/plugins/tables/datatables/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/plugins/tables/datatables/extensions/scroller.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/plugins/forms/selects/select2.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/admin/js/custom/roles.js') }}"></script>

@endsection