<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('/assets/css/pages/login/bootstrap.min.css') }}" rel="stylesheet"
          integrity="" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@100;200;300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/css/pages/login/all.css') }}" integrity="" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('/assets/css/pages/login/login.css') }}">
    <title>HRMS | Login</title>
    <style>
        .forgot-password {
            text-align: left;
            margin-top: 210px;
            margin-left: 50px;
        }
        .forgot-password a {
            font-family: "Arial Narrow", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            text-decoration: underline;
            color: #404040;
            letter-spacing: 2px;
            font-weight: 800;
            font-size: 14px;
            opacity: 0.7;
        }
        .forgot-password a:hover {
            opacity: 1;
        }
        @media (max-width: 576px) {
            .forgot-password {
                text-align: center;
                margin-top: 2rem;
                margin-left: -10px;
            }
        }
        .error-msg-show{
            width: 35%;
            margin: 0 auto;
            font-weight: bold;
            font-size: 18px;
        }
        .all-errors-show{
            margin-top: 0.25rem;
            font-size: .875em;
            color: #dc3545;
            display: block;
        }
    </style>
</head>

<body>
<div class="loginSection">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 defultM">
                <div class="leftsideLogin">
                    <h5>EXPLORE. INNOVATE. INVEST.</h5>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 imgFixed">
                <div class="rightSideLogin">
                    <div class="loginInformation">
                        <div class="logoSction">
                            <img src="{{ asset('assets/media/login/bysl.png') }}" alt="BYSL">
                            <div class="live-focus">
                                <ul>
                                    <li>
                                        <p>LIVE </p>
                                    </li>
                                    <li>
                                        <div class="video__icon">
                                            <div class="circle--outer"></div>
                                            <div class="circle--inner"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>


                        @error('email')
                        <div class="error-msg-show">
                        <div class="alert alert-danger" role="alert">
                            {!! implode('', $errors->all('<div>:message</div>')) !!}
                            </div>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="fromMargin">
                            @csrf
                            <div class="mb-3">
                                <!-- <label for="exampleInputEmail1" class="form-label">Email address</label> -->
                                <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                       placeholder="ID or Phone or Email" aria-describedby="emailHelp" value="{{ old('email') }}"  autocomplete="email" required autofocus>

                                @error('fingerprint_no')
                                <span class="all-errors-show" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <!-- <label for="exampleInputPassword1" class="form-label">Password</label> -->
                                <input type="password" placeholder="Password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" autocomplete="current-password" required/>

                                @error('password')
                                <span class="all-errors-show" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label login-remember" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block" onclick="this.form.submit(); this.disabled=true;">SIGN IN</button>
                        </form>
                        <div class="forgot-password">
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                    </div>
                    <!-- <div class="bg-area">
                        <img src="image/login-background.png" alt="">
                    </div> -->
                </div>
                <div class="backgroundFix"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/pages/login/bootstrap.bundle.min.js') }}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!-- Option 2: Separate Popper and Bootstrap JS -->
<!--
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
-->
</body>
</html>
