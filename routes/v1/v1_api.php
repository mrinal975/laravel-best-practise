<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Gender\GenderController;

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:api']], function () {

    Route::resource('/gender', GenderController::class);

});
