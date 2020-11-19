<h1>Edit comment</h1>
<form method="POST">
    <div class="form-group">
        <label for="content">Content</label>
        <input type="text" name="content" value="<?php echo $_POST['content'] ?? $comment->getContent(); ?>"
        class="form-control <?php echo $validation->hasError('content') ? 'is-invalid' : '' ?>" id="content">
        <div class="invalid-feedback">
            <?php echo $validation->getError('content'); ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Edit</button>
</form>
