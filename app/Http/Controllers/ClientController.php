<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use App\Traits\RestResponseTrait;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use App\Http\Requests\StoreClientRequest;

class ClientController extends Controller
{
    use RestResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $include = $request->has('include') ? [$request->input('include')] : [];

        $clients = QueryBuilder::for(Client::class)
            ->allowedFilters(['surname'])
            ->allowedIncludes(['user'])
            ->get();

        return $this->sendResponse(new ClientCollection($clients), 'SUCCESS', 'Liste des clients récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        try {
            DB::beginTransaction();

            $clientRequest = $request->only('surname', 'adresse', 'telephone');
            $client = Client::create($clientRequest);

            if ($request->has('user')) {
                $user = User::create([
                    'nom' => $request->input('user.nom'),
                    'prenom' => $request->input('user.prenom'),
                    'login' => $request->input('user.login'),
                    'password' => bcrypt($request->input('user.password')),
                    'role' => $request->input('user.role'),
                ]);

                $user->client()->save($client);
            }

            DB::commit();
            return $this->sendResponse(new ClientResource($client), 'SUCCESS', 'Client créé avec succès', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponse(null, 'ECHEC', 'Erreur lors de la création du client : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            return $this->sendResponse(new ClientResource($client), 'SUCCESS', 'Client récupéré avec succès');
        } catch (Exception $e) {
            return $this->sendResponse(null, 'ECHEC', 'Erreur lors de la récupération du client : ' . $e->getMessage(), 404);
        }
    }

    /**
     * Method to send a response in the desired format.
     */
    private function sendResponse($data, $status, $message, $httpStatus = 200)
    {
        return response()->json([
            'data'    => $data,
            'status'  => $status,
            'message' => $message,
        ], $httpStatus);
    }
}