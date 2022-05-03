<?php require_once("../include/header.php");

if (isset($_GET["url"])) {
    $response = sql_query("SELECT * FROM folders WHERE url=?", [$_GET["url"]]);
} else if (isset($_GET["id"])) {
    $response = sql_query("SELECT * FROM folders WHERE id=?", [$_GET["id"]]);
} else {
    returnMessage("error_folder", "/blog/");
}

$row = $response->fetch_assoc();

if ($response->num_rows === 0) {
    returnMessage("error_folder", "/blog/");
}

$response_breadcrumbs = sql_query("SELECT T2.url, T2.title 
                                    FROM ( 
                                        SELECT 
                                            @r AS _id, 
                                            (SELECT @r := parent FROM folders WHERE id = _id) AS parent, 
                                            @l := @l + 1 AS lvl 
                                        FROM 
                                            (SELECT @r := ?, @l := 0) vars, 
                                            folders h 
                                        WHERE @r <> 0) T1 
                                    JOIN folders T2 
                                    ON T1._id = T2.id 
                                    ORDER BY T1.lvl DESC", [$row["id"]]);

?>

    <meta name="og:type" content="article" />
    <meta name="description" content="<?= htmlspecialchars($row['description']) ?>">
    <meta name="og:description" content="<?= htmlspecialchars($row['description']) ?>" />
    <meta name="og:image" content="<?= get_baseurl() ?>/img/blog/<?= $row['img'] ?>" />
    <meta name="og:site_name" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>">
    <meta property="og:article:section" content="1" />
    <meta property="og:article:author" content="Jorian Woltjer" />
    <meta name="twitter:card" content="summary_large_image">

<nav aria-label="breadcrumb">
    <ol class="breadcrumb my-4">
        <li class="breadcrumb-item"><a href="/blog"><code>Blog</code></a></li>
        <?php
        $i = 0;
        while ($row_bc = $response_breadcrumbs->fetch_assoc()) {
            $i++;
            if ($i === $response_breadcrumbs->num_rows) { // If last row
                echo "<li class='breadcrumb-item active' aria-current='page'><h1><code>$row_bc[title]</code></h1></li>";
            } else {
                echo "<li class='breadcrumb-item'><a href='/blog/folder/$row_bc[url]'><code>$row_bc[title]</code></a></li>";
            }
        } ?>
    </ol>
</nav>

<?php  // Title: folder + title
if ($response_breadcrumbs->num_rows >= 2) {
    $response_breadcrumbs->data_seek($response_breadcrumbs->num_rows-2);
    $folder = $response_breadcrumbs->fetch_assoc()['title'];
} else {
    $folder = "Blog";  // If no parent
}
$title = $folder." - ".$row['title'];
?>
<title><?= $title ?> | Jorian Woltjer</title>
<meta name="og:title" content="<?= $title ?> | Jorian Woltjer" />

<hr>
<p class="lead">
    <?php
    $first = first_sentence($row["description"]);
    $rest = substr($row["description"], strlen($first));
    echo $first;
    echo "<span class='desktop-only'>$rest</span>";
    ?>
</p>

<?php if ($admin) { ?>
    <a href="/blog/edit_folder?id=<?= $row['id'] ?>" class="folder" style="margin-bottom: 0"><i class="fa-solid fa-edit"></i>Edit folder</a>
<?php } ?>

<?php
$response_folders = sql_query("SELECT * FROM folders WHERE parent = ? ORDER BY timestamp DESC", [$row["id"]]);

while ($row_folders = $response_folders->fetch_assoc()) { ?>
    <div class="card card-horizontal">
        <div class="row no-gutters">
            <div class="col-sm-3" style="padding: 0;">
                <a href="/blog/folder/<?= $row_folders['url'] ?>">
                    <img src="/img/blog/<?= $row_folders['img'] ?? '../placeholder.png' ?>" class="card-img-top h-100" style="object-fit: cover;" alt="Folder thumbnail">
                </a>
            </div>
            <div class="col-sm-9" style="display: flex; flex-direction: column;">
                <div class="card-body">
                    <h3 class="card-title">
                        <a href="/blog/folder/<?= $row_folders['url'] ?>"><code><?= $row_folders['title'] ?></code></a>
                    </h3>
                    <p class="card-text"><?= $row_folders['description'] ?></p>
                </div>
                <div class="card-footer text-muted">
                    <?= time_to_ago($row_folders['timestamp']) ?>
                </div>
            </div>
        </div>
    </div>
<?php }

$response_posts = sql_query("SELECT * FROM posts WHERE hidden IS NULL AND parent = ? ORDER BY timestamp DESC", [$row["id"]]);

while ($row_posts = $response_posts->fetch_assoc()) { ?>
    <div class="card card-horizontal">
        <div class="row no-gutters">
            <div class="col-sm-3" style="padding: 0;">
                <a href="/blog/post/<?= $row_posts['url'] ?>">
                    <img src="/img/blog/<?= $row_posts['img'] ?>" class="card-img-top h-100" style="object-fit: cover;" alt="Post thumbnail">
                </a>
            </div>
            <div class="col-sm-9" style="display: flex; flex-direction: column;">
                <div class="card-body">
                    <p class="card-text tags">
                        <?php
                        $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row_posts['id']]);

                        while ($tag_row = $tags->fetch_assoc()) {
                            echo "<span class='tag tag-$tag_row[class]'>$tag_row[name]</span>";
                        }
                        ?>
                        <?= $row_posts['points'] ? '+'.$row_posts['points'].' points' : '' ?>
                    </p>
                    <h3 class="card-title">
                        <a href="/blog/post/<?= $row_posts['url'] ?>"><code><?= $row_posts['title'] ?></code></a>
                    </h3>
                    <p class="card-text"><?= $row_posts['description'] ?></p>
                </div>
                <div class="card-footer text-muted">
                    <?= time_to_ago($row_posts['timestamp']) ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php require_once("../include/footer.php"); ?>
