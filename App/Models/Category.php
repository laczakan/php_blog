<?php

namespace App\Models;

/**
 * Model Category to manage category table
 *
 * @category Model
 * @package  App
 */
class Category extends Model
{
    protected $table = 'categories';

    public const ACTIVE = 'active';
    public const PENDING = 'pending';

    /**
     * Find all articles in category.
     *
     * @param array $filters name of category
     * @param int   $limit   set limit on page
     * @param int   $offset  set offset
     *
     * @return array
     */
    public function getArticles(array $filters = [], $limit = 0, $offset = 0): array
    {
        //by default return all articles (if filter is pending) - return just pending.
        return Article::find(
            // Merge additional filters if specified
            array_merge(['category_id' => $this->id], $filters),
            $limit,
            $offset,
            ['id' => 'DESC'] // order
        );
    }
}
