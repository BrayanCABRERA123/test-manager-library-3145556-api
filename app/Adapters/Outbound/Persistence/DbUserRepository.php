<?php

namespace App\Adapters\Outbound\Persistence;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\User;
use Illuminate\Support\Facades\DB;

final class DbUserRepository implements UserRepository
{
    private const TABLE = 'library.user';

    public function findByUsername(string $username): ?User
    {
        $row = DB::table(self::TABLE)->where('username', $username)->first();

        return $row === null ? null : User::create($row->id, $row->username, $row->password_hash, $row->role);
    }

    public function save(User $user): void
    {
        DB::table(self::TABLE)->insert([
            'id' => $user->id,
            'username' => $user->username,
            'password_hash' => $user->passwordHash,
            'role' => $user->role,
        ]);
    }
}
