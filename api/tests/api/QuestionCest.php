<?php

class QuestionCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE questions')->execute();
        Yii::$app->db->createCommand('TRUNCATE likes')->execute();
    }

    public function getUserListOnlyActiveTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DELETED
        $I->haveInDatabase('questions', [
            'id' => 2,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DISABLED
        $I->haveInDatabase('questions', [
            'id' => 3,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/questions');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserListFilterTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Question1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('questions', [
            'id' => 2,
            'text' => 'Question2 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DISABLED
        $I->haveInDatabase('questions', [
            'id' => 3,
            'text' => 'Question3 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/questions?search=Question1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserLikeQuestionTest(ApiTester $I)
    {
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        $I->sendPOST('/v1/questions/likes/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserListSortByTotalLikesTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Question1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('questions', [
            'id' => 2,
            'text' => 'Question2 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706350',
            'updated_at'  => '1554706350',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('questions', [
            'id' => 3,
            'text' => 'Question3 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706355',
            'updated_at'  => '1554706355',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        // Like
        $I->sendPOST('/v1/questions/likes/2');

        $I->sendGET('/v1/questions');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(2, $data[0]['id']);
    }

    public function getListSortByTotalCommentTest(ApiTester $I)
    {
        // QUESTION
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Question1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('questions', [
            'id' => 2,
            'text' => 'Question2 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706350',
            'updated_at'  => '1554706350',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amStaff();

        $data = [
            'question_id' => 2,
            'text' => 'Komentar',
            'status' => 10,
            'is_flagged' => false
        ];

        $I->sendPOST('/v1/questions/1/comments', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->sendGET('/v1/questions?sort_by=comments_count&sort_order=descending');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(2, $data[0]['id']);
    }

    public function postStaffUpdate(ApiTester $I)
    {
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Sample Question',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amStaff();

        $I->sendPUT('/v1/questions/1', [
            'is_flagged' => 1,
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postStaffProvUpdate(ApiTester $I)
    {
        $I->haveInDatabase('questions', [
            'id' => 1,
            'text' => 'Sample Question',
            'is_flagged' => 0,
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amStaff('staffprov');

        $I->sendPUT('/v1/questions/1', [
            'is_flagged' => 1,
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }
}
