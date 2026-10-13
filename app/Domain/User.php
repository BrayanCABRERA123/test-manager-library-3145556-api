<?php

namespace App\Domain;

final readonly class User
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_LIBRARIAN = 'librarian';

    private function __construct(
        public string $id,
        public string $username,
        public string $passwordHash,
        public string $role,
    ) {
    }

    public static function create(string $id, string $username, string $passwordHash, string $role): self
    {
        if (! in_array($role, [self::ROLE_ADMIN, self::ROLE_LIBRARIAN], true)) {
            throw new \InvalidArgumentException('Rol inválido.');
        }

        return new self($id, self::normalizeUsername($username), $passwordHash, $role);
    }

    public static function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }
}
