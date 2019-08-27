<?php

namespace Jdsteam\Sapawarga\Behaviors;

use app\models\Broadcast;
use app\models\Message;
use Jdsteam\Sapawarga\Jobs\MessageJob;
use yii\base\Behavior;
use yii\queue\db\Queue;

class BroadcastBehavior extends Behavior
{
    public function events()
    {
        return [
            Queue::EVENT_AFTER_EXEC => 'afterExec',
        ];
    }

    public function afterExec(\yii\queue\ExecEvent $event)
    {
        $job = $event->job;
        if ($job instanceof MessageJob) {
            $this->sendPushNotification($job->instance);
        }
    }

    public function sendPushNotification($model)
    {
        $model->data = [
            'target'            => 'broadcast',
            'id'                => $model->id,
            'author'            => $model->author->name,
            'title'             => $model->title,
            'category_name'     => $model->category->name,
            'description'       => $model->description,
            'updated_at'        => $model->updated_at ?? time(),
            'push_notification' => true,
        ];
        // By default, send notification to all users
        $topic = Broadcast::TOPIC_DEFAULT;
        if ($model->kel_id && $model->rw) {
            $topic = "{$model->kel_id}_{$model->rw}";
        } elseif ($model->kel_id) {
            $topic = (string) $model->kel_id;
        } elseif ($model->kec_id) {
            $topic = (string) $model->kec_id;
        } elseif ($model->kabkota_id) {
            $topic = (string) $model->kabkota_id;
        }

        $notifModel = new Message();
        $notifModel->setAttributes([
            'title'         => $model->title,
            'description'   => $model->description,
            'data'          => $model->data,
            'topic'         => $topic,
        ]);
        $notifModel->send();
    }
}
