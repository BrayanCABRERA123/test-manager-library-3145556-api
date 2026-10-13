<?php

namespace App\Application\Ports\Outbound;

interface IdGenerator
{
    /** UUID v4 como texto (api-contract.md §1). */
    public function next(): string;
}
