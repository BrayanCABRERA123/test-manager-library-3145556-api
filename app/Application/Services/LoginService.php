<?php

namespace App\Application\Services;

use App\Application\Errors\ApplicationError;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenIssuer;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\User;

/** HU-01: iniciar sesión. */
final readonly class LoginService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private TokenIssuer $tokens,
    ) {
    }

    /** @return array{access_token: string, token_type: string, expires_in: int, role: string} */
    public function login(string $username, string $password): array
    {
        $user = $this->users->findByUsername(User::normalizeUsername($username));

        // CA-01.2: el mismo error si falla el usuario o la clave.
        if ($user === null || ! $this->hasher->verify($password, $user->passwordHash)) {
            throw ApplicationError::invalidCredentials();
        }

        return [
            'access_token' => $this->tokens->issue($user->id, $user->role),
            'token_type' => 'Bearer',
            'expires_in' => TokenIssuer::TTL_SECONDS,
            'role' => $user->role,
        ];
    }
}
