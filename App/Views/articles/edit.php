<h1>Edit article</h1>
<?php if ($validation->getErrors()) : ?>
<?php endif ?>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" value="<?php echo $_POST['title'] ?? $article->getTitle(); ?>"
        class="form-control <?php echo $validation->hasError('title') ? 'is-invalid' : '' ?>" id="title">
        <div class="invalid-feedback">
            <?php echo $validation->getError('title'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea name="content" class="form-control <?php echo $validation->hasError('content') ? 'is-invalid' : '' ?>" id="content"><?php echo $_POST['content'] ?? $article->getContent(); ?></textarea>
        <div class="invalid-feedback">
            <?php echo $validation->getError('content'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="category_id">Category</label>
        <select name="category_id" class="custom-select <?php echo $validation->hasError('category_id') ? 'is-invalid' : '' ?>" id="category_id">
            <option>Choose category</option>
                <?php foreach ($categories as $category) : ?>
                    <option <?php echo $article->getCategoryId() == $category->getId() ? 'selected' : ''; ?> value="<?php echo $category->getId() ?>"><?php echo $category->getTitle() ?></option>
                <?php endforeach ?>
        </select>
        <div class="invalid-feedback">
            <?php echo $validation->getError('category_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" class="custom-select <?php echo $validation->hasError('status') ? 'is-invalid' : '' ?>" id="status">
            <option value="">Choose...</option>
            <option <?php echo $article->getStatus() == 'pending' ? 'selected' : ''; ?> value="pending">Pending</option>
            <option <?php echo $article->getStatus() == 'active' ? 'selected' : ''; ?> value="active">Active</option>
      </select>
      <div class="invalid-feedback">
          <?php echo $validation->getError('status'); ?>
      </div>
    </div>
    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" name="image" class="form-control-file <?php echo $validation->hasError('image') ? 'is-invalid' : '' ?>" id="image">
        <div class="invalid-feedback">
            <?php echo $validation->getError('image'); ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Edit</button>
</form>
