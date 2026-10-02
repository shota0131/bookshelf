<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;

class AutoExpireReadingPlans extends Command
{
    protected $signature = 'reading-plans:auto-expire';

    protected $description = '期限切れの読書計画を失効状態に変更する';

    public function handle(): int
    {
        ReadingPlan::where('status', ReadingPlanStatus::IN_PROGRESS->value)
            ->whereDate('target_date', '<', today())
            ->update([
                'status' => ReadingPlanStatus::EXPIRED->value,
            ]);

        $this->info('期限切れの読書計画を失効状態に変更しました。');

        return self::SUCCESS;
    }
}
