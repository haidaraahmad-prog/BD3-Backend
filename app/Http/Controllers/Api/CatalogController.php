<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ProductCatalog;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ProductCatalog::filterOptions());
    }
}
