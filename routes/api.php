<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\SubscriptionController;
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

Route::post('convert/pdf/images',[APIController::class,'convert_pdf_to_images_api']);
Route::post('convert_pdf_to_images',[APIController::class,'convert_pdf_to_images']);
Route::post('test/code',[APIController::class,'usort']);

Route::post('signup',[AuthController::class,'signup']);
Route::post('login',[AuthController::class,'login']);
Route::post('login',[AuthController::class,'login']);
Route::post('is-subscribed',[AuthController::class,'isSubscribed']);
Route::post('addSubscriber',[SubscriptionController::class,'addSubscriber']);

Route::group(['middleware' => ['auth:api','force.json']], function() {
    Route::post('convert_pdf_to_csv',[APIController::class,'convert_pdf_to_csv']);
    Route::post('update/file',[APIController::class,'UpdateFile']);
    Route::post('get/history',[APIController::class,'history']);
    Route::post('delete/history/record',[APIController::class,'DeleteRecord']);
    Route::post('flag/history/record',[APIController::class,'SetFlag']);
    Route::post('delete/file',[APIController::class,'ProcessCancel']);
    Route::post('file/detail',[APIController::class,'FileDetail']);
    Route::post('filter/documents',[APIController::class,'filterDocument']);

    Route::post('editHistoryRecord',[HistoryController::class,'editHistoryRecord']);
    Route::post('editHistoryNotes',[HistoryController::class,'editHistoryNotes']);
    Route::post('shareHistoryWith',[HistoryController::class,'shareHistoryWith']);

    Route::get('getUserCaseNumbers',[CaseController::class,'getUserCaseNumbers']);
    Route::get('getUsersWithSameOrg',[HistoryController::class,'getUsersWithSameOrg']);
    Route::post('search/user/share',[HistoryController::class,'searchUserforShare']);

    Route::post('pageRequest',[SubscriptionController::class,'pageRequest']);
    Route::get('get-usage',[SubscriptionController::class,'getUsage']);
});
Route::post('get/organization',[HistoryController::class,'getOrganization']);
Route::get('exec',[APIController::class,'execPythonCommand']);
