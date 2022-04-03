<?php
$title = "Blog";
$description = "A blog with hacking-related articles. Writeups of challenges, stories and guides with code examples and detailed explanations";
require_once("../include/header.php"); ?>

    <h1 class="my-4"><code>Blog</code></h1>
    <?php displayMessage() ?>

    <div>
        <?php
        $response = sql_query("SELECT * FROM folders WHERE parent IS NULL");

        if ($response) {
            while ($row = $response->fetch_assoc()) {
                echo "<a class='folder' href='/blog/folder/$row[url]'><i class='fa-solid $row[icon]'></i>$row[title]</a>";
            }
        }
        ?>
    </div>
    <br>
    <a class='folder' href='/blog/all_posts'><i class="fa-solid fa-earth-americas"></i>All posts</a>

<?php if ($admin) { ?>
    <h3 class="my-4"><code>Admin</code></h3>
    <a href="create_post" class="folder"><i class="fa-solid fa-plus"></i>Create post</a>
    <a href="create_folder" class="folder"><i class="fa-solid fa-folder-plus"></i>Create folder</a>
    <a href="hidden" class="folder"><i class="fa-solid fa-eye-slash"></i>Hidden posts</a>
<?php } ?>

    <br>
    <h3 class="my-4"><code>New posts</code></h3>
<?php
$response = sql_query("SELECT * FROM posts WHERE featured=1 ORDER BY timestamp DESC");

if ($response->num_rows > 0) {
    echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
    while ($row = $response->fetch_assoc()) { ?>
        <div class="col">
            <div class="card h-100">
                <a href="/blog/post/<?= $row['url'] ?>">
                    <img class="card-img-top" src="/img/blog/<?= $row['img'] ?>">
                </a>
                <div class="card-body">
                    <p class="card-text tags">
                        <?php
                        $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row['id']]);

                        while ($tag_row = $tags->fetch_assoc()) {
                            echo "<span class='tag tag-$tag_row[class]'>$tag_row[name]</span>";
                        }
                        ?>
                    </p>
                    <h4 class="card-title">
                        <a href="/blog/post/<?= $row['url'] ?>">
                            <code><?= $row['title'] ?></code>
                        </a>
                    </h4>
                    <p class="card-text"><?= $row['description'] ?></p>
                </div>
                <div class="card-footer text-muted">
                    <?= time_to_ago($row['timestamp']) ?>
                </div>
            </div>
        </div>
    <?php }
} else {
    echo '<p class="lead">No posts yet</p>';
}?>

<?php require_once("../include/footer.php"); ?>