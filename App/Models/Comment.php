<?php

namespace App\Models;

use App\Libraries\Auth;

/**
 * Comment model to manage comments.
 *
 * @category Model
 * @package  App
 */
class Comment extends Model
{
    protected $table = 'comments';

    public const ACTIVE = 'active';
    public const DELETED = 'deleted';

    /**
     * Function to get author of comments
     *
     * @return User
     */
    public function getUser(): User
    {
        // its for loaded comments (must be loaded before)
        return User::findById($this->user_id);
    }

    /**
     * Get article for which comment is added
     *
     * @return Article
     */
    public function getArticle(): Article
    {
        return Article::findById($this->article_id);
    }

    /**
     * Create comment
     *
     * @return bool
     */
    public function create()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->status = self::ACTIVE;

        return parent::create();
    }

    /**
     * Update comment
     *
     * @return bool
     */
    public function update()
    {
        $this->updated_at = date('Y-m-d H:i:s');

        return parent::update();
    }

    /**
     * Check if user can edit comment
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
     * Check if user can delete comment
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
