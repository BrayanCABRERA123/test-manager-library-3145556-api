<?php

namespace App\Application\Ports\Outbound;

interface TokenIssuer
{
    public const TTL_SECONDS = 28800;

    public function issue(string $userId, string $role): string;
}
