<?php

namespace App\Adapters\Inbound\Http\Controllers;

use App\Application\Services\ListCategoriesService;
use Illuminate\Http\JsonResponse;

/** GET /api/v1/categories (api-contract.md §3.3). */
final class CategoriesController
{
    public function __invoke(ListCategoriesService $service): JsonResponse
    {
        return response()->json($service->list());
    }
}
