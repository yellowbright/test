<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WaifuAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WaifuController extends Controller
{
    public function __construct(private readonly WaifuAiService $ai) {}

    public function status(): JsonResponse
    {
        return response()->json(['enabled' => $this->ai->isEnabled()]);
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        try {
            $answer = $this->ai->ask(trim($validated['question']));

            return response()->json(['answer' => $answer]);
        } catch (RuntimeException $e) {
            $code = $e->getMessage();
            $status = $code === WaifuAiService::ERROR_DISABLED ? 403 : 502;

            return response()->json(['error' => $code], $status);
        }
    }
}
