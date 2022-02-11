<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware'=>'auth:admin-api'], function () {
     Route::get('/',function (){
            echo 'you are an admin';
    });
    Route::post('/admin',[AdminController::class,'createNewAdmin']);
    Route::post('/moderator',[AdminController::class,'createNewModerator']);
    Route::post('/setAsModerator',[AdminController::class,'setAsModerator']);
    Route::post('/upgradeToAdmin',[AdminController::class,'upgradeToAdmin']);
    Route::post('/downgradeToUser',[AdminController::class,'downgradeToUser']);
    Route::post('/authenticate',[AdminController::class,'authenticate']);
    Route::post('/unAuthenticate',[AdminController::class,'unAuthenticate']);
});

Route::group(['middleware'=>'auth:moderator-api'], function () {
     Route::get('/',function (){
            echo 'you are a moderator';
    });
});

Route::group(['middleware'=>'auth:user-api'], function () {
     Route::get('/',function (){
        echo 'you are a user';
    });
});


Route::get('/test',function (){
    echo 'good this rout is for all users';
});

Route::post('/logout',[AuthController::class,'logout']);

Route::post('/login',[AuthController::class,'login']);

Route::post('/register',[AuthController::class,'register']);
