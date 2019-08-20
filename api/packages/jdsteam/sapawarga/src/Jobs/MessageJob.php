<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\Broadcast;
use app\models\Message;
use app\models\UserMessage;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class MessageJob extends BaseObject implements JobInterface
{
    public $type;
    public $instance;

    public function execute($queue)
    {
        $instance = $this->instance;
        $model = new UserMessage();
        $model->setAttributes([
            'type' => $this->type,
            'message_id' => $instance->id,
            'sender_id' => $instance->author_id,
            'recipient_id' => 1, //TODO iterate recipient ids
            'title' => null,
            'excerpt' => null,
            'content' => $instance->description,
            'status' => 10,
            'meta' => null,
            'read_at' => null,
        ]);
        if ($model->save()) {
            echo sprintf("Job executed! %s %s", $this->type, $this->instance->id);
        } else {
            echo sprintf("Job failed! %s %s", $this->type, $this->instance->id);
        }
    }
}
