<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiAuthController;
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

 
Route::post('register', [ApiAuthController::class, 'register']);
Route::post('login', [ApiAuthController::class, 'login']);
  
Route::middleware('auth:api')->group(function () {
    Route::get('getuserprofile', [ApiAuthController::class, 'getuserprofile']);
    Route::post('storeuserprofile', [ApiAuthController::class, 'storeuserprofile']);
    Route::post('createpost', [ApiAuthController::class, 'createpost']);
    Route::post('updatepost', [ApiAuthController::class, 'updatepost']);
    Route::get('viewpost', [ApiAuthController::class, 'viewpost']);
    Route::post('deletepost', [ApiAuthController::class, 'deletepost']);
    Route::post('addcommenttopost', [ApiAuthController::class, 'addcommenttopost']);
    Route::post('logout', [ApiAuthController::class, 'logout']);
});
 
