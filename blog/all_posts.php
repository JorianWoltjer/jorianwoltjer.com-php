<?php
$title = "Blog - All Posts";
$description = "See all posts on my blog about cybersecurity. Including writeups of Capture The Flag challenges, guides, and more.";
require_once("../include/header.php"); ?>

<h1 class="my-4"><code>All posts</code></h1>
<?php
$page_size = 5;

if (!isset($_GET["page"]) or !is_numeric($_GET["page"])) {
    $_GET["page"] = 1;
}
$page_offset = ($_GET["page"]-1) * $page_size;
$response = sql_query("SELECT * FROM posts WHERE hidden IS NULL ORDER BY timestamp DESC LIMIT ? OFFSET ?", [$page_size, $page_offset]);

if ($response->num_rows > 0) {
    while ($row = $response->fetch_assoc()) { ?>
        <div class="card card-horizontal">
            <div class="row no-gutters">
                <div class="col-sm-2" style="padding: 0;">
                    <a href="/blog/post/<?= $row['url'] ?>">
                        <img src="/img/blog/<?= $row['img'] ?>" class="card-img-top h-100" style="object-fit: cover;">
                    </a>
                </div>
                <div class="col-sm-9" style="display: flex; flex-direction: column;">
                    <div class="card-body">
                        <p class="card-text tags">
                            <?php
                            $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row['id']]);

                            while ($tag_row = $tags->fetch_assoc()) {
                                echo "<span class='tag tag-$tag_row[class]'>$tag_row[name]</span>";
                            }
                            ?>
                            <?= $row['points'] ? '+'.$row['points'].' points' : '' ?>
                        </p>
                        <h3 class="card-title">
                            <a href="/blog/post/<?= $row['url'] ?>"><code><?= $row['title'] ?></code></a>
                        </h3>
                        <p class="card-text"><?= $row['description'] ?></p>
                    </div>
                    <div class="card-footer text-muted">
                        <?= time_to_ago($row['timestamp']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php }
    $response = sql_query("SELECT count(*) FROM posts WHERE hidden IS NULL");
    $count = $response->fetch_row()[0];
    ?>
    <div class="pagination">
        <div class="center">
            <div class="text-white-50">
                <?php
                $pages = ceil($count / $page_size);
                $ellipsis_printed = false;
                for ($i = 1; $i <= $pages; $i++) {
                    if ($i == $_GET["page"]) {  # If current page ?>
                        <li class="nav-item page-number">
                            <span class="nav-link active" href="/blog/all_posts?page=<?= $i ?>"><?= $i ?></span>
                        </li>
                    <?php } else if (abs($_GET["page"] - $i) < 2 + ($_GET["page"] == 1 or $_GET["page"] == $pages)) {  # If page is near current page ?>
                        <li class="nav-item page-number">
                            <a class="nav-link" href="/blog/all_posts?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php } else if ($i == 1 or $i == $pages) {  # If first or last page ?>
                        <li class="nav-item page-number">
                            <a class="nav-link" href="/blog/all_posts?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php } else if (!$ellipsis_printed) {  # ellipsis ?>
                        <i class="fa-solid fa-ellipsis"></i>
                        <?php
                        $ellipsis_printed = true;
                        continue;
                    } else {
                        continue;
                    }
                    $ellipsis_printed = false;
                }
                ?>
            </div>
        </div>
    </div>
<?php } else {
    echo '<p class="lead">No posts yet</p>';
}?>

<?php require_once("../include/footer.php"); ?>