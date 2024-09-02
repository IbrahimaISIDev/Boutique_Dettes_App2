<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Routes d'authentification accessibles sans authentification
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Routes protégées par auth:api et blacklisted
    Route::middleware(['auth:api', 'blacklisted'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Routes protégées pour les utilisateurs
        Route::apiResource('/users', UserController::class);
        
        // Routes pour les clients
        Route::post('/clients/telephone', [ClientController::class, 'getByPhoneNumber']);
        Route::apiResource('/clients', ClientController::class)->only(['index', 'store', 'show']);
        Route::post('/clients/register', [ClientController::class, 'addAccount']);
        
        // Routes pour les articles
        Route::apiResource('/articles', ArticleController::class);
        Route::prefix('/articles')->group(function () {
            Route::post('/trashed', [ArticleController::class, 'trashed']);
            Route::patch('/{id}/restore', [ArticleController::class, 'restore']);
            Route::post('/libelle', [ArticleController::class, 'getByLibelle']);
            Route::delete('/{id}/force-delete', [ArticleController::class, 'forceDelete']);
            Route::post('/stock', [ArticleController::class, 'updateMultiple']);
        });
    });
});
