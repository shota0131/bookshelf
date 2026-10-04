<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'reading-plans:send-reminders';

    protected $description = '期限が近い読書計画にリマインダー通知を送信する';

    public function handle(): int
    {
        $tomorrow = today()->addDay();

        $readingPlans = ReadingPlan::with(['user', 'book'])
            ->where('status', ReadingPlanStatus::IN_PROGRESS->value)
            ->whereDate('target_date', $tomorrow)
            ->get();

        foreach ($readingPlans as $readingPlan) {
            $alreadySent = DB::table('notifications')
                ->where('notifiable_type', get_class($readingPlan->user))
                ->where('notifiable_id', $readingPlan->user_id)
                ->where('type', ReadingPlanReminderNotification::class)
                ->where('data', 'like', '%"reading_plan_id":'.$readingPlan->id.'%')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $readingPlan->user->notify(
                new ReadingPlanReminderNotification($readingPlan)
            );
        }

        $this->info('読書計画のリマインダー通知を処理しました。');

        return self::SUCCESS;
    }
}