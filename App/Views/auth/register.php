<h1>Register new User</h1>
<hr>
<form method="POST">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('name') ? 'is-invalid' : '' ?>" id="name">
        <div class="invalid-feedback">
            <?php echo $validation->getError('name'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="email">Email address</label>
        <input type="text" name="email" value="<?php echo $_POST['email'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('email') ? 'is-invalid' : '' ?>" id="email">
        <div class="invalid-feedback">
            <?php echo $validation->getError('email'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" value="<?php echo $_POST['password'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('password') ? 'is-invalid' : '' ?>" id="password">
        <div class="invalid-feedback">
            <?php echo $validation->getError('password'); ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
