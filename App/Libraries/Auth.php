<?php

namespace App\Libraries;

use App\Models\User;

/**
 * Auth library to manage user authorization.
 *
 * @category Library
 * @package  App
 */
class Auth
{
    /**
     * Log in user into the system.
     *
     * @param object $user user object
     *
     * @return void (nothing)
     */
    public static function login(User $user): void
    {
        // Store in the session (serialized user object)
        $_SESSION['user'] = serialize($user);
    }

    /**
     * Check whether user is logged in?
     *
     * @return bool true/false
     */
    public static function loggedIn(): bool
    {
        // Check if user is in the session
        return isset($_SESSION['user']);
    }

    /**
     * Get logged in user!
     *
     * @return int|null
     */
    public static function getUser(): ?User
    {
        // Check whether user is logged in
        if (!self::loggedIn()) {
            return null;
        }
        // If loggedIn unserialize the user object and return it
        return unserialize($_SESSION['user']);
    }

    /**
     * Get logged in user ID.
     *
     * @return int|null
     */
    public static function getLoggedInUserId(): ?int
    {
        // Check whether user is logged in
        if (!self::loggedIn()) {
            return null;
        }
        // If loggedIn return ID
        return self::getUser()->id;
    }

    /**
     * Log out user form the system.
     *
     * @return void (nothing)
     */
    public static function logout(): void
    {
        // Remove from the session (user object)
        unset($_SESSION['user']);
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $user = self::getUser();

        // $user->admin (check if user is admin in database - set to 1)
        if (!$user || !$user->admin) {
            return false;
        }

        return true;
    }

    /**
     * Check if user is moderator
     *
     * @return bool
     */
    public static function isMod(): bool
    {
        $user = self::getUser();

        // Check if is logged in and (check if user is moderator in database - set to 1)
        if (!$user || !$user->moderator) {
            return false;
        }

        return true;
    }
}
