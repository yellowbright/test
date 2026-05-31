<?php

namespace App\Jobs;

use App\Mail\ReminderNotificationMail;
use App\Models\Reminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReminderEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $reminderId)
    {
    }

    public function handle(): void
    {
        $reminder = Reminder::query()->with('user')->find($this->reminderId);
        if (! $reminder || $reminder->channel !== 'email' || $reminder->status !== 'active') {
            return;
        }

        Mail::to($reminder->user->email)->send(new ReminderNotificationMail($reminder));
    }

    public function failed(?Throwable $exception = null): void
    {
        // 失败记录依赖 Laravel failed_jobs；后续可扩展独立发送日志表。
    }
}
