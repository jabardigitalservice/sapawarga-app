<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\UserImport;
use Illuminate\Support\Arr;
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

        $importedCollection = new Collection();
        $rowNum = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;

                if ($rowNum === 1) {
                    continue;
                }

                $cells = $row->getCells();

                $importedRow = [
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

                $importedRow = Arr::except($importedRow, ['kabkota', 'kecamatan', 'kelurahan']);

                $model = new UserImport();
                $model->load($importedRow, '');

                if ($model->validate() === false) {
                    exit('Invalid data input.');
                }

                $importedCollection->push($model);

                echo "Row: $rowNum\n";
            }
        }

        $reader->close();

        // var_dump($importedCollection->take(5)); exit;
    }
}
