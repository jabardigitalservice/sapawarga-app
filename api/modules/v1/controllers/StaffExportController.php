<?php

namespace app\modules\v1\controllers;

use app\models\User;
use app\models\UserExport;
use app\modules\v1\repositories\UserRepository;
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
        $repository = new UserRepository();

        $params = [
            'kabkota_id'  => 1,
            'kec_id'      => null,
            'kel_id'      => null,
            'roles'       => [User::ROLE_STAFF_KEC, User::ROLE_STAFF_KEL, User::ROLE_STAFF_RW],
            'last_access' => ['2019-12-01', '2019-12-31'],
            'status'      => User::STATUS_ACTIVE,
        ];

        $query = $repository->findAllQuery($params);

        $search    = new UserExport();
        $totalRows = $search->getUserExport($query)->count();

        if ($totalRows > User::MAX_ROWS_EXPORT_ALLOWED) {
            throw new ServerErrorHttpException(
                "User export have $totalRows rows, max rows is " . User::MAX_ROWS_EXPORT_ALLOWED
            );
        }

        $filePath = $search->generateFile($query);

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
