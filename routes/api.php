<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Routes d'authentification accessibles sans authentification
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

// Vos autres routes protégées
// Routes protégées par auth:api
Route::middleware('auth:api')->group(function () {
    // Routes existantes pour les articles
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

    // Déconnexion de l'utilisateur
    Route::post('/logout', [AuthController::class, 'logout']);

    // Creer des utilisateurs avec le boutiquier et l'admin
    Route::post('/users', [UserController::class, 'store']);

    // Récupération des informations de l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Gestion des utilisateurs
    Route::apiResource('users', UserController::class);

Route::prefix('v1')->group(function () {
    Route::apiResource('/clients', ClientController::class)->only(['index', 'store', 'show']);

Route::apiResource('/articles', ArticleController::class);
    Route::get('/articles/trashed', [ArticleController::class, 'trashed']);
    Route::patch('/articles/{id}/restore', [ArticleController::class, 'restore']);
    Route::delete('/articles/{id}/force-delete', [ArticleController::class, 'forceDelete']);
    Route::post('/articles/stock', [ArticleController::class, 'updateMultiple']);
    // Gestion des clients

    // Préfixe v1 pour les routes versionnées
    Route::prefix('v1')->group(function () {
        // Gestion des clients (versionnée)
        Route::apiResource('/clients', ClientController::class)->only(['index', 'store', 'show']);
        // Gestion des articles avec un sous-préfixe
        Route::prefix('/articles')->group(function () {
            Route::apiResource('', ArticleController::class);
            Route::get('/trashed', [ArticleController::class, 'trashed']);
            Route::patch('/{id}/restore', [ArticleController::class, 'restore']);
            Route::delete('/{id}/force-delete', [ArticleController::class, 'forceDelete']);
            Route::post('/stock', [ArticleController::class, 'updateMultiple']);
        });

    });
});
Route::apiResource('client', ClientController::class);