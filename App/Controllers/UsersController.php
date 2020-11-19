<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Test;
use App\Models\Article;
use App\Models\Category;
use App\Libraries\Validation;
use App\Libraries\Auth;
use App\Libraries\Pagination;

/**
 * User controller which is called.
 *
 * @category Controller
 * @package  App
 */
class UsersController extends Controller
{
    /**
     * Show my profile page
     *
     * @return bool
     */
    public function indexAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //get user from session
        $user = Auth::getUser();

        // assign everything to data variable(to keep POST variable original)
        $data = $_POST;

        // check if password field is not empty
        if (!empty($data['password'])) {
            //get password from POST and hash it!
            $data['password'] = md5(SECRET . $data['password']);
        }

        // put a newpassword into variable $newPassword (??- empty string to avoid errors on 1st load)
        $newPassword = $data['newpassword'] ?? '';

        $validation = new Validation();

        // set global POST variable as a $data to validate
        $validation->setData($data);

        // set Rules for data
        $validation->setRules([
            // checked if hashed password from post equils hashed password from database
            'password' => ['required', "same:{$user->getPassword()}"],
            'newpassword' => ['required', 'between:4:32'],
            // checked if confirmnewpassword password from post equils new password from post
            'confirmnewpassword' => ['required', "same:{$newPassword}"],
        ]);

        //set custom labels for the fields in validation errors
        $validation->setLabels([
            'password' => 'Password',
            'newpassword' => 'New password',
            'confirmnewpassword' => 'Confirm new password',
        ]);

        // pass all variable to the view
        $this->variables = [
            'validation' => $validation,
            'user' => $user,
        ];

        // Validate if post is sent
        if (!empty($_POST)) {
            // check all the rules and - add errors if not passed
            $validation->validate();

            // get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                $user->fill([
                    'password' => md5(SECRET . $newPassword),
                ]);

                $user->update();

                set_alert('success', '<b>Success!</b> Password has been changed!');

                return redirect("users");
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Change user image.
     *
     * @return bool
     */
    public function imageAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //from session
        //$id = Auth::getLoggedInUserId();

        //get logged in user object
        $user = Auth::getUser();

        //validate image
        $validation = new Validation();

        // set global FILES variable as a data to validate
        $validation->setData($_FILES);

        // set Rules for data
        $validation->setRules([
            'image' => [
                'file:image/jpeg,image/png',
                "size:2000000"
            ],
        ]);

        // pass all variables to the view
        $this->variables = [
            'validation' => $validation,
        ];

        // check all of the rules
        $validation->validate();

        // get errors if any
        $errors = $validation->getErrors();

        if (isset($_FILES['image']['error']) &&  $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            if (!$errors) {
                $from = $_FILES['image']['tmp_name'];
                $to = ROOT_PATH . '/public/upload/users/';
                $filename = $user->getId();
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                //check if user has image in database and file exist on the disc.
                $user->deleteImageFile();

                //move temporary file to the right location on disc.
                $moved = move_uploaded_file($from, $to . $filename . '.' . $ext);

                if ($moved) {
                    // set image file name in the user object
                    $user->fill([
                        'image' => $filename . '.' . $ext,
                    ]);

                    //update new user image (name) in database.
                    $user->update();

                    //refresh user in the session
                    Auth::login($user);

                    return redirect("users");
                }
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Delete user image
     *
     * @return bool
     */
    public function deleteimageAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //get logged in user object
        $user = Auth::getUser();

        //set null as image
        $user->fill([
            'image' => null,
        ]);

        $user->update();

        //refresh user in the session
        Auth::login($user);

        return redirect("users");
    }

    /**
     * Show all users articles
     *
     * @return bool
     */
    public function articlesAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //from session
        $id = Auth::getLoggedInUserId();

        //get logged in user object
        $user = User::findById($id);

        //set a limit of articles on page
        $limit = (int) ($_GET['limit'] ?? 5);

        //take page from GET if is set. if not set:1.
        $page = (int) ($_GET['page'] ?? 1);

        // for page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        $pagination = new Pagination();

        $pagination->setPage($page);

        //count all articles (pending,active,deleted) for logged in user
        $pagination->setTotal(Article::count([
            'user_id' => $user->getId(),
        ]));
        $pagination->setLimit($limit);

        try {
            $pagination->calculate();
        } catch (Exception $e) {
            // dont do anything
        }

        //get users articles with limit and offset in desc order
        $articles = $user->getArticles([], $limit, $offset, ['id' => 'DESC']);

        //send to view
        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination
        ];
    }

    /**
     * Add new category
     *
     * @return bool
     */
    public function addCategoryAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        if (Auth::isAdmin()) {
            //$categories = Category::find(['status' => Category::ACTIVE]);

            //$ids = array_map(fn($category) => $category->id, $categories);

            //prepare validation for article (so we can use it in view before form is sent)
            $validation = new Validation();

            $validation->setData($_POST);

            //set all rules for validation
            $validation->setRules([
                'name' => ['required', 'between:4:150', 'unique:categories'],
                'title' => ['required', 'between:4:150'],
                'status' => ['required', 'in:' . join(',', [Category::PENDING, Category::ACTIVE])],
            ]);

            //send to view
            $this->variables = [
                'validation' => $validation,
                //'categories' => (array) $categories,
            ];

            if (!empty($_POST)) {
                $validation->validate();

                // get errors if any
                $errors = $validation->getErrors();

                // Check whether validation on all fields passed (no errors)
                if (!$errors) {
                    // Create empty article object
                    $category = new Category();

                    $category->fill([
                        'name' => $_POST['name'],
                        'title' => $_POST['title'],
                        'status' => $_POST['status'],
                    ]);
                    $category = $category->create();

                    set_alert('success', '<b>Success!</b> Category has been added!');

                    return redirect('users');
                } else {
                    set_alert('danger', '<b>Warning!</b> Please correct the errors!');
                }
            }
        } else {
            return redirect('');
        }
    }
}
