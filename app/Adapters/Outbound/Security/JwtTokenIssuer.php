<?php

namespace App\Adapters\Outbound\Security;

use App\Application\Ports\Outbound\TokenIssuer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/** D-11 y ADR-005: JWT HS256 de 8 horas con sub y role, sin datos personales. */
final readonly class JwtTokenIssuer implements TokenIssuer
{
    private const ALGORITHM = 'HS256';

    public function __construct(private string $secret)
    {
    }

    public function issue(string $userId, string $role): string
    {
        $now = time();

        return JWT::encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => $now,
            'exp' => $now + self::TTL_SECONDS,
        ], $this->secret, self::ALGORITHM);
    }

    /** @return array{sub: string, role: string} */
    public function decode(string $token): array
    {
        $claims = JWT::decode($token, new Key($this->secret, self::ALGORITHM));

        return ['sub' => $claims->sub, 'role' => $claims->role];
    }
}
