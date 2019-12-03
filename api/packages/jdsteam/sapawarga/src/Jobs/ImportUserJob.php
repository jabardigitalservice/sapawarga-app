<?php

namespace Jdsteam\Sapawarga\Jobs;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;

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

    /**
     * @var int
     */
    protected $rowNum;

    public function init()
    {
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger('import-users');

        $this->logger = $logger;
    }

    public function execute($queue)
    {
        $this->notifyImportStarted();
        $this->startedTime = Carbon::now();
        $this->maxRows     = Yii::$app->params['userImportMaximumRows'];

        $filePathTemp = $this->downloadAndCreateTemporaryFile();
        if ($filePathTemp === false) {
            throw new UserException('Failed to download from object storage.');
        }

        // Read from temporary file
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($filePathTemp);

        $this->importedRows = new Collection();
        $this->failedRows   = new Collection();

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->processSheet($sheet);
        }

        $reader->close();

//        if ($this->failedRows->count() > 0) {
//            return $this->notifyImportFailed($this->failedRows);
//        }

        return $this->saveImportedRows($this->importedRows);
    }

    protected function processSheet($sheet)
    {
        $this->rowNum = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $this->processRow($row);
        }
    }

    protected function processRow($row)
    {
        $this->rowNum++;

        // Skip header row
        if ($this->rowNum === 1) {
            return false;
        }

        if ($this->rowNum > $this->maxRows) {
            return $this->notifyImportFailedMaxRows();
        }

        $cells       = $row->getCells();
        $importedRow = $this->parseRows($cells);

        $this->validateRow($importedRow);
    }

    protected function validateRow($row)
    {
        $errors = $this->validateInCurrentFile($row);

        if (count($errors) > 0) {
            $errorRow = [
                'username' => Arr::get($row, 'username'),
                'message'  => $errors,
            ];

            $this->logger->info(sprintf('Imported Row (Error): %s (%s)', json_encode($errorRow), json_encode($row)));
            $this->failedRows->push($errorRow);

            return false;
        }

        $model = new UserImport();
        $model->load($row, '');

        if ($model->validate() === false) {
            $errorRow = [
                'username' => $model->username,
                'message'  => $model->getFirstErrors(),
            ];

            $this->logger->info(sprintf('Imported Row (Error): %s (%s)', json_encode($errorRow), json_encode($row)));
            $this->failedRows->push($errorRow);

            return false;
        }

        $this->logger->info(sprintf('Imported Row (Success): %s', json_encode($row)));
        $this->importedRows->push($model);

        return true;
    }

    protected function validateInCurrentFile($row): array
    {
        $errors = [];

        $usernameExist = $this->importedRows->where('username', '=', $row['username'])->first();

        if ($usernameExist !== null) {
            $errors[] = 'Duplicated usernames.';
        }

        $emailExist = $this->importedRows->where('email', '=', $row['email'])->first();

        if ($emailExist !== null) {
            $errors[] = 'Duplicated emails.';
        }

        return $errors;
    }

    protected function downloadAndCreateTemporaryFile()
    {
        $contents     = Yii::$app->fs->read($this->filePath);
        $filename     = basename($this->filePath);
        $filePathTemp = __DIR__ . '/../../../../../web/storage/' . $filename; // Cannot use @alias to web/storage

        // If success, return temporary file path
        if (file_put_contents($filePathTemp, $contents) > 0) {
            $this->logger->info("Temporary File Path: {$filePathTemp}");
            return $filePathTemp;
        }

        $this->logger->info('Temporary File Path: FAILED');

        return false;
    }

    protected function parseRows($cells)
    {
        // if column counts is not equals as expected, something wrong with row, skip that
        if (count($cells) < 12) {
            return null;
        }

        [$kabkota, $kecamatan, $kelurahan] = $this->mapStringToArea([
            trim($cells[9]->getValue()),
            trim($cells[10]->getValue()),
            trim($cells[11]->getValue()),
        ]);

        $roleId = trim($cells[3]->getValue());
        $role   = $this->getRoleValue(trim($cells[3]->getValue()));

        return [
            'username'   => trim($cells[0]->getValue()),
            'email'      => trim($cells[1]->getValue()),
            'password'   => trim($cells[2]->getValue()),
            'role'       => $role,
            'name'       => trim($cells[4]->getValue()),
            'phone'      => trim($cells[5]->getValue()),
            'address'    => trim($cells[6]->getValue()),
            'rt'         => in_array($roleId, ['TRAINER', 'RW']) ? trim($cells[7]->getValue()) : null,
            'rw'         => in_array($roleId, ['TRAINER', 'RW']) ? trim($cells[8]->getValue()) : null,
            'kabkota_id' => $kabkota ? $kabkota->id : null,
            'kec_id'     => $kecamatan ? $kecamatan->id : null,
            'kel_id'     => $kelurahan ? $kelurahan->id : null,
        ];
    }

    protected function notifyImportStarted()
    {
        $this->logger->info("Import User STARTED: {$this->filePath}");

        $textBody = "Filename: {$this->filePath}";

        $this->sendEmail('Import User Started', $textBody);
    }

    protected function notifyImportFailed(Collection $rows)
    {
        $this->logger->info("Import User FAILED: {$this->filePath}");

        $textBody  = "Filename: {$this->filePath}\n";

        $textBody .= "Validation failed:\n";

        foreach ($rows as $row) {
            $message   = implode(', ', $row['message']);
            $textBody .= sprintf("%s : %s\n", $row['username'], $message);
        }

        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Failed', $textBody);
    }

    protected function notifyImportFailedMaxRows()
    {
        $this->logger->info("Import User FAILED (MAX ROWS Exceeded): {$this->filePath}");

        $textBody  = "Filename: {$this->filePath}\n";

        $textBody .= sprintf('Total rows exceeded maximum: %s', $this->maxRows);

        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Failed', $textBody);
    }

    protected function notifyImportSuccess(Collection $rows)
    {
        $this->logger->info("Import User SUCCESS: {$this->filePath}");

        $textBody  = "Filename: {$this->filePath}\n";

        $textBody .= sprintf("Total imported rows: %s\n", $rows->count());

        foreach ($rows as $row) {
            $textBody .= sprintf("%s\n", $row['username']);
        }

        $textBody .= $this->debugProcessTime();

        $this->sendEmail('Import User Success', $textBody);
    }

    public function notifyError(Exception $exception)
    {
        $this->logger->info("Import User ERROR: {$this->filePath}");

        $textBody  = "Filename: {$this->filePath}\n";
        $textBody .= $exception->getMessage();

        $this->sendEmail('Import User Error', $textBody);
    }

    protected function sendEmail($subject, $textBody)
    {
        return true;
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
            $user->scenario   = User::SCENARIO_REGISTER;

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
            $kabkota = Area::findOne(['depth' => 2, 'name' => $kabkota]);
        }

        if ($kabkota !== null && $kecamatan !== null) {
            $kecamatan = Area::findOne(['parent_id' => $kabkota->id, 'name' => $kecamatan]);
        }

        if ($kecamatan !== null && $kelurahan !== null) {
            $kelurahan = Area::findOne(['parent_id' => $kecamatan->id, 'name' => $kelurahan]);
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
}
