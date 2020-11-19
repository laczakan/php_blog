
<div class="row">
    <h3>My articles: </h3>
    <table class="table">
        <thead class ="thead-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Title</th>
                <th scope="col">Status</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <?php foreach ($articles as $article) : ?>
            <tr>
                <th scope="row"><span><?php echo $article->getId(); ?></span></th>
                <td><a href="<?php echo url('articles/details/' . $article->getId()); ?>" class="text-dark"><?php echo substr($article->getTitle(), 0, 120)?>...</a></td>
                <td><span><?php echo $article->getStatus(); ?></span></td>
                <td><a href="<?php echo url('articles/edit/' . $article->getId()); ?>" type="submit" class="btn btn-primary float-right">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php if ($pagination->getPages() > 1) : ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <?php foreach ($pagination->getItems() as $page) : ?>
                <?php if ($page['number']) : ?>
                    <?php if ($page['label'] == 'current') : ?>
                        <li class="page-item active"><span class="page-link"><?php echo $page['number'] ?></span></li>
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
