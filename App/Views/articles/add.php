<h1>Add a new article:</h1>
<hr>
<!-- executes on every requests(view page, send form) by default returns empty array -->
<?php if ($validation->getErrors()) : ?>
<?php endif ?>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" value="<?php echo $_POST['title'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('title') ? 'is-invalid' : '' ?>" id="title">
        <div class="invalid-feedback">
            <?php echo $validation->getError('title'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea name="content" class="form-control <?php echo $validation->hasError('content') ? 'is-invalid' : '' ?>" id="content" placeholder="Put some content"><?php echo $_POST['content'] ?? ''; ?></textarea>
        <div class="invalid-feedback">
            <?php echo $validation->getError('content'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="category_id">Category</label>
        <select name="category_id" class="custom-select <?php echo $validation->hasError('category_id') ? 'is-invalid' : '' ?>" id="category_id">
            <option value="" <?php echo empty($_POST['category_id']) ? 'selected' : ''; ?>>Choose category</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo $category->getId() ?>" <?php echo !empty($_POST['category_id']) && $_POST['category_id'] == $category->getId() ? 'selected' : ''; ?>><?php echo $category->getTitle() ?></option>
                <?php endforeach ?>
        </select>
        <div class="invalid-feedback">
            <?php echo $validation->getError('category_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" name="image" class="form-control-file <?php echo $validation->hasError('image') ? 'is-invalid' : '' ?>" id="image">
        <div class="invalid-feedback">
            <?php echo $validation->getError('image'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" class="custom-select <?php echo $validation->hasError('status') ? 'is-invalid' : '' ?>" id="status">
            <option selected>Choose...</option>
            <option value="pending">Pending</option>
            <option value="active">Active</option>
        </select>
        <div class="invalid-feedback">
            <?php echo $validation->getError('status'); ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Add article</button>
</form>
