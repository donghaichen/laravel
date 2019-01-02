<?php

use Illuminate\Support\Facades\Route;
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

Route::prefix('v1')->group(function () {
    Route::get('geetest','Api\PassportController@StartCaptchaServlet');
    Route::post('login', 'Api\PassportController@login');
    Route::put('forgetPasswd','Api\PassportController@forgetPasswd');
    Route::post('register', 'Api\PassportController@register');
    Route::post('sendMail', 'Api\PassportController@sendMail');

    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('userInfo', 'Api\PassportController@userInfo');
        Route::prefix('userInfo')->group(function () {
            Route::get('qrcodeGa','Api\UserInfoController@qrcodeGa');
            Route::put('verifyGa','Api\UserInfoController@verifyGa');
            Route::get('sendMobileCode','Api\UserInfoController@sendMobileCode');
            Route::put('bindMobile','Api\UserInfoController@bindMobile');
            Route::put('resetPasswd','Api\PassportController@resetPasswd');
        });
    });

});
