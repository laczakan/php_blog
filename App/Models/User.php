<?php

namespace App\Models;

/**
 * User model to manage users.
 *
 * @category Model
 * @package  App
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Function to get all user Articles
     *
     * @param array $filters set filter (ACTIVE/PENDING/DELETED)
     * @param int   $limit   set limit to show per page
     * @param int   $offset  set offset (5-10 etc)
     *
     * @return array
     */
    public function getArticles(array $filters = [], $limit = 0, $offset = 0): array
    {
        $order = ['id' => 'DESC'];
        //by default return all articles (if filter is pending) - return just pending.
        return Article::find(
            array_merge(['user_id' => $this->id], $filters),
            $limit,
            $offset,
            $order
        );
    }

    /**
     * Check whether user exists with given email address.
     *
     * @param string $email check email from
     *
     * @return User|null
     */
    public function existByEmail(string $email)
    {
        // Prepare SQL query
        $sql = "SELECT * FROM `users` WHERE `email` = :email";

        // Prepare parameters to bind
        $params = [
            'email' => $email,
        ];

        $user = new User();

        $result = $user->findOne($sql, $params);
        if (!$result) {
            return null;
        }
        $user->fill((array) $result);
        // Execute query and return a result
        return $user;
    }

    /**
     * Function to delete user photo
     *
     * @return void
     */
    public function deleteImageFile()
    {
        if ($this->image && file_exists(ROOT_PATH . '/public/upload/users/' . $this->image)) {
            //delete current file
            unlink(ROOT_PATH . '/public/upload/users/' . $this->image);
        }
    }
}
