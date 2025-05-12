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

// Debug route to check if frontend files exist
Route::get('/debug-frontend', function () {
    $indexPath = public_path('index.html');
    $exists = file_exists($indexPath);
    $size = $exists ? filesize($indexPath) : 0;
    
    $files = scandir(public_path());
    $files = array_diff($files, ['.', '..']);
    
    return response()->json([
        'index_exists' => $exists,
        'index_size' => $size,
        'public_directory_files' => $files
    ]);
});

// Serve the Vue SPA from the root
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

// Catch-all route to serve Vue SPA for any non-API routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
