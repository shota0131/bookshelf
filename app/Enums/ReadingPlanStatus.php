<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => '読書中',
            self::COMPLETED => '完了',
            self::EXPIRED => '期限切れ',
        };
    }
}

