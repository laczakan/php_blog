<?php

namespace App\Models;

use App\Libraries\Auth;

/**
 * Article model to manage articles.
 *
 * @category Model
 * @package  App
 */
class Article extends Model
{
    //set table name
    protected $table = 'articles';

    public const ACTIVE = 'active';
    public const PENDING = 'pending';
    public const DELETED = 'deleted';

    /**
     * Function to get author of article
     *
     * @return User
     */
    public function getUser(): User
    {
        // its for loaded article (must be loaded before in controller)
        return User::findById($this->user_id);
    }

    /**
     * Find category of Article
     *
     * @return Category
     */
    public function getCategory(): Category
    {
        return Category::findById($this->category_id);
    }

    /**
     * Get all comments for specific article
     *
     * @return array all comments
     */
    public function getComments(): array
    {
        return Comment::find(['article_id' => $this->id, 'status' => Comment::ACTIVE]);
    }

    /**
     * Create new article with set date/time
     *
     * @return bool
     */
    public function create()
    {
        $this->created_at = date('Y-m-d H:i:s');

        return parent::create();
    }

    /**
     * Update article with updated date/time.
     *
     * @return bool
     */
    public function update()
    {
        $this->updated_at = date('Y-m-d H:i:s');

        return parent::update();
    }

    /**
     * Check if user can Edit
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        if (Auth::getLoggedInUserId() == $this->user_id || Auth::isMod() || Auth::isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can Delete
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        if (Auth::getLoggedInUserId() == $this->user_id || Auth::isAdmin()) {
            return true;
        }

        return false;
    }
}
