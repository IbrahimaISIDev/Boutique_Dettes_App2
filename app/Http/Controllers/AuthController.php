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

        $tokens = $this->generateTokens($user);

        return $this->sendResponse($tokens, StateEnum::SUCCESS, 'Connexion réussie');
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

        $this->revokeTokens($user);

        $tokens = $this->generateTokens($user);

        return $this->sendResponse($tokens, StateEnum::SUCCESS, 'Token rafraîchi avec succès');
    }

    public function logout(Request $request)
    {
        $this->revokeTokens($request->user());

        $request->user()->update(['refresh_token' => null]);

        return $this->sendResponse($request, StateEnum::SUCCESS, 'Déconnexion réussie');
    }

    private function generateTokens($user)
    {
        $accessToken = $user->createToken('auth_token')->accessToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ];
    }

    private function revokeTokens($user)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $tokenRepository->revokeAccessToken($user->token()->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($user->token()->id);
    }

    public function sendResponse($data, $status, $message, $httpStatus = 200)
    {
        return response()->json([
            'data'    => $data,
            'status'  => $status,
            'message' => $message,
        ], $httpStatus);
    }
}
