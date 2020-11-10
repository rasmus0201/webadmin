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
    Route::prefix('auth')->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('refresh', 'AuthController@refresh');

        Route::middleware('auth:api')->group(function() {
            Route::post('logout', 'AuthController@logout');
        });
    });

    Route::middleware('auth:api')->group(function() {
        Route::get('user/me', 'UserController@index');

        Route::apiResource('databases', 'DatabaseController');
        Route::apiResource('databaseUsers', 'DatabaseUserController');
        Route::apiResource('databaseUsers.privileges', 'DatabasePrivilegesController');
        Route::apiResource('websites', 'WebsiteController');
        Route::apiResource('backup/databases', 'DatabaseBackupController');
        Route::apiResource('backup/website', 'WebsiteBackupController');
    });
});
