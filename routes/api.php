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

// 路由

    | ANY                               /api/v1/passport/geetest
    | ANY                               /api/v1/passport/login
    | ANY                               /api/v1/passport/forgetPasswd
    | ANY                               /api/v1/passport/register
    | ANY                               /api/v1/passport/sendMail
    | ANY                               /api/v1/passport/site
    | GET                               /api/v1/userInfo
    | POST                              /api/v1/userInfo/bindKey
    | PUT                               /api/v1/userInfo/bindMobile
    | GET                               /api/v1/userInfo/key
    | GET                               /api/v1/userInfo/qrcodeGa
    | PUT                               /api/v1/userInfo/resetPasswd
    | GET                               /api/v1/userInfo/sendMobileCode
    | PUT                               /api/v1/userInfo/verifyGa

*/

//自定义路由
Route::prefix('v1')->group(function () {

    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('userInfo', 'Api\PassportController@userInfo');
        Route::prefix('userInfo')->group(function () {
            Route::put('resetPasswd','Api\PassportController@resetPasswd');
            //userinfo接口
            Route::get('qrcodeGa','Api\UserInfoController@qrcodeGa');
            Route::put('verifyGa','Api\UserInfoController@verifyGa');
            Route::get('sendMobileCode','Api\UserInfoController@sendMobileCode');
            Route::put('bindMobile','Api\UserInfoController@bindMobile');
            Route::get('key','Api\UserInfoController@key');
            Route::post('bindKey','Api\UserInfoController@bindKey');
        });
    });

});

//通配路由
Route::pattern('version_id', '[0-9]+');
Route::group(['prefix' => 'v{version_id}'], function ($router){
    $router->any('/{controller?}/{action?}/{id?}', function() use ($router){
//        $version ='V'. Route::input('version_id');
        $controller = Route::input('controller');
        $action = Route::input('action');
        $id = Route::input('id');

        \Illuminate\Support\Facades\App::booting()

        if(Route::has($controller)){
            return redirect($controller);
        }else{
            $realcontroller = "App\\Http\\Controllers\\Api\\" .  ucwords($controller) . "Controller";
            if (!class_exists($realcontroller)){
                return error(404);
            }else{
                $ctrl = \App::make($realcontroller);
                if(method_exists($ctrl, $action)){
                    return \App::call([$ctrl, $action], ['id' => $id]);
                }elseif(method_exists($ctrl, $id)){
                    return \App::call([$ctrl, $id], ['action' => $action]);
                }else{
                    $data = $_REQUEST;
                    $data['id'] = $id;
                    $data['action'] = $action;
                    return \App::call([$ctrl, 'undefined'], ['data' => $data]);
                }
            }
        }
    });
});
