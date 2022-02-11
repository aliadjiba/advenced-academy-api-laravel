<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Moderator;
use App\Models\Admin;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','logout']]);
    }
//    public function login(Request $request)
//    {
//        $token =null;
//        $role = null;
//        $profile = null;
//        $fields = $request->validate([
//            'email'=>'required|string',
//            'password'=>'required|string',
//        ]);
//
//        $user = User::where('email',$fields['email'])->first();
//        if($user && Hash::check($fields['password'],$user->password)){
//            $token = $user->createToken('myAppToken')->plainTextToken;
//            $role = 'user';
//            $profile = $user;
//        }
//
//        $moderator = Moderator::where('email',$fields['email'])->first();
//        if($moderator && Hash::check($fields['password'],$moderator->password)){
//            $token = $moderator->createToken('myAppToken')->plainTextToken;
//            $role = 'moderator';
//            $profile = $moderator;
//        }
//
//        $admin = Admin::where('email',$fields['email'])->first();
//        if($admin && Hash::check($fields['password'],$admin->password)){
//            $token = $admin->createToken('myAppToken')->plainTextToken;
//            $role = 'admin';
//            $profile = $admin;
//        }
//
//        //return response(['message'=>'email or password is wrong'],401);
//
//
//        $response = [
//            'role'=>$role,
//            'user'=> $profile,
//            'token'=> $token
//        ];
//        if ($token==null){
//            return response(['message'=>'invalid email or password','data'=>[]],401);
//        }
//        return response(['message'=>'','data'=>$response],201);
//    }
    public function login(Request $request)
    {
        $userInfo = $request->only(['email', 'password']);
        $email = $request->email;
        $user = User::where('email',$email)->first();
        if (!$user){
            #check if is in moderators table
            $user = Moderator::where('email',$email)->first();
            if (!$user) {
                #check if is in users table
                $user = Admin::where('email',$email)->first();
                #can't find it in users table
                if (!$user) return response(['message'=>'can\'t find the user','data'=>null],500);
            }
        }
        $authenticated = $user->authenticated;
        if (!$authenticated) return response(['message'=>'you are not authenticated','data'=>null],500);
        $token = Auth::guard('user-api')->attempt($userInfo);
        if ($token) {
            $user = Auth::guard('user-api')->user();
            return response(['message' => '', 'token' => $token, 'data' => $user], 201);
        }

        $token = Auth::guard('moderator-api')->attempt($userInfo);
        if ($token) {
            $user = Auth::guard('moderator-api')->user();
            return response(['message' => '','token'=>$token,'data'=>$user], 201);
        }

        $token = Auth::guard('admin-api')->attempt($userInfo);
        if ($token) {
            $user = Auth::guard('admin-api')->user();
            return response(['message' => '','token'=>$token,'data'=>$user], 201);
        }
        return response(['error' => 'Unauthorized', 'data' => $token], 401);
    }
    public function register(Request $request)
    {
        $fields = $request->validate([
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'user_name'=>'required|string',
            'country'=>'required|string',
            'city'=>'required|string',
            'street'=>'required|string',
            'email'=>'required|string|unique:users,email|unique:moderators,email|unique:admins,email',
            'phone_number'=>'required|string|min:10|max:12|unique:users,phone_number|unique:moderators,phone_number|unique:admins,phone_number',
            'password'=>'required|string|confirmed|min:8|max:20|',
        ]);
        $user = User::create([
            'first_name'=> $fields['first_name'],
            'last_name'=> $fields['last_name'],
            'user_name'=> $fields['user_name'],
            'country'=> $fields['country'],
            'city'=> $fields['city'],
            'street'=> $fields['street'],
            'phone_number'=> $fields['phone_number'],
            'email'=>$fields['email'],
            'password'=>bcrypt($fields['password']),
        ]);
        $response = ['message'=>'user created','data'=>$user];
        return response($response,201);

    }

    public function logout(Request $request)
    {
        $token= request()->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'somthing went wrong', 'data' => $token]);
        }else{
            JWTAuth::setToken($token)->invalidate();
            return response()->json(['message' => 'Successfully logged out','data'=>'']);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
