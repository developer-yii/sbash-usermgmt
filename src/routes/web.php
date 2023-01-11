<?php

use Sbash\Usermgmt\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('index', [UserController::class,'index'])->name('users')->middleware('web');