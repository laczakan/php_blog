<h3>Add category:</h3>
<br>
<form method="POST">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" value="<?php echo $_POST['name'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('name') ? 'is-invalid' : '' ?>" id="name">
        <div class="invalid-feedback">
            <?php echo $validation->getError('name'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" value="<?php echo $_POST['title'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('title') ? 'is-invalid' : '' ?>" id="title">
        <div class="invalid-feedback">
            <?php echo $validation->getError('title'); ?>
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
<button type="submit" class="btn btn-primary">Add Category</button>
</form>
