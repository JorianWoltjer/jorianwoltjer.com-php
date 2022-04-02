<?php require_once("../include/header.php");

if (!$admin) { // Admin only
    header("HTTP/1.1 403 Forbidden");
    exit();
}
?>

<h1 class="my-4"><code>Hidden posts</code></h1>

<?php
$response_posts = sql_query("SELECT * FROM posts WHERE hidden IS NOT NULL");

while ($row = $response_posts->fetch_assoc()) { ?>
    <div class="card">
        <div class="row no-gutters">
            <div class="col-sm-3">
                <img src="/img/blog/<?= $row['img'] ?>" class="card-img-top h-100" style="object-fit: cover;">
            </div>
            <div class="col-sm-9">
                <table class="table-container">
                    <tr><td valign="top">
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
                                    <a href="/blog/post/<?= $row['url'] ?>?hidden=<?= bin2hex($row["hidden"]) ?>"><code><?= $row['title'] ?></code></a>
                                </h3>
                                <p class="card-text"><?= $row['description'] ?></p>
                            </div>
                        </td></tr>
                    <tr><td valign="bottom">
                            <div class="card-footer text-muted">
                                <?= time_to_ago($row['timestamp']) ?>
                            </div>
                        </td></tr>
                </table>
            </div>
        </div>
    </div>
    <br>
<?php }

if ($response_posts->num_rows === 0) {
    echo '<hr><p class="lead">No posts yet</p>';
}

?>

<?php require_once("../include/footer.php"); ?>
