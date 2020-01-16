<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Message;
use app\models\User;
use app\models\UserMessage;
use app\components\ModelHelper;

class MessageJob extends BaseObject implements JobInterface
{
    public $type;
    public $title;
    public $content;
    public $instance;
    public $senderId;
    public $pushNotifyPayload;

    public function execute($queue)
    {
        $instance = $this->instance;

        $params = [
            'kabkota_id' => $instance['kabkota_id'],
            'kec_id'     => $instance['kec_id'],
            'kel_id'     => $instance['kel_id'],
            'rw'         => $instance['rw'],
        ];

        // Get userIds
        $usersQuery = User::find()->select('id')
            ->andWhere(['status' => User::STATUS_ACTIVE])
            ->andWhere(['not', ['last_login_at' => null]]);

        $users = ModelHelper::filterByAreaTopDown($usersQuery, $params);

        // Do nothing if empty
        if ($users->count() === 0) {
            return true;
        }

        // Delete first when any update broadcast
        UserMessage::deleteAll(['message_id' => $instance['id']]);

        $this->insertUserMessages($users->all(), [
            'type'        => $this->type,
            'sender_id'   => $this->senderId,
            'instance_id' => $instance['id'],
            'title'       => $this->title,
            'description' => $this->content,
        ]);

        // if insert to users inbox has been done, then send Push Notification
        $this->sendPushNotification();
    }

    public function insertUserMessages($users, array $attributes)
    {
        foreach ($users as $index => $user) {
            $model = new UserMessage();

            /**
             * Attributes
             * type: Category type for message. Each categories have different behavior/action in mobile app
             */
            $model->setAttributes([
                'type'         => $attributes['type'],
                'message_id'   => $attributes['instance_id'],
                'sender_id'    => $attributes['sender_id'],
                'recipient_id' => $user->id,
                'title'        => $attributes['title'],
                'excerpt'      => null,
                'content'      => $attributes['description'],
                'status'       => UserMessage::STATUS_ACTIVE,
                'meta'         => null,
                'read_at'      => null,
            ]);

            if ($model->save()) {
                Yii::info(sprintf('Job executed! type = %s, id = %s, recipient_id = %s', $attributes['type'], $attributes['instance_id'], $user->id));
            } else {
                Yii::info(sprintf('Job failed! type = %s, id = %s, recipient_id = %s', $attributes['type'], $attributes['instance_id'], $user->id));
            }
        }

        Yii::info(sprintf('Total jobs = %s, finished.', $index+1));
    }

    public function sendPushNotification()
    {
        Yii::info(sprintf('Sending push notification for type = %s, id = %s', $this->type, $this->instance->id));

        $notifModel = new Message();
        $notifModel->setAttributes($this->pushNotifyPayload);
        $notifModel->send();
    }
}
