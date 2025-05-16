<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;

class InstructorController extends Controller
{
    public function getInstructors(Request $request)
    {
        $instructorsQuery = Instructor::query();
        switch ($request->get('sort_by')) {
            case 'views_asc':
                $instructorsQuery->orderBy('views', 'asc');
                break;
            case 'views_desc':
                $instructorsQuery->orderBy('views', 'desc');
                break;
        }
        $instructors = $instructorsQuery->paginate(10);
        $instructors->appends($request->query());
        return response()->json([
            'current_page' => $instructors->currentPage(),
            'data' => $instructors,
            'links' => [
                'previous' => $instructors->previousPageUrl(),
                'next' => $instructors->nextPageUrl(),
            ],
            'per_page' => $instructors->perPage(),
            'total' => $instructors->total(),
        ]);
    }
    public function show($id)
    {
        $instructor = Instructor::with('categories')->findOrFail($id);
        return response()->json(['data' => $instructor], 200);
    }
}
