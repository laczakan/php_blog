<h1>Change image</h1>
<br>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="image">Select image:</label>
            <input type="file" name="image" class="form-control-file <?php echo $validation->hasError('image') ? 'is-invalid' : '' ?>" id="image">
            <div class="invalid-feedback">
                <?php echo $validation->getError('image'); ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
<hr>
