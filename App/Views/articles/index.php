<?php if (isset($title)) : ?>
    <h2><?php echo $title ?></h2>
    <hr>
<?php endif ?>

<?php foreach ($articles as $article) : ?>
    <div class="jumbotron ">
        <div class="row">
            <div class="col-sm-10">
                <h3>
                    <a href="<?php echo url('articles/details/' . $article->getId()); ?>" class="text-dark"><?php echo $article->getTitle(); ?></a>
                </h3>
            </div>
            <?php if ($article->getImage()) : ?>
                <div class="col-sm-2">
                    <img src="<?php echo url('public/upload/articles/' . $article->getImage()) ?>" class="img-thumbnail rounded float-right" alt="image">
                </div>
            <?php endif ?>
        </div>
        <br>
        <p class="lead"><?php echo substr($article->getContent(), 0, 150)?>...</p>
        <a class="btn btn-info btn-sm" href="<?php echo url('articles/details/' . $article->getId()); ?>" role="button">More</a>
    </div>
<?php endforeach; ?>

<?php if ($pagination->getPages() > 1) : ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <?php foreach ($pagination->getItems() as $page) : ?>
                <?php if ($page['number']) : ?>
                    <?php if ($page['label'] == 'current') : ?>
                        <li class="page-item active" aria-current="page"><span class="page-link"><?php echo $page['number'] ?></span></li>
                    <?php else : ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page['number'] ?>"><?php echo $page['label'] ?></a></li>
                    <?php endif ?>
                <?php else : ?>
                    <li class="page-item disabled"><span class="page-link"><?php echo $page['label'] ?></span></li>
                <?php endif ?>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif ?>
