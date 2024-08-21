<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * throw failed login response
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse()
    {
        throw ValidationException::withMessages([
            "email" => "Invalid UserID or Password"
        ]);
    }

    /**
     * @return string
     */
    public function username()
    {
        $login = request()->input('email');

        if(is_numeric($login) AND strlen($login) > 10)
        {
            $field = 'phone';
        }
        elseif (filter_var($login, FILTER_VALIDATE_EMAIL))
        {
            $field = 'email';
        }
        else
        {
            $field = 'fingerprint_no';
        }

        request()->merge([$field => $login]);

        return $field;
    }

    public function logout() {
        Auth::logout(); // logout user
        return redirect('/');
    }
}
