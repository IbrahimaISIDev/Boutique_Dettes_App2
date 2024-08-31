<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return $this->sendResponse($users, 'SUCCESS', 'Liste des utilisateurs récupérée avec succès');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,user',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'login' => $request->login,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->sendResponse($user, 'SUCCESS', 'Utilisateur créé avec succès', 201);
    }

    public function show(User $user)
    {
        return $this->sendResponse($user, 'SUCCESS', 'Utilisateur récupéré avec succès');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'login' => 'string|unique:users,login,'.$user->id.'|max:255',
            'password' => 'string|min:6',
            'role' => 'string|in:admin,user',
        ]);

        $user->update($request->all());

        return $this->sendResponse($user, 'SUCCESS', 'Utilisateur mis à jour avec succès');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->sendResponse(null, 'SUCCESS', 'Utilisateur supprimé avec succès', 204);
    }

    private function sendResponse($data, $status, $message, $httpStatus = 200)
    {
        return response()->json([
            'data'    => $data,
            'status'  => $status,
            'message' => $message,
        ], $httpStatus);
    }
}