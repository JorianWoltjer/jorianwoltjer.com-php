<?php require_once("../include/header.php");

# Find post
if (isset($_GET["url"])) {
    $response = sql_query("SELECT * FROM posts WHERE url=?", [$_GET["url"]]);
} else if (isset($_GET["id"])) {  # id for backwards compatibility
    $response = sql_query("SELECT * FROM posts WHERE id=?", [$_GET["id"]]);
} else {
    returnMessage("error_post", "/blog/");
}

$row = $response->fetch_assoc();

if ($response->num_rows === 0) {
    returnMessage("error_post", "/blog/");
}

// Verify hidden hash
if ($row["hidden"] !== NULL) {
    $hash = $_GET["hidden"] ?? $_GET["hash"];  // Get hidden parameter or hash for backwards compatibility
    if ($hash !== bin2hex($row["hidden"])) {
        returnMessage("error_post", "/blog/");
    }
}

sql_query("UPDATE posts SET views = views+1 WHERE id = ?", [$row["id"]])
?>

    <link rel="stylesheet" href="/assets/highlight/github-dark.min.css">
    <meta name="og:type" content="article" />
    <meta name="description" content="<?= htmlspecialchars($row['description']) ?>" />
    <meta name="og:description" content="<?= htmlspecialchars($row['description']) ?>" />
    <meta name="og:image" content="<?= get_baseurl() ?>/img/blog/<?= $row['img'] ?>" />
    <meta name="og:site_name" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>" />
    <meta property="og:article:section" content="1" />
    <meta property="og:article:author" content="Jorian Woltjer" />
    <meta name="twitter:card" content="summary_large_image">

<?php
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
                                    ORDER BY T1.lvl DESC", [$row['parent']]);
?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href="/blog"><code>Blog</code></a></li>
            <?php
            while ($row_bc = $response_breadcrumbs->fetch_assoc()) {
                echo "<li class='breadcrumb-item'><a href='/blog/folder/$row_bc[url]'><code>$row_bc[title]</code></a></li>";
            }
            ?>
            <li class='breadcrumb-item active' aria-current='page'><h1><code><?= $row['title'] ?></code></h1></li>
        </ol>
    </nav>
    <br>

    <?php  // Title: folder + title + 'writeup' if ctf
    $response_breadcrumbs->data_seek($response_breadcrumbs->num_rows-1);
    $folder = $response_breadcrumbs->fetch_assoc();
    $title = $folder['title']." - ".$row['title'] . (str_starts_with($folder['url'], "ctf") ? ' (Writeup)' : '');
    ?>
    <title><?= $title ?> | Jorian Woltjer</title>
    <meta name="og:title" content="<?= $title ?> | Jorian Woltjer" />

    <p class="tags">
        <?php
        $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row["id"]]);

        while ($row_tag = $tags->fetch_assoc()) {
            echo "<span class='tag tag-$row_tag[class]'>$row_tag[name]</span>";
        }
        ?>
        <?= $row['points'] ? '+'.$row['points'].' points' : '' ?>
    </p>

    <div class="text-muted">
        <?= time_to_ago($row['timestamp']) ?> - <i class="far fa-eye"></i> <?= $row["hidden"] === NULL ? $row["views"]." views" : "<b>Hidden</b>" ?>
    </div>

<?php if (isset($admin) && $admin) { ?>
    <a href="/blog/edit_post?id=<?= $row['id'] ?>" class="folder"><i class="fa-solid fa-edit"></i>Edit post</a>
    <br>
    <br>
<?php } ?>

    <h1><?= $row['title'] ?></h1>
    <div class='blog-content'>
        <?= $row['html'] ?>
    </div>

<?php
$response_prev = sql_query("SELECT url FROM posts WHERE id = (SELECT max(id) FROM posts WHERE id < ?) AND parent=? AND hidden IS NULL;",
    [$row["id"], $row["parent"]]);
$prev = $response_prev->fetch_assoc();

$response_next = sql_query("SELECT url FROM posts WHERE id = (SELECT min(id) FROM posts WHERE id > ?) AND parent=? AND hidden IS NULL;",
    [$row["id"], $row["parent"]]);
$next = $response_next->fetch_assoc();
?>
    <div class="pagination">
        <div class="left">
            <?php
            if ($prev) {
                echo '<a href="/blog/post/'.$prev["url"].'"><i class="fa-solid fa-caret-left"></i> Previous</a>';
            }
            ?>
        </div>
        <div class="center">
            <div class="text-white-50">
                <p>The end! If you have any questions feel free to ask me anywhere on my <a href="/contact" target="_blank">Contacts</a></p>
            </div>
        </div>
        <div class="right">
            <?php
            if ($next) {
                echo '<a href="/blog/post/'.$next["url"].'">Next <i class="fa-solid fa-caret-right"></i></a>';
            }
            ?>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="" id="previewImage" style="width: 100%;" alt="">
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/highlight/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    <script>
        // Copy button from code blocks
        function copy_code(element) {
            const code = element.parentElement.parentElement.getElementsByTagName("code")[0].innerText;
            navigator.clipboard.writeText(code);
            const tooltip = new bootstrap.Tooltip(element);
            tooltip.show();
            setTimeout(function() {
                tooltip.dispose();
            }, 2000);
        }

        // Open all links in new tab
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll(".blog-content a:not(.copy)").forEach((e) => {
                e.target = "_blank";
            });
        });

        // Modal
        $(function() {
            // Show modal on image click
            $('.lightbox').on('click', function() {
                $('#previewImage').attr('src', $(this).attr('src'));
                $('#previewModalLabel').text($(this).attr('alt'));
                $('#previewModal').modal('show');
            });
            // Hide modal if clicked anywhere
            $('#previewModal').on('click', function() {
                $(this).modal('hide');
            });
        });
    </script>

<?php require_once("../include/footer.php"); ?>