<?php

class StaftPermissionsCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();

        $sql = file_get_contents(__DIR__ . '/../../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();

        $sql = file_get_contents(__DIR__ . '/../../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    // VIEW PERMISSION
    public function staffProvViewCascaded(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/18'); // staffrw
        $I->canSeeResponseCodeIs(200);
    }

    public function staffKabkotaViewCascaded(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKecamatanViewCascaded(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKelurahanViewCascaded(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendGET('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendGET('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    // EDIT PERMISSION
    public function staffProvEditCascaded(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendPUT('/v1/staff/3', ['name' => 'test']); // staffkabkota
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/4', ['name' => 'test']); // staffkabkota2
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/5', ['name' => 'test']); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/6', ['name' => 'test']); // staffkec2
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/7', ['name' => 'test']); // staffkec3
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/8', ['name' => 'test']); // staffkec4
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/9', ['name' => 'test']); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/17', ['name' => 'test']); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/18', ['name' => 'test']); // staffrw
        $I->canSeeResponseCodeIs(200);
    }

    public function staffKabkotaEditCascaded(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendPUT('/v1/staff/2', ['name' => 'test']); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/3', ['name' => 'test']); // staffkabkota
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/4', ['name' => 'test']); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/5', ['name' => 'test']); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/6', ['name' => 'test']); // staffkec2
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/7', ['name' => 'test']); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/8', ['name' => 'test']); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/9', ['name' => 'test']); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/15', ['name' => 'test']); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/17', ['name' => 'test']); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/25', ['name' => 'test']); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKecamatanEditCascaded(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendPUT('/v1/staff/2', ['name' => 'test']); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/3', ['name' => 'test']); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/4', ['name' => 'test']); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/5', ['name' => 'test']); // staffkec
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/6', ['name' => 'test']); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/7', ['name' => 'test']); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/8', ['name' => 'test']); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/9', ['name' => 'test']); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/15', ['name' => 'test']); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/17', ['name' => 'test']); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/25', ['name' => 'test']); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKelurahanEditCascaded(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendPUT('/v1/staff/2', ['name' => 'test']); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/3', ['name' => 'test']); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/4', ['name' => 'test']); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/5', ['name' => 'test']); // staffkec
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/6', ['name' => 'test']); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/7', ['name' => 'test']); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/8', ['name' => 'test']); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/9', ['name' => 'test']); // staffkel
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/15', ['name' => 'test']); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendPUT('/v1/staff/17', ['name' => 'test']); // staffrw
        $I->canSeeResponseCodeIs(200);

        $I->sendPUT('/v1/staff/25', ['name' => 'test']); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    // DELETE PERMISSION
    public function staffProvDeleteCascaded(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendDELETE('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/18'); // staffrw
        $I->canSeeResponseCodeIs(204);
    }

    public function staffKabkotaDeleteCascaded(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendDELETE('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKecamatanDeleteCascaded(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendDELETE('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/9'); // staffkel
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }

    public function staffKelurahanDeleteCascaded(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendDELETE('/v1/staff/2'); // staffprov
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/3'); // staffkabkota
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/4'); // staffkabkota2
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/5'); // staffkec
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/6'); // staffkec2
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/7'); // staffkec3
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/8'); // staffkec4
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/15'); // staffkel5
        $I->canSeeResponseCodeIs(403);

        $I->sendDELETE('/v1/staff/17'); // staffrw
        $I->canSeeResponseCodeIs(204);

        $I->sendDELETE('/v1/staff/25'); // staffrw9
        $I->canSeeResponseCodeIs(403);
    }
}
