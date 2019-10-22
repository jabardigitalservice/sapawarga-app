<?php

namespace app\modules\v1\controllers;

use app\models\Broadcast;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class CronController extends Controller
{
    /**
     * Scheduler for checking any scheduled messages
     * Get list records of scheduled messages, then insert new queue/job for start send broadcast
     *
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBroadcasts()
    {
        $query = Broadcast::find();
        $query->andWhere(['status' => Broadcast::STATUS_SCHEDULED]);
        $query->andWhere(['not', ['scheduled_datetime' => null]]);

        /**
         * @var Broadcast[] $broadcasts
         */
        $broadcasts = $query->all();

        foreach ($broadcasts as $broadcast) {
            $this->sendBroadcast($broadcast);
        }

        $response = new Response();
        $response->statusCode = 200;

        return $response;
    }

    /**
     * Insert new queue for broadcast message to users (async)
     *
     * @param \app\models\Broadcast $broadcast
     * @return void
     */
    protected function sendBroadcast(Broadcast $broadcast): void
    {
        Broadcast::pushSendMessageToUserJob($broadcast);

        $broadcast->status = Broadcast::STATUS_PUBLISHED;
        $broadcast->save();
    }
}
