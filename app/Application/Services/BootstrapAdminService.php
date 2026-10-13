<?php

namespace App\Application\Services;

use App\Application\Ports\Outbound\IdGenerator;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\User;

/** D-10: el admin inicial lo crea el arranque desde el entorno; nadie lo otorga en ejecución. */
final readonly class BootstrapAdminService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private IdGenerator $ids,
    ) {
    }

    /** @return bool true si lo creó, false si ya existía. */
    public function ensureAdmin(string $username, string $password): bool
    {
        if ($this->users->findByUsername(User::normalizeUsername($username)) !== null) {
            return false;
        }

        $this->users->save(User::create(
            $this->ids->next(),
            $username,
            $this->hasher->hash($password),
            User::ROLE_ADMIN,
        ));

        return true;
    }
}
