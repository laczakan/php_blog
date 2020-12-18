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
        // Set a limit of articles on page
        $limit = 5;

        // Take page from GET if is set. if not set:1.
        $page = $_GET['page'] ?? 1;

        // For page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        // Find articles ([array status - only active], limit=5, with offset, sort desc)
        $articles = Article::find(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        // Prepare pagination (create manual)
        $pagination = new Pagination();

        // Set current page to pagination
        $pagination->setPage($page);

        // Count how many active articles are in database and set the number to pagination total
        $pagination->setTotal(Article::count(['status' => Article::ACTIVE]));

        // Set limit to show 5 per page
        $pagination->setLimit($limit);

        try {
            // Callculate previous, next and pages
            $pagination->calculate();
        } catch (Exception $e) {
            // Do nothing
        }

        // Send variables to view (array with articles, plus pagination data)
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
        // Add article only if logged in
        if (!Auth::loggedIn()) {
            return redirect('auth/login');
        }

        // Get all active categories from database
        $categories = Category::find(['status' => Category::ACTIVE]);

        // Foreach category get only category ID (get only id from categories and return array).
        $ids = array_map(fn($category) => $category->id, $categories);

        // Prepare validation for article (so we can use it in view before form is sent)
        $validation = new Validation();

        // Combine post and files
        $data = array_merge($_POST, $_FILES);

        // Post + files = data -----will be validated
        $validation->setData($data);

        // Set all rules for validation
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

        // Set custom label in validation errors.
        $validation->setLabels([
            'category_id' => 'Category', // For category_id field
        ]);

        // Send to view
        $this->variables = [
            'validation' => $validation,
            'categories' => (array) $categories,
        ];

        // If sent
        if (!empty($data)) {
            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Create empty article object
                $article = new Article();

                // Fill the array $data[from form]
                $article->fill([
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'user_id' => Auth::getLoggedInUserId(),
                    'status' => $data['status'],
                ]);

                // If image was sent
                if (!empty($data['image'])) {
                    $from = $_FILES['image']['tmp_name'];
                    $to = ROOT_PATH . '/public/upload/articles/';

                    // Generate unique Id name
                    $filename = uniqid();

                    // Pathinfo — Returns information about a file extension from sent filename
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                    // Move temporary file to the right location on disc.
                    $moved = move_uploaded_file($from, $to . $filename . '.' . $ext);

                    if ($moved) {
                        // Set image file name in the article object
                        $article->fill([
                            'image' => $filename . '.' . $ext,
                        ]);
                    }
                }

                // Create new article in database
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
        // Get first parameter from URL as article $id.($this->params are from Controller)
        // $id = $this->params[0];
        $id = $this->getParam();

        // If NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        /**
        * Call static function from Model which return article object or null.
        *
        * $article = new Article();
        * $article->findOne($sql, $params);
        */
        $article = Article::findById($id);

        // To get author for article
        $user = $article->getUser();

        // Set variable in Controller which will be sent to the Views (afterAction())
        $this->variables = [
            'article' => $article,
            'author' => $user,
        ];

        // Start of comment validation
        $validation = new Validation();

        // From post data
        $validation->setData($_POST);

        $validation->setRules([
            'content' => ['required', 'minimum:4'],
        ]);

        // Added to $this->variables another variable(validation).
        $this->variables['validation'] = $validation;

        if (!empty($_POST)) {
            if (!Auth::loggedIn()) {
                return redirect('auth/login');
            }

            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {

                // Create empty comment object
                $comment = new Comment();

                // $comment->setContent($_POST['content']);
                // $comment->setArticleId($id);
                // $comment->setUserId(Auth::getLoggedInUserId());

                $comment->fill([
                    'content' => $_POST['content'],
                    'article_id' => $article->getId(),
                    'user_id' => Auth::getLoggedInUserId(),
                ]);

                // Save comment to database
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

        // Get first parameter from URL as article $id.($this->params are from Controller)
        // $id = $this->params[0];
        $id = $this->getParam();

        // If NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        // Find article from url param id
        $article = Article::findById($id);

        if (!$article) {
            return redirect('articles');
        }

        // To get author for article
        $user = $article->getUser();

        if (!$article->canEdit()) {
            return redirect('articles/details/' . $article->getId());
        }

        // Find all active categories
        $categories = Category::find(['status' => Category::ACTIVE]);

        // Get only the ids from the categories and return array
        $ids = array_map(fn($category) => $category->id, $categories);

        // Set variable in Controller which will be sent to the Views (afterAction())
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

        // Add variables validation to be sent to the view
        $this->variables['validation'] = $validation;

        if (!empty($data)) {
            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Fill existing article object from form
                $article->fill([
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'user_id' => $article->user_id,
                    'status' => $data['status'],
                ]);

                // If image was sent and there is no upload error
                if (isset($_FILES['image']['error']) &&  $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                    $from = $_FILES['image']['tmp_name'];
                    $to = ROOT_PATH . '/public/upload/articles/';
                    $filename = uniqid();
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                    // Check if article has image in database and file exist on the disc.
                    if (
                        $article->getImage()
                        && file_exists(ROOT_PATH . '/public/upload/articles/' . $article->getImage())
                    ) {
                        // Delete current file
                        unlink(ROOT_PATH . '/public/upload/articles/' . $article->getImage());
                    }

                    // Move temporary file to the right location on disc.
                    $moved = move_uploaded_file($from, $to . $filename . '.' . $ext);

                    if ($moved) {
                        // Set image file name in the article object
                        $article->fill([
                            'image' => $filename . '.' . $ext,
                        ]);
                    }
                }

                // Run update sql query
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

        // Get first parameter from URL as comment $id.($this->params are from Controller)
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

        // Delete each comment in the loop
        foreach ($comments as $comment) {
            $comment->delete();
        }

        // Delete article on the end
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
        // Get first parameter from URL as article $id.($this->params are from Controller)
        $id = $this->getParam();

        // If NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        // Find user by url param id
        $user = User::findById($id);

        if (!$user) {
            return redirect('articles');
        }

        // Set a limit of articles on page
        $limit = (int) ($_GET['limit'] ?? 5);

        // Take page from GET if is set. if not set:1.
        $page = (int) ($_GET['page'] ?? 1);

        // For page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        $pagination = new Pagination();

        $pagination
            ->setPage($page)
            ->setLimit($limit)

            // Count all active articles for the user
            ->setTotal(Article::count([
                'user_id' => $user->getId(),
                'status' => Article::ACTIVE
            ]));

        try {
            // Callculate previous, next and pages
            $pagination->calculate();
        } catch (Exception $e) {
            // Do nothing;
        }

        // Get active articles for the user with limit and offset
        $articles = $user->getArticles(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        // Tell controller to use index view from articles
        $this->setView('articles/index');

        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination,

            // Title to show name of the user's articles
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
        // Get first parameter from URL as category $name (not numeric)= ($this->params are from Controller)
        $name = $this->getParam();

        // If NO parameters = redirect to Home Page
        if (!$name) {
            return redirect('');
        }

        // Category = find category by name column => name param from URL
        $category = Category::findFirst(['name' => $name]);

        if (!$category) {
            return redirect('articles');
        }

        // Set a limit of articles on page
        $limit = (int) ($_GET['limit'] ?? 5);

        // Take page from GET if is set. if not set:1.
        $page = (int) ($_GET['page'] ?? 1);

        // For page1 show 0-5 articles, page2 show 5-10 articles, page3 show 10-15 ...
        $offset = ($page - 1) * $limit;

        $pagination = new Pagination();

        $pagination->setPage($page);

        // Count active articles for the category
        $pagination->setTotal(Article::count([
            'category_id' => $category->getId(),
            'status' => Article::ACTIVE
        ]));
        $pagination->setLimit($limit);

        try {
            $pagination->calculate();
        } catch (Exception $e) {
            // Do nothing;
        }

        // Get active articles for the category with limit and offset
        $articles = $category->getArticles(['status' => Article::ACTIVE], $limit, $offset, ['id' => 'DESC']);

        // Tell controller to use index view from articles
        $this->setView('articles/index');

        $this->variables = [
            'articles' => $articles,
            'pagination' => $pagination,

            // Show title of category
            'title' => "{$category->getTitle()} articles:"
        ];
    }
}
