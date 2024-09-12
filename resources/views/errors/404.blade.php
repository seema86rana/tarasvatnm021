@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><p></p></div>

                <div class="card-body text-center">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <h1>404</h1>
                    <h2>Oops! Page Not Found</h2>
                    <p>The page you are looking for might have been removed, had its name changed, or is temporarily
                        unavailable.</p>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-primary">Go Back Home</a>
                </div>

                <div class="card-footer"><p></p></div>
            </div>
        </div>
    </div>
</div>
@endsection
