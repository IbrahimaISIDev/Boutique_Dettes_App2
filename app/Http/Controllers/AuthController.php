<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\StateEnum;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('login', 'password'))) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Les identifiants sont incorrects', 401);
        }

        $user = User::where('login', $request->login)->firstOrFail();

        $token = $user->createToken('auth_token')->accessToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return $this->sendResponse([
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], StateEnum::SUCCESS, 'Connexion réussie');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $user = User::where('refresh_token', $request->refresh_token)->first();

        if (!$user) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Refresh token invalide', 401);
        }

        // Révoquer tous les tokens existants
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $tokenRepository->revokeAccessToken($user->token()->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($user->token()->id);

        $token = $user->createToken('auth_token')->accessToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return $this->sendResponse([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], StateEnum::SUCCESS, 'Token rafraîchi avec succès');
    }

    public function logout(Request $request)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        // Révoquer le token d'accès
        $tokenRepository->revokeAccessToken($request->user()->token()->id);

        // Révoquer le refresh token associé
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($request->user()->token()->id);

        $request->user()->update(['refresh_token' => null]);

        return $this->sendResponse(null, StateEnum::SUCCESS, 'Déconnexion réussie');
    }

    public function sendResponse($result, $message)
    {
        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ]);
    }
}
