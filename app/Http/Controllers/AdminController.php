<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Moderator;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function createNewModerator(Request $request)
    {
        $fields = $request->validate([
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'user_name'=>'string|nullable',
            'country'=>'string|nullable',
            'city'=>'string|nullable',
            'street'=>'string|nullable',
            'email'=>'required|string|unique:users,email|unique:moderators,email|unique:admins,email',
            'phone_number'=>'required|string|min:10|max:12|unique:users,phone_number|unique:moderators,phone_number|unique:admins,phone_number',
            'password'=>'required|string|confirmed|min:8|max:20|',
        ]);
        $mod = Moderator::create([
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
        $response = ['message'=>'moderator created','data'=>$mod];
        return response($response,201);
    }

    public function createNewAdmin(Request $request)
    {
        $fields = $request->validate([
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'user_name'=>'string|nullable',
            'country'=>'string|nullable',
            'city'=>'string|nullable',
            'street'=>'string|nullable',
            'email'=>'required|string|unique:users,email|unique:moderators,email|unique:admins,email',
            'phone_number'=>'required|string|min:10|max:12|unique:users,phone_number|unique:moderators,phone_number|unique:admins,phone_number',
            'password'=>'required|string|confirmed|min:8|max:20|',
        ]);
        $admin = Admin::create([
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
        $response = ['message'=>'admin created','data'=>$admin];
        return response($response,201);
    }

    public function upgradeToAdmin(Request $request)
    {
        #check if is alrady admin
        $email = $request->email;
        $user = Admin::where('email',$email)->first();
        if (!$user){
            #check if is in moderators table
            $user = Moderator::where('email',$email)->first();
            if (!$user) {
                #check if is in users table
                $user = User::where('email',$email)->first();
                #can't find it in users table
                if (!$user) return response(['message'=>'can\'t find the user','data'=>null],500);
            }
        }else{
            return response(['message'=>'user is alrady admin','data'=>$user],200);
        }
        try {
            $newUser = Admin::create([
                'first_name'=> $user['first_name'],
                'last_name'=> $user['last_name'],
                'user_name'=> $user['user_name'],
                'country'=> $user['country'],
                'city'=> $user['city'],
                'street'=> $user['street'],
                'phone_number'=> $user['phone_number'],
                'email'=>$user['email'],
                'password'=>$user['password'],
            ]);
            $user->delete();
            return response(['message'=>'user upgraded successfully','data'=>$newUser],201);
        }catch (\Exception $e){
            return response(['message'=>'user can\'t be upgraded to admin','data'=>null],500);
        }
    }

    public function setAsModerator(Request $request)
    {
        $email = $request->email;
        #check if is alrady moderator
        $user = Moderator::where('email',$email)->first();
        if (!$user){
            #check if is in moderators table
            $user = Admin::where('email',$email)->first();
            if (!$user) {
                #check if is in users table
                $user = User::where('email',$email)->first();
                #can't find it in users table
                if (!$user) return response(['message'=>'can\'t find the user','data'=>null],500);
            }
        }else{
            return response(['message'=>'user is alrady moderator','data'=>$user],200);
        }

        try {
            $newUser = Moderator::create([
                'first_name'=> $user['first_name'],
                'last_name'=> $user['last_name'],
                'user_name'=> $user['user_name'],
                'country'=> $user['country'],
                'city'=> $user['city'],
                'street'=> $user['street'],
                'phone_number'=> $user['phone_number'],
                'email'=>$user['email'],
                'password'=>$user['password'],
            ]);
            $user->delete();
            return response(['message'=>'user is set as moderator successfully','data'=>$newUser],201);
        }catch (\Exception $e){
            return response(['message'=>'user can\'t be set as moderator','data'=>null],500);
        }
    }

    public function downgradeToUser(Request $request)
    {
        $email = $request->email;

        #check if is alrady admin
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
        }else{
            return response(['message'=>'user is alrady user','data'=>$user],200);
        }
        try {
            $newUser = User::create([
                'first_name'=> $user['first_name'],
                'last_name'=> $user['last_name'],
                'user_name'=> $user['user_name'],
                'country'=> $user['country'],
                'city'=> $user['city'],
                'street'=> $user['street'],
                'phone_number'=> $user['phone_number'],
                'email'=>$user['email'],
                'password'=>$user['password'],
            ]);
            $user->delete();
            return response(['message'=>'user downgraded successfully','data'=>$newUser],201);
        }catch (\Exception $e){
            return response(['message'=>'user can\'t be set as user','data'=>null],500);
        }
    }

    public function authenticate(Request $request){
        $email = $request->email;

        #check if is user exist
        $user = User::where('email',$email)->first();
        if (!$user){
            $user = Moderator::where('email',$email)->first();
            if (!$user) {
                $user = Admin::where('email',$email)->first();
                if (!$user) return response(['message'=>'can\'t find the user','data'=>null],500);
            }
        }
        try {
            $newUser = $user->update(['authenticated'=>1]);
            return response(['message'=>'user authenticated successfully','data'=>$newUser],201);
        }catch (\Exception $e){
            return response(['message'=>'user can\'t be authenticate','data'=>null],500);
        }
    }
    public function unAuthenticate(Request $request){
        $email = $request->email;

        #check if is alrady admin
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
        try {
            $newUser = $user->update(['authenticated'=>0]);
            return response(['message'=>'user unAuthenticated successfully','data'=>$newUser],201);
        }catch (\Exception $e){
            return response(['message'=>'user can\'t be authenticate','data'=>null],500);
        }
    }
}
