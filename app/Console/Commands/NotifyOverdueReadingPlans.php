<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueReadingPlans extends Command
{
    protected $signature = 'reading-plans:notify-overdue';

    protected $description = '期限切れの読書計画を更新して通知する';

    public function handle(): int
    {
        $plans = ReadingPlan::with('user', 'book')
            ->where('status', ReadingPlanStatus::IN_PROGRESS->value)
            ->whereDate('target_date', '<', today())
            ->get();

        foreach ($plans as $plan) {
            $plan->update([
                'status' => ReadingPlanStatus::EXPIRED,
            ]);

            // ここで既存の通知クラスを使って通知を送信する
        }

        $this->info('期限切れの読書計画を処理しました。');

        return self::SUCCESS;
    }
}
