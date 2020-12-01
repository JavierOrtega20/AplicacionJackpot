<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\LoginAttempt;
use App\Session as SessionModel;
use \Carbon\Carbon;
use Session;

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

    use AuthenticatesUsers {
        attemptLogin as traitAttemptLogin;
        sendLoginResponse as traitSendLoginResponse;
        logout as traitLogout;
	}

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function attemptLogin(Request $request)
    {
        $result = $this->traitAttemptLogin($request);
        if ($result) {
           $userHaveValidSession = SessionModel::where([
            ['user_id', '=',$this->guard()->user()->id ],
            ['last_activity', '>', Carbon::now()->subMinutes(3)->timestamp]
            ])->first();
        }
        
        if ($result && $userHaveValidSession ){
            $this->saveAttemp($request, $this->guard()->user(), false);
            $this->guard()->logout();
            $message = "Actualmente existe una sesión para este usuario, intente mas tarde.";
            session()->put('sessionExists', $message);
            return false;
        }
        SessionModel::where([
            ['user_id', '=',$this->guard()->user()->id],
            ['id', '!=', session()->getId()]
        ])->delete();
        return $result;
    }

    protected function sendLoginResponse(Request $request)
    {
        $user = $this->guard()->user();
        $user->session_id = session()->getId();
        $user->save();
        $this->saveAttemp($request, $user, true);
        return $this->traitSendLoginResponse($request);
    }

    public function logout(Request $request)
    {
        $user = $this->guard()->user();
        if ($user) {
           $user->session_id = null;
           $user->save();
        }
        return $this->traitLogout($request);
    }
    protected function saveAttemp($request,$user, $succesfull){
        return LoginAttempt::create([
            'ip' => request()->ip(),
            'user_id' => $user->id,
            'succesfull' => $succesfull

        ]);
    }
}
