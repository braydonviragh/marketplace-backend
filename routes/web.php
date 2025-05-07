<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Ultra simple health check for Railway
Route::get('/api/health', function () {
    return response('check complete', 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/', function () {
    return view('welcome');
});
