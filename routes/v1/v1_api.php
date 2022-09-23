<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Gender\GenderController;
use App\Http\Controllers\Role\RoleController;
use  App\Http\Controllers\RoleAssign\RoleAssignController;
use App\Http\Controllers\RolePermission\RolePermissionController;

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:api']], function () {

    Route::resource('/gender', GenderController::class);


    //page permission
    Route::resource('roles', RoleController::class);
    Route::resource('role-permission', RolePermissionController::class);
    Route::get('role-permission-page/{roleId}', [RolePermissionController::class, 'groupWisePage']);
    Route::get('/pages-with-permission', [RolePermissionController::class, 'getPages']);
    Route::get('/pages-show', [RolePermissionController::class, 'showPages']);
    Route::get('/check-page-permission', [RolePermissionController::class, 'checkPagePermission']);
    Route::resource('/role-assign', RoleAssignController::class);
    Route::get('permission/button', [RolePermissionController::class, 'getButtonPermission']);
    // end page permission
});
