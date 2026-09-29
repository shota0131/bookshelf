<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\OverdueReadingPlanNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            DB::transaction(function () use ($plan) {
                $plan->update([
                    'status' => ReadingPlanStatus::EXPIRED,
                ]);

                $plan->user->notify(
                    new OverdueReadingPlanNotification($plan)
                );
            });
        }

        $this->info('期限切れの読書計画を処理しました。');

        return self::SUCCESS;
    }
}
