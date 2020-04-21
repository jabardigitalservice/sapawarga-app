<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class SeederController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        echo 'Seeding Areas...' . PHP_EOL;
        $this->actionArea();

        echo 'Seeding Users...' . PHP_EOL;
        $this->actionUser();

        $this->seedCategories();

        echo 'Seeding Phonebooks...' . PHP_EOL;
        $this->actionPhoneBook();

        echo 'Seeding Broadcasts...' . PHP_EOL;
        $this->actionBroadcast();

        echo 'Seeding Aspirasi...' . PHP_EOL;
        $this->actionAspirasi();

        echo 'Seeding Notifications...' . PHP_EOL;
        $this->actionNotification();

        echo 'Seeding Polling...' . PHP_EOL;
        $this->actionPolling();

        echo 'Seeding Survey...' . PHP_EOL;
        $this->actionSurvey();

        echo 'Seeding News Channels and News...' . PHP_EOL;
        $this->actionNews();

        echo 'Seeding Release Versions...' . PHP_EOL;
        $this->actionRelease();

        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
    }

    public function actionArea()
    {
        Yii::$app->db->createCommand('TRUNCATE areas')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/area_seed.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionUser()
    {
        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    // Seed data untuk table User dengan username yang menggunakan kode BPS
    public function actionUserBps()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/user_and_permission_bps.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionCategory()
    {
        Yii::$app->db->createCommand('TRUNCATE categories')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/category.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionPhoneBook()
    {
        Yii::$app->db->createCommand('TRUNCATE phonebooks')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/phonebook.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionBroadcast()
    {
        Yii::$app->db->createCommand('TRUNCATE broadcasts')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/broadcast.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionAspirasi()
    {
        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();

        // Jika dibutuhkan Seeder, hapus komentar di bawah
        // $sql = file_get_contents(__DIR__ . '/../migrations/seeder/aspirasi.sql');
        // Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionNotification()
    {
        Yii::$app->db->createCommand('TRUNCATE notifications')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/notification.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionPolling()
    {
        Yii::$app->db->createCommand('TRUNCATE polling_votes')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling_answers')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling')->execute();

        // $sql = file_get_contents(__DIR__ . '/../migrations/seeder/polling.sql');
        // Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionSurvey()
    {
        Yii::$app->db->createCommand('TRUNCATE survey')->execute();
    }

    public function actionNews()
    {
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/news_newschannel.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionRelease()
    {
        Yii::$app->db->createCommand('TRUNCATE releases')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/release.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    // This action is manually executed, excluded from actionIndex()
    public function actionQuestionComment()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE questions')->execute();
        Yii::$app->db->createCommand('TRUNCATE question_comments')->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/comment.sql');
        Yii::$app->db->createCommand($sql)->execute();

        $sql = file_get_contents(__DIR__ . '/../migrations/seeder/question.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    protected function seedCategories()
    {
        echo 'Seeding Categories...' . PHP_EOL;
        $this->actionCategory();
    }
}
