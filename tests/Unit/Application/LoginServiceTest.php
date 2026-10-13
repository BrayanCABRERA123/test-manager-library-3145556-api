<?php

namespace Tests\Unit\Application;

use App\Application\Errors\ApplicationError;
use App\Application\Services\LoginService;
use App\Domain\User;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\FakeTokenIssuer;
use Tests\Fakes\InMemoryUserRepository;

/** HU-01 contra fakes (T-07). */
final class LoginServiceTest extends TestCase
{
    private LoginService $service;

    protected function setUp(): void
    {
        $users = new InMemoryUserRepository();
        $users->save(User::create('user-1', 'ana', 'hashed:secret', User::ROLE_LIBRARIAN));

        $this->service = new LoginService($users, new FakePasswordHasher(), new FakeTokenIssuer());
    }

    public function test_valid_credentials_return_an_eight_hour_bearer_token(): void
    {
        $result = $this->service->login(' ANA ', 'secret');

        $this->assertSame('token:user-1:librarian', $result['access_token']);
        $this->assertSame('Bearer', $result['token_type']);
        $this->assertSame(28800, $result['expires_in']);
        $this->assertSame('librarian', $result['role']);
    }

    public function test_wrong_password_and_unknown_user_fail_with_the_same_error(): void
    {
        $wrongPassword = $this->captureError(fn () => $this->service->login('ana', 'wrong'));
        $unknownUser = $this->captureError(fn () => $this->service->login('nobody', 'secret'));

        // CA-01.2: el mensaje no dice si falló el usuario o la clave.
        $this->assertSame('INVALID_CREDENTIALS', $wrongPassword->errorCode);
        $this->assertSame(401, $wrongPassword->status);
        $this->assertSame($wrongPassword->getMessage(), $unknownUser->getMessage());
        $this->assertSame($wrongPassword->errorCode, $unknownUser->errorCode);
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
