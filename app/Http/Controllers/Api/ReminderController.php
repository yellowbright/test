<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReminderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reminders = Reminder::query()
            ->with('festivalPreset')
            ->where('user_id', $request->user()->id)
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $reminders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dates' => ['required', 'array', 'min:1'],
            'dates.*.date' => ['required', 'date_format:Y-m-d'],
            'dates.*.content' => ['required', 'string', 'max:255'],
            'dates.*.remind_before_days' => ['required', 'integer', 'min:0', 'max:365'],
            'dates.*.festival_preset_id' => ['nullable', 'integer', 'exists:festival_presets,id'],
            'dates.*.channel' => ['nullable', 'in:email,sms'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            foreach ($validated['dates'] as $item) {
                Reminder::query()->updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'date' => $item['date'],
                        'content' => $item['content'],
                    ],
                    [
                        'festival_preset_id' => $item['festival_preset_id'] ?? null,
                        'remind_before_days' => $item['remind_before_days'],
                        'channel' => $item['channel'] ?? 'email',
                        'status' => 'active',
                    ]
                );
            }
        });

        return response()->json(['data' => ['message' => '保存成功']], Response::HTTP_CREATED);
    }
}
