<?php

use App\Libraries\Auth;
use App\Models\Users;

?>
<div class="container">
    <div class="row">
        <div class="col-sm-9">
            <h1>
                <?php echo 'HELLO ' . $user->getName() . '!'?>
                <?php if (Auth::getUser()->admin) : ?>
                    <span class="small badge badge-danger">admin</span>
                <?php endif ?>
                <?php if (Auth::getUser()->moderator) : ?>
                    <span class="small badge badge-warning">moderator</span>
                <?php endif ?>
            </h1>
            <h3><?php echo 'email: ' . $user->getEmail() ?></h3>
        </div>
        <div class="col-sm-3">
            <img src="<?php echo url('public/upload/users/' . ($user->getImage() ?? 'null.png')) ?>" class="img-thumbnail rounded-circle float-right">
            <a href="users/image" type="submit" class="btn btn-primary btn-sm mt-2"><?php echo $user->getImage() ? 'Change image' : 'Add image' ?></a>
            <?php if ($user->getImage()) : ?>
                <a href="users/deleteimage" type="submit" class="btn btn-danger btn-sm mt-2">Delete image</a>
            <?php endif ?>
        </div>
    </div>
</div>
<hr>
<h3>Change password:</h3>
<br>
<form method="POST">
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" value="<?php echo $_POST['password'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('password') ? 'is-invalid' : '' ?>" id="password">
        <div class="invalid-feedback">
            <?php echo $validation->getError('password'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="password">New password</label>
        <input type="password" name="newpassword" value="<?php echo $_POST['newpassword'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('newpassword') ? 'is-invalid' : '' ?>" id="newpassword">
        <div class="invalid-feedback">
            <?php echo $validation->getError('newpassword'); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Confirm new password</label>
        <input type="password" name="confirmnewpassword" value="<?php echo $_POST['confirmnewpassword'] ?? ''; ?>"
        class="form-control <?php echo $validation->hasError('confirmnewpassword') ? 'is-invalid' : '' ?>" id="confirmnewpassword">
        <div class="invalid-feedback">
            <?php echo $validation->getError('confirmnewpassword'); ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Change password</button>
</form>
<br>
<?php if ($user == Auth::isAdmin()) : ?>
<a href="users/addcategory" type="submit" class="btn btn-primary mt-2"><?php echo 'Add category' ?></a>
<?php endif ?>
