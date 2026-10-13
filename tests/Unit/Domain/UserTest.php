<?php

namespace Tests\Unit\Domain;

use App\Domain\User;
use PHPUnit\Framework\TestCase;

/** T-06: normalización del nombre y rol válido. */
final class UserTest extends TestCase
{
    public function test_username_is_trimmed_and_lowercased(): void
    {
        $user = User::create('id-1', ' Ana ', 'hash', User::ROLE_LIBRARIAN);

        $this->assertSame('ana', $user->username);
    }

    public function test_role_outside_admin_and_librarian_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        User::create('id-1', 'ana', 'hash', 'superuser');
    }
}
