<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use app\models\Area;
use app\models\User;
use app\models\UserImport;
use Illuminate\Support\Collection;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ImportUserJob extends BaseObject implements JobInterface
{
    public $file;
    public $uploaderEmail;

    public function execute($queue)
    {
        $reader = ReaderEntityFactory::createCSVReader();
        $reader->open($this->file);

        $importedRows = new Collection();
        $failedRows   = new Collection();

        $rowNum = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;

                // Skip header row
                if ($rowNum === 1) {
                    continue;
                }

                $cells = $row->getCells();

                $importedRow = $this->parseRows($cells);

                $model = new UserImport();
                $model->load($importedRow, '');

                if ($model->validate() === false) {
                    $failedRows->push([
                        'username' => $model->username,
                        'message'  => $model->getFirstErrors(),
                    ]);
                }

                $importedRows->push($model);
            }
        }

        $reader->close();

        if ($failedRows->count() > 0) {
            return $this->notifyImportFailed($failedRows);
        }

        return $this->saveImportedRows($importedRows);
    }

    protected function parseRows($cells)
    {
        [$kabkota, $kecamatan, $kelurahan] = $this->mapStringToArea([
            $cells[9]->getValue(),
            $cells[10]->getValue(),
            $cells[11]->getValue(),
        ]);

        return [
            'username'   => $cells[0]->getValue(),
            'email'      => $cells[1]->getValue(),
            'password'   => $cells[2]->getValue(),
            'role'       => $this->getRoleValue($cells[3]->getValue()),
            'name'       => $cells[4]->getValue(),
            'phone'      => $cells[5]->getValue(),
            'address'    => $cells[6]->getValue(),
            'rt'         => $cells[7]->getValue(),
            'rw'         => $cells[8]->getValue(),
            'kabkota_id' => $kabkota ? $kabkota->id : null,
            'kec_id'     => $kecamatan ? $kecamatan->id : null,
            'kel_id'     => $kelurahan ? $kelurahan->id : null,
        ];
    }

    protected function notifyImportFailed(Collection $rows)
    {
        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        $textBody  = "Validation failed:\n";

        foreach ($rows as $row) {
            $message   = implode(', ', $row['message']);
            $textBody .= sprintf("%s : %s\n", $row['username'], $message);
        }

        Yii::$app->mailer->compose()
            ->setFrom([$fromEmail => $fromName])
            ->setTo($this->uploaderEmail)
            ->setSubject('Import User Failed')
            ->setTextBody($textBody)
            ->send();
    }

    protected function notifyImportSuccess(Collection $rows)
    {
        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        $textBody  = sprintf('Total imported rows: %s', $rows->count());

        Yii::$app->mailer->compose()
            ->setFrom([$fromEmail => $fromName])
            ->setTo($this->uploaderEmail)
            ->setSubject('Import User Success')
            ->setTextBody($textBody)
            ->send();
    }

    protected function saveImportedRows(Collection $rows)
    {
        $rows->each(function ($row) {
            $user             = new User();
            $user->username   = $row->username;
            $user->email      = $row->email;
            $user->name       = $row->name;
            $user->phone      = $row->phone;
            $user->address    = $row->address;
            $user->rt         = $row->rt;
            $user->rw         = $row->rw;
            $user->kabkota_id = $row->kabkota_id;
            $user->kec_id     = $row->kec_id;
            $user->kel_id     = $row->kel_id;
            $user->role       = $row->role;
            $user->setPassword($row['password']);

            $user->save(false);
        });

        return $this->notifyImportSuccess($rows);
    }

    protected function getRoleValue($key)
    {
        // @TODO Dynamic Roles?
        $availableRoles = [
            'STAFF_PROV'    => User::ROLE_STAFF_PROV,
            'STAFF_KABKOTA' => User::ROLE_STAFF_KABKOTA,
            'STAFF_KEC'     => User::ROLE_STAFF_KEC,
            'STAFF_KEL'     => User::ROLE_STAFF_KEL,
            'TRAINER'       => User::ROLE_TRAINER,
            'RW'            => User::ROLE_STAFF_RW,
        ];

        return $availableRoles[$key];
    }

    protected function mapStringToArea($row)
    {
        [$kabkota, $kecamatan, $kelurahan] = $row;

        if ($kabkota !== null) {
            $kabkota = Area::findOne(['name' => $kabkota]);
        }

        if ($kecamatan !== null) {
            $kecamatan = Area::findOne(['name' => $kecamatan]);
        }

        if ($kelurahan !== null) {
            $kelurahan = Area::findOne(['name' => $kelurahan]);
        }

        return [$kabkota, $kecamatan, $kelurahan];
    }
}
