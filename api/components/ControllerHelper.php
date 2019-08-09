<?php

namespace app\components;

use Yii;
use app\models\User;
use app\models\UserEditForm;
use Illuminate\Support\Arr;

class ControllerHelper
{
    /**
     * Return logged in user information
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public static function getCurrentUser()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $userArray = $user->toArray();

            return Arr::only($userArray, [
                'id', 'username', 'email', 'role_id', 'role_label', 'last_login_at', 'last_login_ip',
                'name', 'phone', 'address', 'rt', 'rw', 'kel_id', 'kelurahan',
                'kec_id', 'kecamatan', 'kabkota_id', 'kabkota', 'lat', 'lon',
                'facebook', 'twitter', 'instagram', 'photo_url',
            ]);
        } else {
            // Validation error
            throw new NotFoundHttpException('Object not found');
        }
    }

    /**
     * Update logged in user information
     *
     * @return array|null|\yii\db\ActiveRecord
     *
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
