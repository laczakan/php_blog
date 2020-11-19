<?php

namespace App;

use App\Libraries\Auth;

?>
<header>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="<?php echo url('') ?>">Blog v5</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="<?php echo url('') ?>">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('articles') ?>">Articles</a>
                </li>
                <?php if (Auth::loggedIn()) :?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('articles/add') ?>">Add article</a>
                    </li>
                <?php else : ?>
                    <li class="nav-item ml-auto">
                        <a class="nav-link" href="<?php echo url('auth/login') ?>">Log In</a>
                    </li>
                    <li class="nav-item ml-auto">
                        <a class="nav-link" href="<?php echo url('auth/register') ?>">Register</a>
                    </li>
                <?php endif ?>
            </ul>
            <?php if (Auth::loggedIn()) :?>
                <ul class="navbar-nav mr-sm-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo 'HELLO ' . Auth::getUser()->name . '!' ?></a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="<?php echo url('auth/logout') ?>">Logout</a>
                            <a class="dropdown-item" href="<?php echo url('users') ?>">My profile</a>
                            <a class="dropdown-item" href="<?php echo url('users/articles') ?>">My articles</a>
                        </div>
                    </li>
                </ul>
            <?php endif ?>
        </div>
    </nav>
</header>
