<?php require_once("../include/all.php");

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

// Title: folder + title
if ($response_breadcrumbs->num_rows >= 2) {
    $all_breadcrumbs = $response_breadcrumbs->fetch_all(MYSQLI_ASSOC);
    $folder_title = $all_breadcrumbs[$response_breadcrumbs->num_rows-2]["title"];
} else {
    $folder_title = "Blog";  // If no parent
}
$meta_title = $folder_title." - ".$row['title'];
$meta_description = $row['description'];
$meta_image = "/img/blog/".$row['img'];
$meta_large_card = true;

require_once("../include/header.php");
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb my-4">
        <li class="breadcrumb-item"><a href="/blog"><code>Blog</code></a></li>
        <?php
        $response_breadcrumbs->data_seek(0);  // Reset pointer
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
    <a href="/blog/create_post?parent=<?= $row['id'] ?>" class="folder"><i class="fa-solid fa-plus"></i>Create post</a>
    <a href="/blog/create_folder?parent=<?= $row['id'] ?>" class="folder"><i class="fa-solid fa-folder-plus"></i>Create folder</a>
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
                        <a href="/blog/folder/<?= $row_folders['url'] ?>">
                            <i class="fa-solid fa-folder-closed" style="margin-right: 10px"></i><code><?= $row_folders['title'] ?></code>
                        </a>
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

if ($admin) {
    $response_posts = sql_query("SELECT * FROM posts WHERE parent = ? ORDER BY timestamp DESC", [$row["id"]]);
} else {
    $response_posts = sql_query("SELECT * FROM posts WHERE hidden IS NULL AND parent = ? ORDER BY timestamp DESC", [$row["id"]]);
}

while ($row_posts = $response_posts->fetch_assoc()) {
    if ($row_posts['hidden'] && $admin) {
        $row_posts['url'] .= "?hidden=" . bin2hex($row_posts['hidden']);
    }
    ?>
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
                        $tags = sql_query("SELECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row_posts['id']]);

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
                    <?= time_to_ago($row_posts['timestamp']) ?> -
                    <span class="darken"><i class="far fa-eye"></i> <?= $row_posts["hidden"] === NULL ? $row_posts["views"]." views" : "<b>Hidden</b>" ?></span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php require_once("../include/footer.php"); ?>
