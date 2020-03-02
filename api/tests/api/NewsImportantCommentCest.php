<?php

use app\commands\SeederController;

class NewsImportantCommentCest
{
    private $endpointComment = '/v1/news-important/1/comments';

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news_important_comments')->execute();
    }

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('news_important_comments', [
            'id' => 1,
            'news_important_id' => 1,
            'text' => 'comment 1',
            'status' => 10,
            'created_by' => 16,
            'updated_by' => 16,
            'created_at' => 1570085479,
            'updated_at' => 1570085479,
        ]);

        $I->haveInDatabase('news_important_comments', [
            'id' => 2,
            'news_important_id' => 1,
            'text' => 'comment 2',
            'status' => 10,
            'created_by' => 17,
            'updated_by' => 17,
            'created_at' => 1570085489,
            'updated_at' => 1570085489,
        ]);
    }

    public function getCommentListAll(ApiTester $I)
    {
        // RW
        $I->amStaff('staffrw');

        $I->sendGET($this->endpointComment);
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);


        // OPD
        $I->amStaff('opd.disdik');

        $I->sendGET($this->endpointComment);
        $I->canSeeResponseCodeIs(200);
    }

    public function postCreateTest(ApiTester $I)
    {
        $data = [
            'news_important_id' => 1,
            'text' => 'comment',
            'status' => 10,
        ];

        // OPD
        $I->amStaff('opd.disdik');

        $I->sendPOST($this->endpointComment, $data);
        $I->canSeeResponseCodeIs(201);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news_important_comments', [
            'news_important_id' => 1,
            'text'        => 'comment',
            'status'      => 10,
            'created_by'  => 42,
        ]);


        // RW
        $I->amStaff('staffrw');

        $I->sendPOST($this->endpointComment, $data);
        $I->canSeeResponseCodeIs(201);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news_important_comments', [
            'news_important_id' => 1,
            'text'        => 'comment',
            'status'      => 10,
            'created_by'  => 17,
        ]);
    }
}
