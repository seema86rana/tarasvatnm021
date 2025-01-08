@extends('layouts.auth')

@section('title', 'Reset Password')
@section('content')
<header class="ex-header">
    <div class="container">
        <div class="row">
            <div class="col-xl-10 offset-xl-1">
                <h1 class="text-center">Reset Password</h1>
            </div> 
        </div> 
    </div> 
</header>
<div class="container">
    <div class="ex-form-1 pt-5 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 offset-xl-3">
                    <div class="text-box mt-5 mb-5">
                        <!-- Password Reset Form -->
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <div class="mb-4 form-floating">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                    placeholder="name@example.com">
                                <label for="email">Email Address</label>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <button type="submit" class="form-control-submit-button">Send Password Reset Link</button>

                        </form>
                        <!-- end of password reset form -->

                    </div> <!-- end of text-box -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div>
</div>
@endsection
