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

    public function getSubCategories($id)
    {
        $subCategories = Category::where('parent_id', $id)->get();
        return response()->json([
            'data' => $subCategories
        ], 200);
    }
    public function getCategories()
    {
        $categories = Category::all();
        return response()->json([
            'data' => $categories
        ], 200);
    }
}
