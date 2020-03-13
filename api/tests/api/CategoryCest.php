<?php

use app\models\Category;

class CategoryCest
{
    private $endpointCategory = '/v1/categories';

    // /v1/categories
    public function createNewCategoryNameExist(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointCategory, [
            'type'      => 'phonebook',
            'name'      => 'Kesehatan',
            'status'    => 10,
        ]);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function createNewCategoryForbiddenType(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointCategory, [
            'type'      => 'notification',
            'name'      => 'New Category',
            'status'    => 10,
        ]);

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function createNewCategory(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointCategory, [
            'type'      => 'phonebook',
            'name'      => 'New Phonebook Category',
            'status'    => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);
    }

    public function getCategoryListAll(ApiTester $I)
    {
        $I->haveInDatabase('categories', [
            'type' =>'phonebook',
            'name' =>'Deleted Category',
            'status' => -1,
        ]);

        $I->haveInDatabase('categories', [
            'type' =>'phonebook',
            'name' =>'Disabled Category',
            'status' => 0,
        ]);

        // admin
        $I->amStaff();

        $I->sendGET($this->endpointCategory);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'type' => 'phonebook',
        ]);

        $I->seeResponseContainsJson([
            'type' => 'broadcast',
        ]);

        $I->seeResponseContainsJson([
            'name' => 'Disabled Category',
        ]);

        $I->cantSeeResponseContainsJson([
            'type' => 'newsHoax',
        ]);

        $I->cantSeeResponseContainsJson([
            'type' => 'notification',
        ]);

        $I->cantSeeResponseContainsJson([
            'name' => 'Deleted Category',
        ]);

        // pimpinan
        $I->amStaff('gubernur');

        $I->sendGET($this->endpointCategory);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function getCategoryListFilterType(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointCategory}?type=phonebook");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'type' => 'phonebook',
        ]);

        $I->cantSeeResponseContainsJson([
            'type' => 'broadcast',
        ]);

        // Assert 'Lainnya' category is at the latest index of search result
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $lastIndex = count($data[0]) - 1;

        $I->assertEquals(Category::DEFAULT_CATEGORY_NAME, $data[0][$lastIndex]['name']);
    }

    public function getCategoryListFilterName(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointCategory}?name=kesehatan");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'name' => 'Kesehatan',
        ]);

        $I->cantSeeResponseContainsJson([
            'name' => 'Keamanan',
        ]);
    }

    public function getCategoryItemNotFound(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointCategory}/999");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 404,
        ]);
    }

    public function getCategoryItem(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointCategory}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success'   => true,
            'status'    => 200,
            'data'      => [
                'id' => 1,
                'type' => 'phonebook',
                'name' => 'Kesehatan',
                'status' => 10,
            ]
        ]);
    }

    public function updateCategoryForbiddenType(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT("{$this->endpointCategory}/1", [
            'name' => 'Layanan Kesehatan Edited',
            'type' => 'notification',
        ]);

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);

        $I->amStaff();

        $I->sendPUT("{$this->endpointCategory}/14", [
            'name' => 'Edited Category',
        ]);

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function updateCategory(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT("{$this->endpointCategory}/1", [
            'name' => 'Layanan Kesehatan Edited',
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function deleteCategoryForbiddenType(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointCategory}/14");
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteCategory(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointCategory}/1");
        $I->canSeeResponseCodeIs(204);
    }

    // /v1/categories/types
    public function getCategoryTypeList(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointCategory}/types");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'id' => 'phonebook',
        ]);

        $I->seeResponseContainsJson([
            'id' => 'broadcast',
        ]);

        $I->cantSeeResponseContainsJson([
            'id' => 'newsHoax',
        ]);

        $I->cantSeeResponseContainsJson([
            'id' => 'notification',
        ]);
    }
}
