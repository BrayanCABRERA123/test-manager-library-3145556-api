<?php

namespace App\Application\Services;

use App\Application\Errors\ApplicationError;
use App\Application\Ports\Outbound\IdGenerator;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\User;

/** HU-02: el admin da de alta bibliotecarios. */
final readonly class CreateLibrarianService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private IdGenerator $ids,
    ) {
    }

    public function create(string $username, string $password, string $role): User
    {
        // CA-02.3: el rol admin no se otorga por la API.
        if ($role !== User::ROLE_LIBRARIAN) {
            throw ApplicationError::roleNotAllowed();
        }

        // CA-02.2: el nombre se guarda en minúsculas y recortado, y es único.
        if ($this->users->findByUsername(User::normalizeUsername($username)) !== null) {
            throw ApplicationError::usernameTaken();
        }

        $user = User::create($this->ids->next(), $username, $this->hasher->hash($password), User::ROLE_LIBRARIAN);
        $this->users->save($user);

        return $user;
    }
}
