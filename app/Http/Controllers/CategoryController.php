<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getMainCategories()
    {
        $mainCategories = Category::whereNull('parent_id')->get();

        return response()->json([
            'data' => $mainCategories
        ], 200);
    }
}
