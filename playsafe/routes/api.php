<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;

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

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);    
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'media'
], function ($router) {
    Route::post('/uploadAndPackage', [MediaController::class, 'uploadAndPackage']);
    Route::post('/removeContent', [MediaController::class, 'removeContent']);
    Route::post('/editContentMetadata', [MediaController::class, 'editContentMetadata']);
    Route::post('/getContentList', [MediaController::class, 'getContentList']);
    Route::post('/getLicenseInfo', [MediaController::class, 'getLicenseInfo']);
    Route::post('/getContentMetadata', [MediaController::class, 'getContentMetadata']);
});