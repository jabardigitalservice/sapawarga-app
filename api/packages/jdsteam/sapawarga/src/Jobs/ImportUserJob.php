<?php

namespace Jdsteam\Sapawarga\Jobs;

use Carbon\Carbon;
use Yii;
use app\models\Area;
use app\models\User;
use app\models\UserImport;
use Illuminate\Support\Collection;
use yii\base\BaseObject;
use yii\base\UserException;
use yii\queue\JobInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ImportUserJob extends BaseObject implements JobInterface
{
    public $filePath;
    public $uploaderEmail;

    /**
     * @var Collection
     */
    protected $importedRows;

    /**
     * @var Collection
     */
    protected $failedRows;

    /**
     * @var Carbon
     */
    protected $startedTime;

    protected $maxRows;

    public function execute($queue)
    {
        $this->notifyImportStarted();
        $this->startedTime = Carbon::now();
        $this->maxRows     = Yii::$app->params['userImportMaximumRows'];

        $filePathTemp = $this->downloadAndCreateTemporaryFile();
        if ($filePathTemp === false) {
            throw new UserException('Failed to download from object storage.');
        }

        if ($this->isExceededMaxRows($filePathTemp)) {
            return $this->notifyImportFailedMaxRows();
        }

        // Read from temporary file
        $reader = ReaderEntityFactory::createCSVReader();
        $reader->open($filePathTemp);

        $this->importedRows = new Collection();
        $this->failedRows   = new Collection();

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->processEachRow($sheet);
        }

        $reader->close();

        if ($this->failedRows->count() > 0) {
            return $this->notifyImportFailed($this->failedRows);
        }

        return $this->saveImportedRows($this->importedRows);
    }

    protected function processEachRow($sheet)
    {
        $rowNum = 0;
        foreach ($sheet->getRowIterator() as $row) {
            $rowNum++;

            // Skip header row
            if ($rowNum === 1) {
                continue;
            }

            $cells       = $row->getCells();
            $importedRow = $this->parseRows($cells);

            $this->validateRow($importedRow);
        }
    }

    protected function validateRow($row)
    {
        $model = new UserImport();
        $model->load($row, '');

        if ($model->validate() === false) {
            $this->failedRows->push([
                'username' => $model->username,
                'message'  => $model->getFirstErrors(),
            ]);
        }

        $this->importedRows->push($model);
    }

    protected function downloadAndCreateTemporaryFile()
    {
        $contents     = Yii::$app->fs->read($this->filePath);
        $filePathTemp = Yii::getAlias('@webroot/storage') . '/' . $this->filePath;

        // If success, return temporary file path
        if (file_put_contents($filePathTemp, $contents) > 0) {
            return $filePathTemp;
        }

        return false;
    }

    protected function parseRows($cells)
    {
        // if column counts is not equals as expected, something wrong with row, skip that
        if (count($cells) !== 12) {
            return null;
        }

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

    protected function notifyImportStarted()
    {
        $textBody = "Filename: {$this->filePath}";

        $this->sendEmail('Import User Started', $textBody);
    }

    protected function notifyImportFailed(Collection $rows)
    {
        $textBody  = "Validation failed:\n";

        foreach ($rows as $row) {
            $message   = implode(', ', $row['message']);
            $textBody .= sprintf("%s : %s\n", $row['username'], $message);
        }

        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Failed', $textBody);
    }

    protected function notifyImportFailedMaxRows()
    {
        $textBody  = sprintf('Total rows exceeded maximum: %s', $this->maxRows);

        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Failed', $textBody);
    }

    protected function notifyImportSuccess(Collection $rows)
    {
        $textBody  = sprintf("Total imported rows: %s\n", $rows->count());
        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Success', $textBody);
    }

    protected function sendEmail($subject, $textBody)
    {
        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        Yii::$app->mailer->compose()
            ->setFrom([$fromEmail => $fromName])
            ->setTo($this->uploaderEmail)
            ->setSubject($subject)
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

    protected function debugProcessTime()
    {
        $finishedAt = Carbon::now();

        $textBody  = "\n\n";
        $textBody .= sprintf("Started at: %s\n", $this->startedTime->toDateTimeString());
        $textBody .= sprintf("Finished at: %s\n", $finishedAt->toDateTimeString());

        return $textBody;
    }

    protected function getLinesCount($file)
    {
        $f = fopen($file, 'rb');
        $lines = 0;

        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }

        fclose($f);

        return $lines;
    }

    protected function isExceededMaxRows($filePathTemp)
    {
        $linesCount = $this->getLinesCount($filePathTemp);

        return $linesCount > $this->maxRows;
    }
}
