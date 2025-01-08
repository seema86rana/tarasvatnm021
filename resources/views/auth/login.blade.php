@extends('layouts.auth')

@section('title', 'Login')
@section('content')
<header class="ex-header">
    <div class="container">
        <div class="row">
            <div class="col-xl-10 offset-xl-1">
                <h1 class="text-center">Log In</h1>
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
                        <!-- <p class="mb-4">You don't have a password? Then please <a class="blue" href="sign-up.html">
                            Sign Up</a></p> -->

                        <!-- Log In Form -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
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

                            <div class="mb-4 form-floating">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="current-password" placeholder="Password">
                                <label for="password">Password</label>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- <div class="mb-4 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agree">
                                    I agree with the site's stated <a href="privacy.html">Privacy Policy</a> and <a
                                        href="#">Terms & Conditions</a>
                                </label>
                            </div> -->

                            <button type="submit" class="form-control-submit-button">Log in</button>

                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </form>
                        <!-- end of log in form -->

                    </div> <!-- end of text-box -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div>

</div>
@endsection