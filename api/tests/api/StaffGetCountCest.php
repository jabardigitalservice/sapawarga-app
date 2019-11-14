<?php

class StaffGetCountCest
{
    private $endpoint = '/v1/staff/count';

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();

        $sql = file_get_contents(__DIR__ . '/../../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function staffGetCount(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpoint);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'items' => [
                    ['level' => 'staffKabkota', 'value' => 2],
                    ['level' => 'staffKec', 'value' => 4],
                    ['level' => 'staffKel', 'value' => 8],
                    ['level' => 'staffRW', 'value' => 16],
                    ['level' => 'trainer', 'value' => 1],
                ],
            ],
        ]);
    }
}
