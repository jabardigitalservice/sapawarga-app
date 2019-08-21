<?php

namespace Jdsteam\Sapawarga\Jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;

use app\models\Broadcast;
use app\models\Message;
use app\models\UserMessage;

class MessageJob extends BaseObject implements JobInterface
{
    public $type;
    public $instance;
    public $recipient_id;

    public function execute($queue)
    {
        $instance = $this->instance;
        $model = new UserMessage();
        $model->setAttributes([
            'type' => $this->type,
            'message_id' => $instance->id,
            'sender_id' => $instance->author_id,
            'recipient_id' => $this->recipient_id,
            'title' => $instance->title,
            'excerpt' => null,
            'content' => $instance->description,
            'status' => 10,
            'meta' => null,
            'read_at' => null,
        ]);

        if ($model->save()) {
            echo sprintf("Job executed! type %s id %s recipient_id %s \n", $this->type, $instance->id, $this->recipient_id);
        } else {
            echo sprintf("Job failed! type %s id %s recipient_id %s \n", $this->type, $instance->id, $this->recipient_id);
        }
    }
}
