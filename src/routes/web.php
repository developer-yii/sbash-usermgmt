<?php

use Sbash\Usermgmt\Controllers\UserController;
use Sbash\Usermgmt\Controllers\SetPasswordController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web','auth']], function() {
  	// uses 'auth' middleware plus all middleware from $middlewareGroups['web']
  	// keep web middleware before auth otherwise it wont work
  	Route::get('users/index', [UserController::class,'index'])->name('users');
	Route::get('users/get', [UserController::class,'getData'])->name('users.list');
	Route::get('users/edit', [UserController::class,'getDetails'])->name('users.edit');
	Route::post('users/update', [UserController::class, 'update'])->name('users.update');
	Route::post('users/add', [UserController::class,'add'])->name('users.add');
	Route::post('users/delete', [UserController::class, 'delete'])->name('users.delete');
});

Route::group(['middleware' => ['web']], function() {  	
  	Route::get('/set-password/{user}', [SetPasswordController::class, 'create'])->name('set-password.create');
  	Route::post('/store-password', [SetPasswordController::class, 'store'])->name('set-password.store');
});