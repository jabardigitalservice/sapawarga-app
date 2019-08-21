<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\Category;

trait HasCategory
{
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    protected function getCategoryField()
    {
        return [
            'id'   => $this->category->id,
            'name' => $this->category->name,
        ];
    }
}
