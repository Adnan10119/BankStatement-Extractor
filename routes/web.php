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
Route::get('login',function(){
    return response()->json(['success' => false, 'message' => 'Please login to continue!']);
})->name('login');

Route::get('overview/{id?}',function(){
    return view('welcome');
});

$pages = [
    '/','signup','home','history','overview','subscription'
];
foreach($pages as $page){
    Route::get("/".$page, function () {
        return view('welcome');
    });
}

Route::get('optimize', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('config:cache');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('optimize');
    dd("Optimized");
});

Route::get('health',function(){
    return true;
});

// Route::get('queue',function(){
//     \Artisan::call('queue:listen --timeout 0');
// });

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
