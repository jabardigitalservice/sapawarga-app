<?php

namespace app\components;

use Yii;
use app\models\LoginForm;
use app\models\User;
use app\models\UserEditForm;
use Illuminate\Support\Arr;

trait UserTrait
{
    /**
     * Process login
     *
     * @return array
     * @throws HttpException
     */
    public function login($roles)
    {
        $model = new LoginForm();
        $model->scenario = LoginForm::SCENARIO_LOGIN;
        $model->roles = $roles;
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = $model->getUser();
            if ($model->push_token) {
                $user->updatePushToken($model->push_token);
            }
            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
            $id = implode(',', array_values($user->getPrimaryKey(true)));

            $responseData = [
                'id' => (int)$id,
                'access_token' => $user->access_token,
            ];

            return $responseData;
        } else {
            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }
    }

    /**
     * Return logged in user information
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function getCurrentUser()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $userArray = $user->toArray();

            return Arr::only($userArray, [
                'id', 'username', 'email', 'role_id', 'role_label', 'last_login_ip',
                'name', 'phone', 'address', 'rt', 'rw', 'kel_id', 'kelurahan',
                'kec_id', 'kecamatan', 'kabkota_id', 'kabkota', 'lat', 'lon',
                'facebook', 'twitter', 'instagram', 'photo_url', 'last_login_at', 'last_access_at',
                'password_updated_at', 'profile_updated_at',
                'birth_date', 'job_type', 'job_type_id', 'education_level', 'education_level_id',
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
    public function updateCurrentUser()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $input = Yii::$app->request->post('UserEditForm');

            $model = new UserEditForm();
            $model->load($input);
            $model->id = $user->id;

            if ($model->validate() && $model->save($input)) {
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
