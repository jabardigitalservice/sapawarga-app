<?php

namespace Jdsteam\Sapawarga\Jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;

use app\models\Message;
use app\models\User;
use app\models\UserMessage;
use app\components\ModelHelper;

class MessageJob extends BaseObject implements JobInterface
{
    public $type;
    public $instance;
    public $sender_id;

    public function execute($queue)
    {
        $instance = $this->instance;

        $params = [
            'kabkota_id' => $instance->kabkota_id,
            'kec_id' => $instance->kec_id,
            'kec_id' => $instance->kec_id,
            'rw' => $instance->rw,
        ];

        // Get userIds
        $usersTarget = User::find()->select('id');
        $usersTarget = ModelHelper::filterByAreaTopDown($usersTarget, $params);

        // Do nothing if empty
        if ($usersTarget->count() == 0) {
            exit();
        }

        // Delete first when any update broadcast
        UserMessage::deleteAll(['message_id' => $instance->id]);

        // Insert to user_messages per user
        foreach ($usersTarget->all() as $key => $user) {
            $model = new UserMessage();
            $model->setAttributes([
                'type' => $this->type,
                'message_id' => $instance->id,
                'sender_id' => $this->sender_id,
                'recipient_id' => $user->id,
                'title' => $instance->title,
                'excerpt' => null,
                'content' => $instance->description,
                'status' => 10,
                'meta' => null,
                'read_at' => null,
            ]);

            if ($model->save()) {
                echo sprintf("Job executed! type = %s, id = %s, recipient_id = %s \n", $this->type, $instance->id, $user->id);

                // Dispatch another job send push FCM

            } else {
                echo sprintf("Job failed! type = %s, id = %s, recipient_id = %s \n", $this->type, $instance->id, $user->id);
            }
        }
        echo sprintf("Total jobs = %s, finished at = %s \n\n", $key+1, date("d-m-Y H:i:s"));
    }
}
