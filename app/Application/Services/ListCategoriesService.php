<?php

namespace App\Application\Services;

use App\Application\Ports\Outbound\CategoryRepository;

/** GET /categories: las cinco categorías, orden por nombre (api-contract.md §3.3). */
final readonly class ListCategoriesService
{
    public function __construct(private CategoryRepository $categories)
    {
    }

    /** @return list<array{id: string, name: string}> */
    public function list(): array
    {
        return $this->categories->allOrderedByName();
    }
}
