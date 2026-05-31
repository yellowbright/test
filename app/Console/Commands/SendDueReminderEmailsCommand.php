<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderEmailJob;
use App\Models\Reminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDueReminderEmailsCommand extends Command
{
    protected $signature = 'reminders:send-due';

    protected $description = 'Dispatch reminder email jobs due today.';

    public function handle(): int
    {
        $today = now()->toDateString();

        // 提醒触发日 = date - remind_before_days；不同数据库的日期函数语法不同。
        $dueExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "date(\"date\", '-' || remind_before_days || ' days')"
            : 'DATE_SUB(`date`, INTERVAL remind_before_days DAY)';

        Reminder::query()
            ->where('status', 'active')
            ->where('channel', 'email')
            ->whereRaw("{$dueExpression} = ?", [$today])
            ->chunkById(200, function ($reminders): void {
                foreach ($reminders as $reminder) {
                    SendReminderEmailJob::dispatch($reminder->id);
                }
            });

        $this->info('Due reminder jobs dispatched.');

        return self::SUCCESS;
    }
}
