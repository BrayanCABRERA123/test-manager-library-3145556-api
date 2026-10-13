<?php

namespace Tests\Unit\Application;

use App\Application\Services\BootstrapAdminService;
use App\Domain\User;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\InMemoryUserRepository;
use Tests\Fakes\SequentialIdGenerator;

/** D-10 y T-17: el admin inicial se crea desde el entorno, una sola vez. */
final class BootstrapAdminServiceTest extends TestCase
{
    public function test_creates_the_admin_once_with_a_hashed_password(): void
    {
        $users = new InMemoryUserRepository();
        $service = new BootstrapAdminService($users, new FakePasswordHasher(), new SequentialIdGenerator());

        $this->assertTrue($service->ensureAdmin(' Admin ', 'secret'));
        $this->assertFalse($service->ensureAdmin('admin', 'other'));

        $admin = $users->findByUsername('admin');
        $this->assertNotNull($admin);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertSame('hashed:secret', $admin->passwordHash);
        $this->assertCount(1, $users->users);
    }
}
