<?php
 
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
 
class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5',
        ]);
 
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
 
        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->user_type = 'User';
        $user->save();

        Log::channel('custom')->info(' data has been saved!!!!!' . $user->email);
 
        return response()->json($user, 201);
        
    }
        
 
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);
 
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user=User::where('email','=',$request->email)->get();
        $user_type=User::where('email','=',$request->email)->value('user_type');
        Log::channel('custom')->info(' data has been saved!!!!!' . $user);
        return [
            'token'=>$token,
            'user_type'=>$user_type,
            'user'=>$user
            ];
    }
 
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }
 
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
 
        return response()->json(['message' => 'Successfully logged out']);
    }
 
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
 
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function index()
    
    {
    return User::where("user_type","like","User")->get();

    }
    
}