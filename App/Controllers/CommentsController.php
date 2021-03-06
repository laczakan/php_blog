<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Models\Article;
use App\Libraries\Validation;
use App\Libraries\Auth;

/**
 * Comment controller which is called.
 *
 * @category Controller
 * @package  App
 */
class CommentsController extends Controller
{
    /**
     * Edit Comment
     *
     * @return bool
     */
    public function editAction()
    {
        // Get params from url
        $id = $this->getParam();

        // If NO parameters = redirect to Home Page
        if (!$id) {
            return redirect('');
        }

        // Call static function from Model which return comment object or null
        $comment = Comment::findById($id);

        // Comment not found
        if (!$comment) {
            return redirect();
        }

        // To get author for comment
        $user = $comment->getUser();

        // Only owner can edit comment
        if (!$comment->canEdit()) {
            return redirect('articles/details/' . $comment->getArticleId());
        }

        $validation = new Validation();

        // Set data from post
        $validation->setData($_POST);

        $validation->setRules([
            'content' => ['required', 'minimum:4'],
        ]);

        // Set variable in Controller which will be sent to the Views (parent::afterAction())
        $this->variables = [
            'comment' => $comment,
            'author' => $user,
            'validation' => $validation,
        ];

        if (!empty($_POST)) {
            // Check all the rules and - add errors if not passed
            $validation->validate();

            // Get errors if any
            $errors = $validation->getErrors();

            // Check whether validation on all fields passed (no errors)
            if (!$errors) {
                // Fill existing article object
                $comment->fill([
                    'content' => $_POST['content'],
                ]);

                // Fun update
                $updated = $comment->update();

                if ($updated) {
                    set_alert('success', '<b>Success!</b> Comment has been updated!');

                    return redirect('articles/details/' . $comment->getArticleId());
                }
            } else {
                set_alert('danger', '<b>Warning!</b> Please correct the errors!');
            }
        }
    }

    /**
     * Delete single comment
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
        $comment = Comment::findById($id);

        if (!$comment) {
            return redirect();
        }

        // Only owner or admin can delete comment (canDelete function from model)
        if (!$comment->canDelete()) {
            return redirect('articles/details/' . $comment->getArticleId());
        }

        $comment->delete();

        set_alert('info', '<b>Success!</b> Your comment has been deleted!');

        return redirect('articles/details/' . $comment->getArticleId());
    }
}
