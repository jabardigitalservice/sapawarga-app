<?php

use app\commands\SeederController;

class NewsImportantCommentCest
{
    private $endpointComment = '/v1/news-important/1/comments';

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('news_important', [
            'id' => 1,
            'title' => 'Info Pendidikan',
            'content' => 'Info Pendidikan',
            'category_id' => 36,
            'status' => 10,
            'created_at' =>1570085400,
            'updated_at' =>1570085400,
            'created_by' => 42,
            'updated_by' => 42
        ]);

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

    /**
     * @before loadData
     */
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

    /**
     * @before loadData
     */
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
