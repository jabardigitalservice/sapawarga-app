<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use app\models\UserImport;
use Illuminate\Support\Collection;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ImportUserJob extends BaseObject implements JobInterface
{
    public $file;

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
        return [
            'username'  => $cells[0]->getValue(),
            'email'     => $cells[1]->getValue(),
            'password'  => $cells[2]->getValue(),
            'role'      => $cells[3]->getValue(),
            'name'      => $cells[4]->getValue(),
            'phone'     => $cells[5]->getValue(),
            'address'   => $cells[6]->getValue(),
            'rt'        => $cells[7]->getValue(),
            'rw'        => $cells[8]->getValue(),
            'kabkota'   => $cells[9]->getValue(),
            'kecamatan' => $cells[10]->getValue(),
            'kelurahan' => $cells[11]->getValue(),
        ];
    }

    protected function notifyImportFailed(Collection $rows)
    {
        exit('Failed !');
    }

    protected function notifyImportSuccess(Collection $rows)
    {
        exit('Success !');
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
            $user->kabkota_id = null;
            $user->kec_id     = null;
            $user->kel_id     = null;
            $user->role       = $this->getRoleValue($row->role);
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
}
