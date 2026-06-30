<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Store\KurdistanStore\Http\Resources\CategoryResource;
use Webkul\Category\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepository $categoryRepository) {}

    public function index(): JsonResponse
    {
        $data = Cache::tags(['kurdistan.categories'])->remember('categories.tree', 1800, function () {
            $categories = $this->categoryRepository
                ->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

            return CategoryResource::collection(collect($categories)->flatten())->toArray(request());
        });

        return response()->json(['data' => $data]);
    }
}
