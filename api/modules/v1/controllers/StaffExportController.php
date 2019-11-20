<?php

namespace app\modules\v1\controllers;

use app\models\User;
use app\models\UserExport;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Yii;
use yii\filters\AccessControl;
use yii\web\ServerErrorHttpException;

class StaffExportController extends RestController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['export'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['export'],
                    'roles' => ['admin', 'manageStaffs'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * User Export to csv
     *
     * @return string URL
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionExport()
    {
        # Get data users
        $currentUser = User::findIdentity(\Yii::$app->user->getId());
        $role = $currentUser->role;

        $maxRoleRange = ($role == User::ROLE_ADMIN) ? ($role) : ($role - 1);

        $params = Yii::$app->request->getQueryParams();
        $params['max_roles'] = $maxRoleRange;
        $params['show_saberhoax'] = $currentUser->role === User::ROLE_ADMIN;
        $params['show_trainer'] = in_array($currentUser->role, [User::ROLE_ADMIN, User::ROLE_STAFF_PROV]);
        $params['kabkota_id'] = $params['kabkota_id'] ?? $currentUser->kabkota_id;
        $params['kec_id'] = $params['kec_id'] ?? $currentUser->kec_id;
        $params['kel_id'] = $params['kel_id'] ?? $currentUser->kel_id;
        $params['rw'] = $params['rw'] ?? $currentUser->rw;

        $search = new UserExport();

        $totalRows = $search->getUserExport($params)->count();
        if ($totalRows > User::MAX_ROWS_EXPORT_ALLOWED) {
            throw new ServerErrorHttpException("User export have $totalRows rows, max rows is " . User::MAX_ROWS_EXPORT_ALLOWED);
        }

        $filePath = $search->generateFile($params);

        return $this->copyLocalToStorage($filePath);
    }

    /**
     * Copy local file to Storage Object, then get URL from Storage
     *
     * @param $sourcePath
     * @return string
     */
    protected function copyLocalToStorage($sourcePath): string
    {
        $filename        = basename($sourcePath);
        $destinationPath = "export/user/$filename";

        $contents = file_get_contents($sourcePath);

        Yii::$app->fs->put($destinationPath, $contents);

        $fileUrl = sprintf('%s/%s', Yii::$app->params['storagePublicBaseUrl'], $destinationPath);

        return $fileUrl;
    }
}
