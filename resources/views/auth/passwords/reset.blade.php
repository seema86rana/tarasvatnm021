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
                        <!-- Reset Password Form -->
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-4 form-floating">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ $email ?? old('email') }}" required autocomplete="email"
                                    autofocus placeholder="name@example.com">
                                <label for="email">Email Address</label>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4 form-floating">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="new-password" placeholder="New Password">
                                <label for="password">New Password</label>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4 form-floating">
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Confirm Password">
                                <label for="password-confirm">Confirm Password</label>
                            </div>

                            <button type="submit" class="form-control-submit-button">Reset Password</button>
                        </form>
                    </div> <!-- end of text-box -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div>
</div>

@endsection