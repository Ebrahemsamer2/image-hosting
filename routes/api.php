<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\AlbumController;
use App\Http\Controllers\V1\ImageManipulationController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function(){
    Route::apiResource('albums', AlbumController::class);
    Route::get('images/{image}', [ImageManipulationController::class, 'show']);
    Route::post('images/resize', [ImageManipulationController::class, 'resize']);
    Route::get('images/by-album/{album}', [ImageManipulationController::class, 'byAlbum']);
    Route::delete('images/{image}', [ImageManipulationController::class, 'destroy']);

});
