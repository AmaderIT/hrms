<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>HRMS | Login</title>
    <meta name="description" content="Login" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('/assets/css/pages/login/login-1.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{asset('/') }}assets/media/logos/bysl_favicon.ico" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/bysl_favicon.ico') }}" />
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
            <div class="d-flex flex-row-fluid flex-center">
                <div class="login-form">
                    <form class="form" id="kt_login_forgot_form" method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div>
                            <h3 class="font-weight-bold text-dark font-size-h3 font-size-h3-lg">Forgot your Password?</h3>
                            <p class="text-muted font-size-h5">Enter your email to reset your password:</p>
                        </div>
                        <div class="form-group">
                            <input class="form-control h-auto py-6 border-2 rounded-lg font-size-h6" type="email" placeholder="Email" name="email" autocomplete="off" />
                        </div>
                        <div class="form-group d-flex flex-wrap">
                            <button type="submit" id="kt_login_forgot_form_submit_button" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-4">Submit</button>
                            <a href="{{ route('login') }}" id="kt_login_forgot_cancel" class="btn btn-light-primary font-weight-bolder font-size-h6 px-8 py-4 my-3">Cancel</a>
                        </div>
                        <div class="form-group mt-5">
                            <a href="{{ route('login') }}" class="text-primary" id="kt_login_signup">Sign In</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{asset('js/custom.js')}}"></script>
    @if(session()->has("message"))
        <script type="text/javascript">
            notify().{{ session("type") ?? "success" }}("{{ session("message") }}")
        </script>
    @endif

    <script type="text/javascript">
            @if($errors->any())
        var i = 0, allError = new Array();
        @foreach($errors->all() as $error)
        allError.push("<p>{{ $error }}</p>");
        @endforeach
        notify().error(allError)
        @endif
    </script>
</body>
</html>
