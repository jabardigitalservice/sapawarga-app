<?php

class UserMessageCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE user_messages')->execute();
    }


    public function getAccessUserMessageAdminFailTest(ApiTester $I)
    {
        $I->amStaff('admin');

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function getAccessUserMessageStaffKecFailTest(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }
    public function getAccessUserMessageStaffKelFailTest(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function getAccessUserMessageUserTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserMessageListOnlyActiveTest(ApiTester $I)
    {
        $I->haveInDatabase('user_messages', [
            'id' => 1,
            'type' => 'broadcast',
            'message_id' => 1,
            'sender_id' => 1,
            'recipient_id' => 17, // as a userrw
            'title' => 'Lorem Ipsum',
            'excerpt' => 'Lorem ipsum dolor',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
            'meta' => null,
            'read_at' => null,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('user_messages', [
            'id' => 2,
            'type' => 'broadcast',
            'message_id' => 2,
            'sender_id' => 1,
            'recipient_id' => 17, // as a userrw
            'title' => 'Lorem Ipsum2',
            'excerpt' => 'Lorem ipsum dolor2',
            'content' => 'Lorem ipsum dolor sit amet2',
            'status' => -1,
            'meta' => null,
            'read_at' => null,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals('z9AY9', $data[0]['id']);
        $I->assertEquals(17, $data[0]['recipient_id']);
    }

    public function getUserMessageListOnlyForMeTest(ApiTester $I)
    {
        $I->haveInDatabase('user_messages', [
            'id' => 1,
            'type' => 'broadcast',
            'message_id' => 1,
            'sender_id' => 1,
            'recipient_id' => 17, // as a userrw
            'title' => 'Lorem Ipsum',
            'excerpt' => 'Lorem ipsum dolor',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
            'meta' => null,
            'read_at' => null,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('user_messages', [
            'id' => 2,
            'type' => 'broadcast',
            'message_id' => 2,
            'sender_id' => 1,
            'recipient_id' => 16, // as a userrw
            'title' => 'Lorem Ipsum2',
            'excerpt' => 'Lorem ipsum dolor2',
            'content' => 'Lorem ipsum dolor sit amet2',
            'status' => 10,
            'meta' => null,
            'read_at' => null,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/user-messages');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals('z9AY9', $data[0]['id']);
        $I->assertEquals(17, $data[0]['recipient_id']);
    }

    public function getUserMessageDetailUpdateReadAtTest(ApiTester $I)
    {
        $I->haveInDatabase('user_messages', [
            'id' => 1,
            'type' => 'broadcast',
            'message_id' => 1,
            'sender_id' => 1,
            'recipient_id' => 17, // as a userrw
            'title' => 'Lorem Ipsum',
            'excerpt' => 'Lorem ipsum dolor',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
            'meta' => null,
            'read_at' => null,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/user-messages/z9AY9');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('z9AY9', $data[0]['id']);
        $I->assertEquals(17, $data[0]['recipient_id']);
        $I->assertNotNull($data[0]['read_at']);
    }

    public function deleteUserMessageTest(ApiTester $I)
    {
        $I->haveInDatabase('user_messages', [
            'id' => 1,
            'type' => 'broadcast',
            'message_id' => 1,
            'sender_id' => 1,
            'recipient_id' => 17, // as a userrw
            'title' => 'Lorem Ipsum',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
        ]);

        $I->amUser('staffrw');

        $I->sendDELETE('/v1/user-messages/z9AY9');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('user_messages', ['id' => 1, 'status' => -1]);
    }

    public function deleteMultipleUserMessageTest(ApiTester $I)
    {
        $I->haveInDatabase('user_messages', [
            'id' => 1,
            'type' => 'broadcast',
            'message_id' => 1,
            'sender_id' => 1,
            'recipient_id' => 17,
            'title' => 'Lorem Ipsum',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
        ]);

        $I->haveInDatabase('user_messages', [
            'id' => 2,
            'type' => 'broadcast',
            'message_id' => 2,
            'sender_id' => 1,
            'recipient_id' => 17,
            'title' => 'Lorem Ipsum',
            'content' => 'Lorem ipsum dolor sit amet',
            'status' => 10,
        ]);

        $I->amUser('staffrw');

        $data = [
            'ids' => [
                'z9AY9',
                '7Lbmv'
            ],
        ];
        $I->sendPOST('/v1/user-messages/bulk-delete', $data);
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('user_messages', ['id' => 1, 'status' => -1]);
        $I->seeInDatabase('user_messages', ['id' => 2, 'status' => -1]);
    }
}
