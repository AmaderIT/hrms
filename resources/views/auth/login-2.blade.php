<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>HRMS | Login</title>
    <meta name="description" content="Login" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{asset('/assets/css/pages/login/login-1.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('/assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{asset('/')}}assets/media/logos/bysl_favicon.ico" />
</head>

<body id="kt_body" class="header-mobile-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
    <div class="d-flex flex-column flex-root">
        <div class="login login-1 login-signin-on d-flex flex-column flex-lg-row flex-column-fluid bg-white" id="kt_login">
            <div class="login-aside d-flex flex-column flex-row-auto" style="background-color: #7EBFDB;">
                <div class="d-flex flex-column-auto flex-column pt-lg-40 pt-15">
                    <a href="#" class="text-center">
                        <img src="{{asset('/')}}assets/media/logos/BYSL_Logo.png" alt="logo" class="h-70px" />
                    </a>
                    <h3 class="font-weight-bolder text-center font-size-h4 font-size-h1-lg text-white">
                        <br />Human Resource Management System
                    </h3>
                </div>
                <div class="aside-img d-flex flex-row-fluid bgi-no-repeat bgi-position-y-bottom bgi-position-x-center" style="background-image: url({{asset('/')}}assets/media/svg/illustrations/payment.svg)"></div>
            </div>
            <div class="login-content flex-row-fluid d-flex flex-column justify-content-center position-relative overflow-hidden p-7 mx-auto">
                <div class="d-flex flex-column-fluid flex-center">
                    <div class="login-form login-signin">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="pb-13 pt-lg-0 pt-5">
                                <h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Welcome to HRMS</h3>
                            </div>
                            <div class="form-group">
                                <label class="font-size-h6 font-weight-bolder text-dark">ID or Phone or Email</label>
                                <input class="form-control form-control-solid h-auto p-6 rounded-lg @error('email') is-invalid @enderror"
                                       name="email" id="email" type="text" value="{{ old('email') }}" required autocomplete="email" autofocus
                                        placeholder="ID or Phone or Email"/>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <div class="d-flex justify-content-between mt-n5">
                                    <label class="font-size-h6 font-weight-bolder text-dark pt-5">Password</label>
                                </div>
                                <input class="form-control form-control-solid h-auto p-6 rounded-lg @error('password') is-invalid @enderror"
                                       id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password"/>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group ml-5">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                            <div class="pb-lg-0 pb-5">
                                <button type="submit" id="kt_login_signin_submit" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3">
                                    Sign In
                                </button>
                            </div>
                            <div class="form-group mt-5">
{{--                                <a href="{{ route('password.request') }}" class="text-primary" id="kt_login_signup">Forgot Password?</a>--}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
