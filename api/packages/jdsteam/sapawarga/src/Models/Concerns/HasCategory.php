<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\components\ModelHelper;
use app\models\Category;

trait HasCategory
{
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    protected function rulesCategory()
    {
        return [
            ['category_id', 'validateCategoryID'],
        ];
    }

    protected function getCategoryField()
    {
        return [
            'id'   => $this->category->id,
            'name' => $this->category->name,
        ];
    }

    /**
     * Checks if category type is aspirasi
     *
     * @param $attribute
     * @param $params
     */
    public function validateCategoryID($attribute, $params)
    {
        ModelHelper::validateCategoryID($this, $attribute);
    }
}
