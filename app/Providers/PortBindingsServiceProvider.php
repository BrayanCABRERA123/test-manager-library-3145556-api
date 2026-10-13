<?php

namespace App\Providers;

use App\Adapters\Outbound\Persistence\DbUserRepository;
use App\Adapters\Outbound\Security\Argon2PasswordHasher;
use App\Adapters\Outbound\Security\JwtTokenIssuer;
use App\Adapters\Outbound\Security\UuidV4Generator;
use App\Application\Ports\Outbound\IdGenerator;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenIssuer;
use App\Application\Ports\Outbound\UserRepository;
use Illuminate\Support\ServiceProvider;

/** Artículo III: único punto donde un puerto se encuentra con su adaptador. */
final class PortBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class, DbUserRepository::class);
        $this->app->singleton(PasswordHasher::class, Argon2PasswordHasher::class);
        $this->app->singleton(IdGenerator::class, UuidV4Generator::class);
        $this->app->singleton(JwtTokenIssuer::class, fn () => new JwtTokenIssuer((string) env('JWT_SECRET')));
        $this->app->singleton(TokenIssuer::class, JwtTokenIssuer::class);
    }
}
