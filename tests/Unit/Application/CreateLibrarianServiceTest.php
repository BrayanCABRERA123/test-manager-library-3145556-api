<?php

namespace Tests\Unit\Application;

use App\Application\Errors\ApplicationError;
use App\Application\Services\CreateLibrarianService;
use App\Domain\User;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\InMemoryUserRepository;
use Tests\Fakes\SequentialIdGenerator;

/** HU-02 contra fakes. */
final class CreateLibrarianServiceTest extends TestCase
{
    private InMemoryUserRepository $users;

    private CreateLibrarianService $service;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->service = new CreateLibrarianService($this->users, new FakePasswordHasher(), new SequentialIdGenerator());
    }

    public function test_creates_a_librarian_with_normalized_username_and_hashed_password(): void
    {
        $user = $this->service->create('Ana ', 'secret', 'librarian');

        $this->assertSame('ana', $user->username);
        $this->assertSame(User::ROLE_LIBRARIAN, $user->role);
        $this->assertSame('hashed:secret', $this->users->findByUsername('ana')?->passwordHash);
    }

    public function test_requesting_admin_role_is_rejected_with_422(): void
    {
        $error = $this->captureError(fn () => $this->service->create('ana', 'secret', 'admin'));

        $this->assertSame('VALIDATION_ERROR', $error->errorCode);
        $this->assertSame(422, $error->status);
        $this->assertSame([], $this->users->users);
    }

    public function test_repeated_username_is_rejected_with_409(): void
    {
        $this->service->create('ana', 'secret', 'librarian');

        $error = $this->captureError(fn () => $this->service->create(' ANA ', 'other', 'librarian'));

        $this->assertSame('USERNAME_TAKEN', $error->errorCode);
        $this->assertSame(409, $error->status);
    }

    private function captureError(callable $action): ApplicationError
    {
        try {
            $action();
        } catch (ApplicationError $error) {
            return $error;
        }

        $this->fail('Se esperaba un ApplicationError.');
    }
}
