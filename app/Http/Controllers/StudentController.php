<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function attachMainCategories(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = auth()->user();

        if (!$user->student) {
            return response()->json([
                'error' => 'User does not have an associated student record.'
            ], 400);
        }

        $student = $user->student;

        $student->categories()->sync($request->category_ids);

        $subCategories = Category::whereIn('parent_id', $request->category_ids)->get();

        return response()->json([
            'message' => 'Main categories attached successfully',
            'sub_categories' => $subCategories
        ], 200);
    }

    public function attachSubCategories(Request $request)
    {
        $request->validate([
            'sub_category_ids' => 'required|array',
            'sub_category_ids.*' => 'exists:categories,id',
        ]);

        $user = auth()->user();

        if (!$user->student) {
            return response()->json([
                'error' => 'User does not have an associated student record.'
            ], 400);
        }

        $student = $user->student;

        $student->categories()->syncWithoutDetaching($request->sub_category_ids);

        return response()->json([
            'message' => 'Sub-categories attached successfully',
        ]);
    }
}
