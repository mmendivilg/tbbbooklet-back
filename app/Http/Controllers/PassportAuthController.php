<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Validaciones\User\UserSignupValidacion;
use App\Validaciones\User\UserLoginValidacion;

class PassportAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validacion = new UserSignupValidacion( null, $request->all() );
            if( !$validacion->esValido() ) {
                return response()->json(array('errors' => $validacion->errores() ), 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = mb_strtolower($request->email);
            $user->password = bcrypt($request->password);
            $user->save();

            $user = User::find($user->id);

            return $user;
        } catch (\Exception | \Throwable | \Error $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $validacion = new UserLoginValidacion( null, $request->all() );
        if( !$validacion->esValido() ) {
            return response()->json(array('errors' => $validacion->errores() ), 400);
        }
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'user' => $request->email], 200);
        } else {
            return response()->json(
                [
                    'errors' => [
                        'password' => 'Please check the Username and Password'
                    ]
                ], 401);
        }
    }

    public function logout(Request $request){
        if (auth()->check()) {
            auth()->user()->token()->revoke();
            return response()->json(['success' =>'logout_success'],200);
        }else{
            return response()->json(['error' =>'api.something_went_wrong'], 500);
        }
    }
}
