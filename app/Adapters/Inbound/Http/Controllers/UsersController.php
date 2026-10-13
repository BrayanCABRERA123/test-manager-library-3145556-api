<?php

namespace App\Adapters\Inbound\Http\Controllers;

use App\Application\Services\CreateLibrarianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** POST /api/v1/users, solo admin (api-contract.md §3.2). */
final class UsersController
{
    public function __invoke(Request $request, CreateLibrarianService $service): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string'],
        ], [
            'required' => 'Es obligatorio.',
            'string' => 'Debe ser texto.',
            'max' => 'Supera la longitud máxima.',
        ]);

        $user = $service->create($data['username'], $data['password'], $data['role']);

        // Sin password ni password_hash (CA-02.3, RNF-3).
        return response()->json(['id' => $user->id, 'username' => $user->username, 'role' => $user->role], 201);
    }
}
