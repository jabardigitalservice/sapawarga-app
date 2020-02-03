<?php

namespace app\components;

use Yii;
use app\models\GamificationParticipant;
use app\models\GamificationActivity;

class GamificationActivityHelper
{
    /**
     * Save gamification activity,
     *
     * @param $model
     * @param $attribute
     */
    public static function saveGamificationActivity($objectEvent, $objectId)
    {
        $userId = Yii::$app->user->id;
        $today = date('Y-m-d');

        // Check user participant
        $gamification = GamificationParticipant::find()
                ->select('gamifications.*, gamification_participants.*')
                ->leftJoin('gamifications', '`gamifications`.`id` = `gamification_participants`.`gamification_id`')
                ->where(['user_id' => $userId])
                ->andWhere(['gamifications.object_event' => $objectEvent])
                ->andwhere(['and', ['<=','start_date', $today],['>=','end_date', $today]])
                ->asArray()
                ->one();

        if (! empty($gamification)) {
            // Check record existing, save if not exist
            $activity = GamificationActivity::find()
                    ->where(['gamification_id' => $gamification['gamification_id']])
                    ->andwhere(['object_id' => $objectId])
                    ->andwhere(['user_id' => $userId])
                    ->exists();

            if (! $activity && $gamification['total_hit'] > $gamification['total_user_hit']) {
                // Save gamification activity
                $saveActivity = new GamificationActivity();
                $saveActivity->gamification_id = $gamification['gamification_id'];
                $saveActivity->object_id = $objectId;
                $saveActivity->user_id = $userId;
                $saveActivity->save(false);

                // Update total user hit
                $participant = GamificationParticipant::findOne($gamification['id']);
                $participant->total_user_hit = $gamification['total_user_hit'] + 1;
                $participant->save(false);
            }
        }
    }
}
