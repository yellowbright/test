<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FestivalPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FestivalPresetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', Rule::in(['default', 'love', 'western', 'traditional', 'memorial'])],
        ]);

        $query = FestivalPreset::query()
            ->where('is_active', true)
            ->orderBy('month')
            ->orderBy('day');

        if (! empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        return response()->json(['data' => $query->get()]);
    }
}
