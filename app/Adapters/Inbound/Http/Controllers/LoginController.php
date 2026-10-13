<?php

namespace App\Adapters\Inbound\Http\Controllers;

use App\Application\Services\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** POST /api/v1/auth/login (api-contract.md §3.1). */
final class LoginController
{
    public function __invoke(Request $request, LoginService $service): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ], [
            'required' => 'Es obligatorio.',
            'string' => 'Debe ser texto.',
            'max' => 'Supera la longitud máxima.',
        ]);

        return response()->json($service->login($data['username'], $data['password']));
    }
}
