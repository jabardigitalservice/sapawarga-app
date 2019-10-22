<?php

namespace app\modules\v1\controllers;

use app\models\Broadcast;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class CronController extends Controller
{
    /**
     * Scheduler for checking any scheduled messages
     * Get list records of scheduled messages, then insert new queue/job for start send broadcast
     *
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionBroadcasts()
    {
        $query = Broadcast::find();
        $query->andWhere(['status' => Broadcast::STATUS_SCHEDULED]);
        $query->andWhere(['not', ['scheduled_datetime' => null]]);

        /**
         * @var Broadcast[] $scheduledBroadcasts
         */
        $scheduledBroadcasts = $query->all();

        foreach ($scheduledBroadcasts as $scheduledBroadcast) {
            if ($scheduledBroadcast->isDue()) {
                $this->sendBroadcast($scheduledBroadcast);
            }
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
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function sendBroadcast(Broadcast $broadcast): void
    {
        Broadcast::pushSendMessageToUserJob($broadcast);

        $broadcast->status = Broadcast::STATUS_PUBLISHED;

        if ($broadcast->save(false) === false) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
    }
}
