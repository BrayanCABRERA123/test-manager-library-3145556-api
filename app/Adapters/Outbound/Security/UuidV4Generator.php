<?php

namespace App\Adapters\Outbound\Security;

use App\Application\Ports\Outbound\IdGenerator;
use Illuminate\Support\Str;

/** D-13: UUID v4, no los UUID ordenados de HasUuids. */
final class UuidV4Generator implements IdGenerator
{
    public function next(): string
    {
        return (string) Str::uuid();
    }
}
