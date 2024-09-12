@extends('layout.main')

@section('title')
{{ $title }}
@endsection

@section('page_header')
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>
                <i class="icon-home4 position-left"></i>
                <span class="text-semibold">Dashboard</span>
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
        <h5 class="panel-title">Dashboard</h5>
    </div>
    <div class="row px-4 removable-flash-messages">
        <div class="col-md-12">
            @include('layout.include.message')
        </div>
    </div>
    <div class="panel-body table-responsive text-center">
        <h1>Welcome {{ Auth::user()->name }}!</h1>
    </div>
</div>
@endsection

@section('footer_js')
@endsection