<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function pendingCvs()
    {
        $instructors = Instructor::whereNotNull('cv_path')
            ->where('verified', false)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message'     => 'Pending CVs retrieved successfully.',
            'instructors' => $instructors,
        ]);
    }
}
