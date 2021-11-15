<?php

use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

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
// Mauvais convention
//Route::get('repository', RepositoryController::class . '@search');

// Bonne methode
Route::get('repository', [RepositoryController::class, 'search']);
