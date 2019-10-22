<?php

namespace app\modules\v1\controllers;

use app\models\Broadcast;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class BroadcastCronController extends Controller
{
    /**
     * Scheduler for checking any scheduled messages
     * Get list records of scheduled messages, then insert new queue/job for start send broadcast
     *
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionIndex()
    {
        $query = Broadcast::find();
        $query->andWhere(['status' => Broadcast::STATUS_SCHEDULED]);
        $query->andWhere(['not', ['scheduled_datetime' => null]]);

        /**
         * Consider change $query limit result if needed
         * Keep process small and make sure not hit execution time limit
         *
         * @var Broadcast[] $scheduledBroadcasts
         */
        $scheduledBroadcasts = $query->limit(5)->all();

        $processedRecords = [];

        foreach ($scheduledBroadcasts as $scheduledBroadcast) {
            if ($scheduledBroadcast->isDue()) {
                $processedRecords[$scheduledBroadcast->id] = $this->sendBroadcast($scheduledBroadcast);
            }
        }

        $response         = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data   = json_encode(['records' => $processedRecords]);

        return $response;
    }

    /**
     * Insert new queue for broadcast message to users (async)
     *
     * @param \app\models\Broadcast $broadcast
     * @return bool
     */
    protected function sendBroadcast(Broadcast $broadcast): bool
    {
        Broadcast::pushSendMessageToUserJob($broadcast);

        $broadcast->status = Broadcast::STATUS_PUBLISHED;

        return $broadcast->save(false);
    }
}
