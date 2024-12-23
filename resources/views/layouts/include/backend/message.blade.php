@if ( Session::has('success') )
    <div class="alert alert-success" role="alert" id="alert-success">
        {{ Session::get('success') }}
    </div>
@endif
@if ( Session::has('error') )
    <div class="alert alert-danger" role="alert" id="alert-danger">
        {{ Session::get('error') }}
    </div>
@endif