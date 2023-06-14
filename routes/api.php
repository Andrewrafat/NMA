<?php
/**
 * File name: api.php
 * Last modified: 19/01/21, 5:57 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\auth\authcontroller;
use App\Http\Controllers\api\QuizeController;
use App\Http\Controllers\Api\question\QuestionCrudController;
use App\Http\Controllers\Api\question\QuestionImportController;
use App\Http\Controllers\Api\question\QuestionTypeController;
use Illuminate\Support\Facades\Request as FacadesRequest;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
    

});
Route::post('logout', [authcontroller::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
Route::get('/version', [ApiController::class, 'index'])->name('api.version');

// auth with laravel 
Route::post('register', [authcontroller::class, 'register'])->name('auth.register');
Route::post('login', [authcontroller::class, 'login'])->name('auth.login');
Route::post('logout', [authcontroller::class, 'logout'])->name('auth.logout');






Route::middleware('auth:sanctum')->group(function () {
Route::get('/quiz/{name:slug}/quiz_instructions', [QuizeController::class, 'instructions']);  // show the quize infomation and struction
Route::get('/quiz/{quiz:slug}/init_quiz', [QuizeController::class, 'initQuiz']);
Route::get('/quiz/{quiz:slug}/{session}', [QuizeController::class, 'goToQuiz']);
Route::get('/quiz/{quiz:slug}/questions/{session}', [QuizeController::class, 'getQuizQuestions']) ;
Route::post('/update_quiz_answer/{quiz:slug}/{session}', [QuizeController::class, 'updateAnswer']);
Route::post('/quiz/{quiz:slug}/finish/{session}', [QuizeController::class, 'finish']);
Route::get('/quiz/{quiz:slug}/thank-you/{session}', [QuizeController::class, 'thankYou']);
Route::get('/quiz/{quiz:slug}/results/{session}', [QuizeController::class, 'results']);



// ->name('go_to_quiz')
// Route::get('/quiz/{quiz:slug}/questions/{session}', [QuizController::class, 'getQuizQuestions'])->name('fetch_quiz_questions');
// Route::post('/update_quiz_answer/{quiz:slug}/{session}', [QuizController::class, 'updateAnswer'])->name('update_quiz_answer');
// Route::post('/quiz/{quiz:slug}/finish/{session}', [QuizController::class, 'finish'])->name('finish_quiz_session');
// Route::get('/quiz/{quiz:slug}/thank-you/{session}', [QuizController::class, 'thankYou'])->name('quiz_thank_you');
// Route::get('/quiz/{quiz:slug}/results/{session}', [QuizController::class, 'results'])->name('quiz_results');
// Route::get('/quiz/{quiz:slug}/solutions/{session}', [QuizController::class, 'solutions'])->name('quiz_solutions');
});
  /*
    |--------------------------------------------------------------------------
    | Question Routes
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix'=>'api','as'=>'api.'], function(){
        Route::resource('questions', QuestionCrudController::class);
        Route::get('questions/{id}/settings', [QuestionCrudController::class, 'settings'])->name('question_settings');
        Route::post('questions/{id}/settings', [QuestionCrudController::class, 'updateSettings'])->name('update_question_settings');
        Route::get('questions/{id}/solution', [QuestionCrudController::class, 'solution'])->name('question_solution');
        Route::post('questions/{id}/solution', [QuestionCrudController::class, 'updateSolution'])->name('update_question_solution');
        Route::get('questions/{id}/attachment', [QuestionCrudController::class, 'attachment'])->name('question_attachment');
        Route::post('questions/{id}/attachment', [QuestionCrudController::class, 'updateAttachment'])->name('update_question_attachment');

        Route::get('import-questions', [QuestionImportController::class, 'initiateImport'])->name('initiate_import_questions');
        Route::post('import-questions/{skill}', [QuestionImportController::class, 'import'])->name('import_questions');
        Route::get('question-types', [QuestionTypeController::class, 'index'])->name('question-types.index');
    });

