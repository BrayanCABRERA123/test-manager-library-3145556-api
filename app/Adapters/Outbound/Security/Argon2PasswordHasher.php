<?php

namespace App\Adapters\Outbound\Security;

use App\Application\Ports\Outbound\PasswordHasher;

/** D-09: Argon2id. */
final class Argon2PasswordHasher implements PasswordHasher
{
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_ARGON2ID);
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
