
<!doctype html>

<html lang="en" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Jekyll v4.0.1">

        <title><?php echo SITENAME ?></title>
            <!-- Bootstrap core CSS -->
            <link href="<?php echo url('public/css/bootstrap.min.css') ?>" rel="stylesheet"
            integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
            <!-- Favicons -->
            <meta name="theme-color" content="#563d7c">
            <!-- Custom styles for this template -->
            <link href="<?php echo url('public/css/style.css') ?>" rel="stylesheet">
            <link rel="stylesheet" href="<?php echo url('public/css/ekko-lightbox.css') ?>" />
    </head>

    <body class="d-flex flex-column h-100">
        <?php load_view('partials/header', $variables) ?>
        <main role="main" class="flex-shrink-0">
            <div class="container">
                <!-- show alert messages in all views -->
                <?php if (has_alert()) : ?>
                    <?php echo get_alert() ?>
                <?php endif ?>
                <?php load_view($view, $variables) ?>
            </div>
        </main>
        <?php load_view('partials/footer', $variables) ?>

        <script src="<?php echo url('public/js/jquery-3.5.1.slim.min.js') ?>" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <!-- <script>window.jQuery || document.write('<script src="https://getbootstrap.com/docs/4.5/assets/js/vendor/jquery.slim.min.js"><\/script>')</script> -->
        <script src="<?php echo url('public/js/bootstrap.bundle.min.js') ?>" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
        <script src="<?php echo url('public/js/ekko-lightbox.js') ?>"></script>
        <script src="<?php echo url('public/js/script.js') ?>"></script>
    </body>
</html>
