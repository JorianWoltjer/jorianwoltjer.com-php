<?php require_once("../include/header.php");

if (isset($_GET["url"])) {
    $response = sql_query("SELECT * FROM posts WHERE url=?", [$_GET["url"]]);
} else if (isset($_GET["id"])) {
    $response = sql_query("SELECT * FROM posts WHERE id=?", [$_GET["id"]]);
} else {
    returnMessage("error_post", "/blog/");
}

$row = $response->fetch_assoc();

if ($response->num_rows === 0) {
    returnMessage("error_post", "/blog/");
}

// Verify hidden hash
if ($row["hash"] !== NULL) {
    if (!isset($_GET["hash"]) || $_GET["hash"] !== bin2hex($row["hash"])) {
        returnMessage("error_post", "/blog/");
    }
}

sql_query("UPDATE posts SET views = views+1 WHERE id = ?", [$row["id"]])
?>

    <link rel="stylesheet" href="/css/vs2015.css">
    <title>Blog - <?= htmlspecialchars($row['title']) ?></title>
    <meta name="og:type" content="article" />
    <meta name="description" content="<?= htmlspecialchars($row['description']) ?>">
    <meta name="og:description" content="<?= htmlspecialchars($row['description']) ?>" />
    <meta name="og:title" content="Blog - <?= htmlspecialchars($row['title']) ?>" />
    <meta name="og:image" content="<?= get_baseurl() ?>/img/blog/<?= $row['img'] ?>" />
    <meta name="og:site_name" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>">
    <meta property="og:article:section" content="1" />
    <meta property="og:article:author" content="Jorian Woltjer" />
    <meta name="twitter:card" content="summary_large_image">

<?php
$response_breadcrumbs = sql_query("SELECT T2.url, T2.name 
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
                echo "<li class='breadcrumb-item'><a href='/blog/folder/$row_bc[url]'><code>$row_bc[name]</code></a></li>";
            } ?>
            <li class='breadcrumb-item active' aria-current='page'><h1><code><?= $row['title'] ?></code></h1></li>
        </ol>
    </nav>

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
        <?= time_to_ago($row['timestamp']) ?> - <i class="far fa-eye"></i> <?= $row["hash"] === NULL ? $row["views"]." views" : "<b>Hidden</b>" ?>
    </div>

<?php if ($admin) { ?>
    <a href="/blog/edit_post?id=<?= $row['id'] ?>" class="folder"><i class="fas fa-edit"></i>Edit post</a>
    <br>
    <br>
<?php } ?>

    <h1><?= $row['title'] ?></h1>
    <div class='blog-content'>
        <?= $row['html'] ?>
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

    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.7.2/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    <script>
        // Copy button from code blocks
        function copy_code(element) {
            const code = element.parentElement.parentElement.getElementsByTagName("code")[0].innerText;
            navigator.clipboard.writeText(code);
            var tooltip = new bootstrap.Tooltip(element);
            tooltip.show();
            setTimeout(function() {
                tooltip.dispose();
            }, 2000);
        }

        // Open all links in new tab
        window.addEventListener('DOMContentLoaded', (event) => {
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