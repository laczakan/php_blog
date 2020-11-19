<?php

use App\Libraries\Auth;

?>
<div class="jumbotron">
    <?php if ($article->canEdit()) : ?>
        <a href="<?php echo BASE_URL; ?>articles/edit/<?php echo $article->getId() ?>" type="submit" class="btn btn-primary float-right mr-1" id="edit">Edit</a>
    <?php endif ?>
    <?php if ($article->canDelete()) : ?>
        <a href="<?php echo BASE_URL; ?>articles/delete/<?php echo $article->getId() ?>" type="submit" class="btn btn-danger float-right mr-1" id="edit" onclick="return confirm('Are you sure you want delete all article with comments?')">Delete</a>
    <?php endif ?>
    <div class="row">
        <div class="col-sm-8">
            <h1 class=""><?php echo $article->getTitle(); ?></h1>
            <p class="lead">
                Created on: <?php echo $article->getCreatedAt()?>
                by <a href="<?php echo url('articles/user/' . $author->getId())?>"><?php echo $author->getName() ?></a>
                in <a href="<?php echo url('articles/category/' . $article->getCategory()->getName())?>"><?php echo $article->getCategory()->getTitle() ?></a>
            </p>
        </div>
        <?php if ($article->getImage()) : ?>
            <div class="col-sm-4">
                <a href="<?php echo url('public/upload/articles/' . ($article->getImage() ?? 'null.jpeg')) ?>" data-toggle="lightbox" data-gallery="img-gallery">
                    <img src="<?php echo url('public/upload/articles/' . ($article->getImage() ?? 'null.jpeg')) ?>" class="img-fluid img-thumbnail rounded">
                </a>
            </div>
        <?php endif ?>
    </div>
    <br>
    <p><?php echo $article->getContent()?></p>
</div>

<h3>Comments: </h3>
<?php if ($comments = $article->getComments()) : ?>
    <?php foreach ($comments as $comment) : ?>
        <div class="media">
            <a href="<?php echo url('articles/user/' . $comment->getUserId())?>">
                <img src="<?php echo url('public/upload/users/' . ($comment->getUser()->getImage() ?? 'null.png'))?>" class="mr-3" alt="profile picture" width="64" height="64"></a>
            <div class="media-body alert alert-secondary">
                <p><?php echo $comment->getContent() ?></p>
                <?php if (!$comment->getUpdatedAt()) : ?>
                    <p class="text-muted small "><?php echo $comment->getCreatedAt() ?></p>
                <?php else :?>
                    <p class="text-muted small ">Updated at: <?php echo $comment->getUpdatedAt() ?></p>
                <?php endif ?>
                <?php if ($comment->canEdit()) : ?>
                    <a title="Edit" href="<?php echo BASE_URL; ?>comments/edit/<?php echo $comment->getId() ?>">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil-square" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                        </svg>
                    </a>
                <?php endif ?>
                <?php if ($comment->canDelete()) : ?>
                    <a title="Delete" onclick="return confirm('are you sure?')" href="<?php echo BASE_URL; ?>comments/delete/<?php echo $comment->getId() ?>">
                    <svg title="delete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                    </svg>
                </a>
                <?php endif ?>
            </div>
        </div>
    <?php endforeach ?>
<?php endif ?>

<?php if (Auth::loggedIn()) :?>
    <form method="POST">
        <div class="form-group">
            <label for="content">New comment:</label>
            <input type="text" name="content" value="<?php echo $_POST['content'] ?? ''; ?>"
            class="form-control <?php echo $validation->hasError('content') ? 'is-invalid' : '' ?>" id="content">
            <div class="invalid-feedback">
                <?php echo $validation->getError('content'); ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mb-4">Add comment</button>
    </form>
<?php else :?>
    <a class="mb-6" href="<?php echo BASE_URL; ?>auth/login">Please login to add a comment</a>
<?php endif ?>
