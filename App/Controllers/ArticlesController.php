<?php

namespace App\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Models\Comment;
use App\Models\Category;
use App\Libraries\Pagination;
use App\Libraries\Validation;
use App\Libraries\Auth;
use Exception;

/**
 * Article controller which is called.
 *
 * @category Controller
 * @package  App
 */
class ArticlesController extends Controller
{
    /**
     * Display all articles.
     *
     * @return void.
     */
    public function indexAction()
    {
        //set a limit of articles on page
        $limit = 5;

        //take page from GET if is set. if not set:1.
        $page = $_GET['page'] ?? 1;

        // for page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        //find articles ([array status - only active], limit=5, with offset, sort desc)
        $articles = Article::find(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        //prepare pagination
        $pagination = new Pagination();

        //set current page to pagination
        $pagination->setPage($page);

        //count how many active articles are in database and set the number to pagination total
        $pagination->setTotal(Article::count(['status' => Article::ACTIVE]));

        //set limit to show 5 per page
        $pagination->setLimit($limit);

        try {
            //callculate previous, next and pages
            $pagination->calculate();
        } catch (Exception $e) {
            //do nothing
        }

        //send variables to view (array with articles, plus pagination data)
        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination,
        ];
    }

    /**
     * Add articles
     *
     * @return bool
     */
    public function addAction()
    {
        //add article only if logged in
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //get all active categories from database
        $categories = Category::find(['status' => Category::ACTIVE]);

        // $ids = [];
        //
        // foreach ($categories as $category) {
        //     $ids[] = $category->id;
        // }


        // $ids = array_map(function ($category) {
        //     return $category->id;
        // }, $categories);

        //foreach category get only category ID (get only id from categories and return array).
        $ids = array_map(fn($category) => $category->id, $categories);

        //prepare validation for article (so we can use it in view before form is sent)
        $validation = new Validation();

        //combine post and files
        $data = array_merge($_POST, $_FILES);

        //post + files = data -----will be validated
        $validation->setData($data);

        //set all rules for validation
        $validation->setRules([
            'title' => ['required', 'between:4:150'],
            'content' => ['required', 'minimum:4'],
            'image' => [
                'file:image/jpeg,image/png',
                "size:2000000"
            ],
            'status' => ['required', 'in:' . join(',', [Article::PENDING, Article::ACTIVE])],
            'category_id' => ['required', 'in:' . join(',', $ids)]
        ]);

        //set custom label in validation errors.
        $validation->setLabels([
            'category_id' => 'Category', // For category_id field
        ]);

        //send to view
        $this->variables = [
            'validation' => $validation,
            'categories' => (array) $categories,
        ];

        //if sent
        if (!empty($data)) {
            // check all the rules and - add errors if not passed
            $validation->validate();

            // get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Create empty article object
                $article = new Article();

                //fill the array $data[from form]
                $article->fill([
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'user_id' => Auth::getLoggedInUserId(),
                    'status' => $data['status'],
                ]);

                // if image was sent
                if (!empty($data['image'])) {
                    $from = $_FILES['image']['tmp_name'];
                    $to = ROOT_PATH . '/public/upload/articles/';

                    //generate unique Id name
                    $filename = uniqid();

                    //pathinfo — Returns information about a file extension from sent filename
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                    // //check if article has image in database and file exist on the disc.
                    // if (
                    //     $article->getImage()
                    //     && file_exists(ROOT_PATH . '/public/upload/articles/' . $article->getImage())
                    // ) {
                    //     //delete current file
                    //     unlink(ROOT_PATH . '/public/upload/articles/' . $article->getImage());
                    // }

                    //move temporary file to the right location on disc.
                    $moved = move_uploaded_file($from, $to . $filename . '.' . $ext);

                    if ($moved) {
                        // set image file name in the article object
                        $article->fill([
                            'image' => $filename . '.' . $ext,
                        ]);
                    }
                }

                //create new article in database
                $article = $article->create();

                set_alert('success', '<b>Success!</b> Article has been added!');

                return redirect('articles');
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Show details of single article together with adding comment
     *
     * @return bool
     */
    public function detailsAction()
    {
        //get first parameter from URL as article $id.($this->params are from Controller)
        // $id = $this->params[0];
        $id = $this->getParam();

        // if NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        /**
        * Call static function from Model which return article object or null.
        *
        * // $article = new Article();
        * // $article->findOne($sql, $params);
        */
        $article = Article::findById($id);

        // to get author for article
        $user = $article->getUser();

        // set variable in Controller which will be sent to the Views (afterAction())
        $this->variables = [
            'article' => $article,
            'author' => $user,
        ];

        //start of comment validation
        $validation = new Validation();

        //from post data
        $validation->setData($_POST);

        $validation->setRules([
            'content' => ['required', 'minimum:4'],
        ]);

        // added to $this->variables another variable(validation).
        $this->variables['validation'] = $validation;

        if (!empty($_POST)) {
            if (!Auth::loggedIn()) {
                return redirect('auth/login');
            }

            // check all the rules and - add errors if not passed
            $validation->validate();

            // get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Create empty comment object
                $comment = new Comment();

                // $comment->setArticleId($id);
                // $comment->setContent($_POST['content']);
                // $comment->setUserId(Auth::getLoggedInUserId());

                $comment->fill([
                    'content' => $_POST['content'],
                    'article_id' => $article->getId(),
                    'user_id' => Auth::getLoggedInUserId(),
                ]);

                //insert comment to database
                $comment = $comment->create();

                set_alert('success', '<b>Success!</b> Comment has been added!');

                return redirect('articles/details/' . $article->getId());
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Edit single articles
     *
     * @return bool
     */
    public function editAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //get first parameter from URL as article $id.($this->params are from Controller)
        // $id = $this->params[0];
        $id = $this->getParam();

        // if NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        //find article from url param id
        $article = Article::findById($id);

        if (!$article) {
            return redirect('articles');
        }

        // to get author for article
        $user = $article->getUser();

        if (!$article->canEdit()) {
            return redirect('articles/details/' . $article->getId());
        }

        //find all active categories
        $categories = Category::find(['status' => Category::ACTIVE]);

        //get only the ids from the categories and return array
        $ids = array_map(fn($category) => $category->id, $categories);

        // set variable in Controller which will be sent to the Views (afterAction())
        $this->variables = [
            'article' => $article,
            'author' => $user,
            'categories' => $categories,
        ];

        $validation = new Validation();

        $data = array_merge($_POST, $_FILES);

        $validation->setData($data);

        $validation->setRules([
            'title' => ['required', 'between:4:100'],
            'content' => ['required', 'minimum:4'],
            'image' => [
                'file:image/jpeg,image/png',
                "size:2000000"
            ],
            'status' => ['required', 'in:' . join(',', [Article::PENDING, Article::ACTIVE])],
            'category_id' => ['required', 'in:' . join(',', $ids)]
        ]);

        $validation->setLabels([
            'category_id' => 'Category',
        ]);

        //add variables validation to be sent to the view
        $this->variables['validation'] = $validation;

        if (!empty($data)) {
            // check all the rules and - add errors if not passed
            $validation->validate();

            // get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // fill existing article object from form
                $article->fill([
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'user_id' => $article->user_id,
                    'status' => $data['status'],
                ]);

                // if image was sent and there is no upload error
                if (isset($_FILES['image']['error']) &&  $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                    $from = $_FILES['image']['tmp_name'];
                    $to = ROOT_PATH . '/public/upload/articles/';
                    $filename = uniqid();
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                    //check if article has image in database and file exist on the disc.
                    if (
                        $article->getImage()
                        && file_exists(ROOT_PATH . '/public/upload/articles/' . $article->getImage())
                    ) {
                        //delete current file
                        unlink(ROOT_PATH . '/public/upload/articles/' . $article->getImage());
                    }

                    //move temporary file to the right location on disc.
                    $moved = move_uploaded_file($from, $to . $filename . '.' . $ext);

                    if ($moved) {
                        // set image file name in the article object
                        $article->fill([
                            'image' => $filename . '.' . $ext,
                        ]);
                    }
                }

                // run update sql query
                $updated = $article->update();

                set_alert('success', '<b>Success!</b> Article has been edited!');

                if ($updated) {
                    return redirect('articles/details/' . $article->getId());
                }
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Delete single article with all comments
     *
     * @return bool
     */
    public function deleteAction()
    {
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        //get first parameter from URL as comment $id.($this->params are from Controller)
        // $id = $this->params[0];
        $id = $this->getParam();

        // Call static function from Model which return comment object or null.
        $article = Article::findById($id);

        if (!$article) {
            return redirect();
        }

        if (!$article->canDelete()) {
            return redirect('articles/details/' . $article->id);
        }

        $comments = $article->getComments();

        // delete each comment in the loop
        foreach ($comments as $comment) {
            $comment->delete();
        }

        // delete article on the end
        $article->delete();

        set_alert('info', '<b>Success!</b> Your article has been deleted!');

        return redirect('articles');
    }

    /**
     * Show user active articles
     *
     * @return bool
     */
    public function userAction()
    {
        //get first parameter from URL as article $id.($this->params are from Controller)
        $id = $this->getParam();

        // if NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        //find user by url param id
        $user = User::findById($id);

        if (!$user) {
            return redirect('articles');
        }

        //set a limit of articles on page
        $limit = (int) ($_GET['limit'] ?? 5);

        //take page from GET if is set. if not set:1.
        $page = (int) ($_GET['page'] ?? 1);

        // for page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        $pagination = new Pagination();

        $pagination
            ->setPage($page)
            ->setLimit($limit)
            //count all active articles for the user
            ->setTotal(Article::count([
                'user_id' => $user->getId(),
                'status' => Article::ACTIVE
            ]));

        try {
            //callculate previous, next and pages
            $pagination->calculate();
        } catch (Exception $e) {
            //do nothing;
        }

        //get active articles for the user with limit and offset
        $articles = $user->getArticles(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        //tell controller to use index view from articles
        $this->setView('articles/index');

        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination,

            //title to show name of the user's articles
            'title' => "{$user->getName()}'s articles:"
        ];
    }

    /**
     * Show all articles for category
     *
     * @return bool
     */
    public function categoryAction()
    {
        //get first parameter from URL as category $name (not numeric)= ($this->params are from Controller)
        $name = $this->getParam();

        // if NO parameters = redirect to Home Page
        if (!$name) {
            return redirect('');
        }

        //category = find category by name column => name param from URL
        $category = Category::findFirst(['name' => $name]);

        if (!$category) {
            return redirect('articles');
        }

        //set a limit of articles on page
        $limit = (int) ($_GET['limit'] ?? 5);

        //take page from GET if is set. if not set:1.
        $page = (int) ($_GET['page'] ?? 1);

        // for page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        $pagination = new Pagination();

        $pagination->setPage($page);

        //count active articles for the category
        $pagination->setTotal(Article::count([
            'category_id' => $category->getId(),
            'status' => Article::ACTIVE
        ]));
        $pagination->setLimit($limit);

        try {
            $pagination->calculate();
        } catch (Exception $e) {
            //do nothing;
        }

        //get active articles for the category with limit and offset
        $articles = $category->getArticles(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        //tell controller to use index view from articles
        $this->setView('articles/index');

        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination,

            //show title of category
            'title' => "{$category->getTitle()} articles:"
        ];
    }
}
