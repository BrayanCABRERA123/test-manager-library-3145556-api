<?php

namespace App\Application\Ports\Outbound;

interface CategoryRepository
{
    /** @return list<array{id: string, name: string}> ordenadas por nombre */
    public function allOrderedByName(): array;
}
