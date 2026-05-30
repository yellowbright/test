<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactSubmitted;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create([
            ...$request->validated(),
            'ip' => $request->ip(),
        ]);

        $this->notify($contact);

        return response()->json([
            'message' => '提交成功，我们会尽快与您联系。',
        ]);
    }

    private function notify(Contact $contact): void
    {
        $to = config('mail.contact_notify');

        if (! $to) {
            return;
        }

        // 邮件发送失败不应阻断用户提交（已入库），仅记录日志
        try {
            Mail::to($to)->send(new ContactSubmitted($contact));
        } catch (\Throwable $e) {
            Log::error('联系表单通知邮件发送失败', ['error' => $e->getMessage()]);
        }
    }
}
