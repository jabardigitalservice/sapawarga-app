<?php

namespace app\components;

use Yii;
use app\models\User;
use app\models\UserEditForm;

class ControllerHelper
{
    /**
     * Checks if category_id is part of category_type
     *
     * @param $id
     * @param $params
     */
    public static function updateCurrentUser()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $model = new UserEditForm();
            $model->load(Yii::$app->request->post());
            $model->id = $user->id;

            if ($model->validate() && $model->save()) {
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);

                $responseData = 'true';

                return $responseData;
            }

            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        throw new NotFoundHttpException('Object not found');
    }
}
