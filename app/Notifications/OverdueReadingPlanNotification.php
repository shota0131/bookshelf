<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueReadingPlanNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ReadingPlan $readingPlan
    ) {}

    /**
     * 通知の保存先
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースに保存する通知内容
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '読書計画の期限が過ぎました',
            'message' => '「'.$this->readingPlan->book->title.'」の目標日を過ぎています。',
            'reading_plan_id' => $this->readingPlan->id,
            'book_id' => $this->readingPlan->book_id,
        ];
    }
}