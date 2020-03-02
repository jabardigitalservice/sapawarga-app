<?php

class UserPostCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE user_posts')->execute();
        Yii::$app->db->createCommand('TRUNCATE user_post_comments')->execute();
        Yii::$app->db->createCommand('TRUNCATE likes')->execute();
    }

    public function getUserListActiveAndInactiveOwnTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DELETED
        $I->haveInDatabase('user_posts', [
            'id' => 2,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DISABLED Own
        $I->haveInDatabase('user_posts', [
            'id' => 3,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        // DISABLED Another User
        $I->haveInDatabase('user_posts', [
            'id' => 4,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 18,
            'updated_by' => 18
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/user-posts');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
    }

    public function getUserLikeUserPostTest(ApiTester $I)
    {
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        $I->sendPOST('/v1/user-posts/likes/1');
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
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'User Post 1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('user_posts', [
            'id' => 2,
            'text' => 'User Post 2 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706350',
            'updated_at'  => '1554706350',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('user_posts', [
            'id' => 3,
            'text' => 'User Post 3 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706355',
            'updated_at'  => '1554706355',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        // Like
        $I->sendPOST('/v1/user-posts/likes/2');

        $I->sendGET('/v1/user-posts?sort_by=likes_count&sort_order=descending');
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

    public function getUserListMeTest(ApiTester $I)
    {
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->haveInDatabase('user_posts', [
            'id' => 2,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 10,
            'created_at'  => '1554706346',
            'updated_at'  => '1554706346',
            'created_by' => 18,
            'updated_by' => 18
        ]);

        $I->haveInDatabase('user_posts', [
            'id' => 3,
            'text' => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 0,
            'created_at'  => '1554706347',
            'updated_at'  => '1554706347',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/user-posts/me');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);
    }

    public function postStaffProvUpdate(ApiTester $I)
    {
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'User Post 1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amStaff('staffprov');

        $I->sendPUT('/v1/user-posts/1', [
            'status' => 10,
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postGubenurCanComment(ApiTester $I)
    {
        $I->haveInDatabase('user_posts', [
            'id' => 1,
            'text' => 'User Post 1 Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et',
            'tags' => 'bencana, siskamling, gotongroyong, pengajian',
            'image_path' => 'general/4546546photo.jpg',
            'status' => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by' => 17,
            'updated_by' => 17
        ]);

        $I->amStaff('gubernur');

        $I->sendPOST('/v1/user-posts/1/comments', [
            'user_post_id' => 1,
            'text' => 'Komentar dari dari gubenur',
            'status' => 10
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);
    }

}
