<?php

// app/Http/Controllers/CategoryTypeController.php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\CategoryType;
use Illuminate\Http\Request;

class CategoryTypeController extends Controller
{
    public function show(Request $request)
    {
        $codes = $request->input('codes');

        if (!$codes) {
            return response()->json(['error' => 'No codes provided'], 400);
        }

        $categoryTypes = CategoryType::whereIn('Code', explode(',', $codes))->get();

        if ($categoryTypes->isNotEmpty()) {
            // Extract CategoryType values and join them into a comma-separated string
            $categoryTypeValues = $categoryTypes->pluck('CategoryType')->implode(', ');
        
            return response()->json(['categoryTypeValues' => $categoryTypeValues]);
        } else {
            return response()->json(['error' => 'CategoryTypes not found'], 404);
        }
        // $categoryType = CategoryType::where('Code', $code)->first();

        // if ($categoryType) {
        //     return response()->json($categoryType);
        // } else {
        //     return response()->json(['error' => 'CategoryType not found'], 404);
        // }
    }
}
