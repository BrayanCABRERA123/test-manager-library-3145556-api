<?php

namespace App\Adapters\Inbound\Http\Middleware;

use App\Adapters\Outbound\Security\JwtTokenIssuer;
use App\Application\Errors\ApplicationError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CA-01.3: todo endpoint salvo el login y /health exige token. Sin token: 401. Rol insuficiente: 403.
 * Uso: ->middleware('auth.token') o ->middleware('auth.token:admin,librarian').
 */
final readonly class AuthenticateToken
{
    public function __construct(private JwtTokenIssuer $tokens)
    {
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $token = $request->bearerToken();
        if ($token === null || $token === '') {
            throw ApplicationError::unauthenticated();
        }

        try {
            $claims = $this->tokens->decode($token);
        } catch (\Throwable) {
            throw ApplicationError::unauthenticated();
        }

        if ($roles !== [] && ! in_array($claims['role'], $roles, true)) {
            throw ApplicationError::forbiddenRole();
        }

        $request->attributes->set('user_id', $claims['sub']);
        $request->attributes->set('role', $claims['role']);

        return $next($request);
    }
}
