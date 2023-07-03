<?php
$meta_title = "Blog";
$meta_description = "A blog with cybersecurity-related articles. Writeups of challenges in Capture The Flag (CTF) events, stories about hacking and guides with code examples and detailed explanations.";
require_once("../include/header.php"); ?>

    <h1 class="my-4"><code>Blog</code></h1>
    <?php displayMessage() ?>

    <!-- Categories -->
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
    <hr class="higher-top">
    <a class='folder folder-big' href='/blog/search'><i class="fa-solid fa-magnifying-glass"></i>Search</a>
    <a class='folder folder-icon-only' href='/blog/rss.xml' title="RSS Feed"><i class="fa-solid fa-square-rss"></i></a>
<?php if ($admin) { ?>
    <a href="/blog/hidden" class="folder"><i class="fa-solid fa-eye-slash"></i>Hidden posts</a>
<?php } ?>

    <!-- New posts -->
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
                    <img class="card-img-top" src="/img/blog/<?= $row['img'] ?>" alt="Post thumbnail">
                </a>
                <div class="card-body">
                    <p class="card-text tags">
                        <?php
                        $tags = sql_query("SELECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row['id']]);

                        while ($tag_row = $tags->fetch_assoc()) {
                            echo "<span class='tag tag-$tag_row[class]'>$tag_row[name]</span>";
                        }
                        ?>
                    </p>
                    <h4 class="card-title">
                        <a href="/blog/post/<?= $row['url'] ?>">
                            <code><?= htmlspecialchars($row['title']) ?></code>
                        </a>
                    </h4>
                    <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                </div>
                <div class="card-footer text-muted">
                    <?= time_to_ago($row['timestamp']) ?> -
                    <span class="darken"><i class="far fa-eye"></i> <?= $row["hidden"] === NULL ? $row["views"]." views" : "<b>Hidden</b>" ?></span>
                </div>
            </div>
        </div>
    <?php }
} else {
    echo '<p class="lead">No posts yet</p>';
}?>

<?php require_once("../include/footer.php"); ?>