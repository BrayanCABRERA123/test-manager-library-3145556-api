<?php

namespace App\Adapters\Outbound\Persistence;

use App\Application\Ports\Outbound\CategoryRepository;
use Illuminate\Support\Facades\DB;

final class DbCategoryRepository implements CategoryRepository
{
    public function allOrderedByName(): array
    {
        return DB::table('library.category')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name])
            ->all();
    }
}
