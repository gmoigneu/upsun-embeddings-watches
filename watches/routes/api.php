<?php

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/watch', [App\Http\Controllers\WatchController::class, 'search']);



