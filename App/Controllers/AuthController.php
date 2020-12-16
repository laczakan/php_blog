<?php

namespace App\Controllers;

use App\Libraries\Validation;
use App\Models\User;
use App\Libraries\Auth;

/**
 * Auth controller which is called.
 *
 * @category Controller
 * @package  App
 */
class AuthController extends Controller
{
    /**
     * Login user in the system.
     *
     * @return void
     */
    public function loginAction(): void
    {
        // Create empty object to validate global POST variable
        $validation = new Validation();

        // Set global POST variable as a $data to validate
        $validation->setData($_POST);

        // Set Rules for data
        $validation->setRules([
            'email' => ['required', 'email', 'exist'],
            'password' => ['required', 'between:4:32'],
        ]);

        // Pass all variables to the view
        $this->variables = [
            'validation' => $validation,
        ];

        // Validate if post is sent
        if (!empty($_POST)) {
            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {

                // Try to get user by email and password (data from POST controller)
                $user = User::findFirst([
                    'email' => $_POST['email'],
                    'password' => md5(SECRET . $_POST['password'])
                ]);

                // Check whether user has been found
                if ($user) {
                    // Login the user (store user data in the session)
                    Auth::login($user);

                    // Redirect user to the home page
                    redirect();
                } else {
                    // Add custom errors to the field
                    $validation->addError('password', "Field <em>password</em> is incorrect");
                }
            } else {
                set_alert('warning', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logoutAction(): void
    {
        // Remove user from the session
        Auth::logout();

        // Redirect user to the home page
        redirect();
    }

    /**
     * Register user in system
     *
     * @return void
     */
    public function registerAction()
    {
        $validation = new Validation();

        // Set global POST variable as a $data to validate
        $validation->setData($_POST);

        // Set Rules for data
        $validation->setRules([
            'name' => ['required', 'minimum:5', 'maximum:32'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'minimum:4', 'maximum:32'],
        ]);

        // Pass all variable to the view
        $this->variables = [
            // Key-value  sent to the views
            'validation' => $validation,
        ];

        // Validate if post is sent
        if (!empty($_POST)) {
            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Store the user in the memory
                 $user = new User();

                $user->fill([
                     'name' => $_POST['name'],
                     'email' => $_POST['email'],
                     'password' => md5(SECRET . $_POST['password']),
                ]);

                // Store user in database
                 $created = $user->create();

                 set_alert('success', '<b>Success!</b> Now you can log in!');

                // Redirect the user to home page if created
                if ($created) {
                    return redirect('auth/login');
                }
            } else {
                set_alert('warning', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }
}
