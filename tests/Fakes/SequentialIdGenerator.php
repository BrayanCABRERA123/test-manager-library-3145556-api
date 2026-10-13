<?php

namespace Tests\Fakes;

use App\Application\Ports\Outbound\IdGenerator;

final class SequentialIdGenerator implements IdGenerator
{
    private int $next = 1;

    public function next(): string
    {
        return sprintf('00000000-0000-4000-8000-%012d', $this->next++);
    }
}
