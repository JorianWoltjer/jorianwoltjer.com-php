<?php require_once("../include/all.php");
if (!isset($preview)) $preview = false;

if (!$preview) {  // If from SQL
    if (isset($_GET["url"])) {
        $response = sql_query("SELECT * FROM posts WHERE url=?", [$_GET["url"]]);
    } else if (isset($_GET["id"])) {  # id for backwards compatibility
        $response = sql_query("SELECT * FROM posts WHERE id=?", [$_GET["id"]]);
    } else {
        returnMessage("error_post", "/blog/");
    }

    if ($response->num_rows === 0) {
        returnMessage("error_post", "/blog/");
    }

    $row = $response->fetch_assoc();

    // Verify hidden hash
    if ($row["hidden"] !== NULL) {
        $hash = $_GET["hidden"] ?? $_GET["hash"] ?? "";  // Get hidden parameter or hash for backwards compatibility
        if ($hash !== bin2hex($row["hidden"])) {
            returnMessage("error_post", "/blog/");
        }
    }

    sql_query("UPDATE posts SET views = views+1 WHERE id = ?", [$row["id"]]);
} else {  // If preview from POST
    if (!isset($_POST['title'], $_POST['description'], $_POST['img'], $_POST['parent'], $_POST['tags'],
        $_POST['text'], $_POST['points'])) {
        returnMessage("all_fields", "/blog/");
    }

    $row = $_POST;  // Take values from POST if preview
    $row["hidden"] = (isset($_POST["hidden"]) && $_POST["hidden"] === "on") ? true : null;
    $row['timestamp'] = "0 sec";
    $row['views'] = 0;
    $row["html"] = md_to_html($row["text"]);
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
                                    ORDER BY T1.lvl DESC", [$row['parent']]);

// Title: folder + title + 'writeup' if ctf
$all_breadcrumbs = $response_breadcrumbs->fetch_all(MYSQLI_ASSOC);
$folder = $all_breadcrumbs[$response_breadcrumbs->num_rows-1];
$meta_title = $folder['title']." - ".$row['title'] . (str_starts_with($folder['url'], "ctf") ? ' (Writeup)' : '');
$meta_description = $row['description'];
$meta_image = "/img/blog/".$row['img'];
$meta_large_card = true;

require_once("../include/header.php");
?>

    <link rel="stylesheet" href="/assets/highlight/github-dark.min.css">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href="/blog"><code>Blog</code></a></li>
            <?php
            $response_breadcrumbs->data_seek(0);  // Reset pointer
            while ($row_bc = $response_breadcrumbs->fetch_assoc()) {
                echo "<li class='breadcrumb-item'><a href='/blog/folder/$row_bc[url]'><code>$row_bc[title]</code></a></li>";
            }
            ?>
            <li class='breadcrumb-item active' aria-current='page'><h1><code><?= $row['title'] ?></code></h1></li>
        </ol>
    </nav>
    <br>

    <p class="tags">
        <?php
        if ($preview) {
            $all_tags = sql_query("SELECT name, class FROM tags")->fetch_all();

            $tag_to_class = array();
            foreach ($all_tags as $tag) {
                $tag_to_class[$tag[0]] = $tag[1];
            }

            foreach ($_POST["tags"] as $tag) {
                if (isset($tag_to_class[$tag])) {
                    echo "<span class='tag tag-$tag_to_class[$tag]'>$tag</span>";
                }
            }
        } else {
            $tags = sql_query("SELECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row["id"]]);

            while ($row_tag = $tags->fetch_assoc()) {
                echo "<span class='tag tag-$row_tag[class]'>$row_tag[name]</span>";
            }
        }
        ?>
        <?= $row['points'] ? '+'.$row['points'].' points' : '' ?>
    </p>

    <div class="text-muted">
        <?= time_to_ago($row['timestamp']) ?> - <i class="far fa-eye"></i> <?= $row["hidden"] === null ? $row["views"]." views" : "<b>Hidden</b>" ?>
    </div>

<?php if (!$preview && isset($admin) && $admin) { ?>
    <a href="/blog/edit_post?id=<?= $row['id'] ?>" class="folder"><i class="fa-solid fa-edit"></i>Edit post</a>
    <br>
    <br>
<?php } ?>

    <h1><?= $row['title'] ?></h1>
    <div class='blog-content'>
        <?= $row['html'] ?>
    </div>

<?php
if (!$preview) {
    $response_prev = sql_query("SELECT url FROM posts WHERE id = (SELECT max(id) FROM posts WHERE id < ?) AND parent=? AND hidden IS NULL;",
        [$row["id"], $row["parent"]]);
    $prev = $response_prev->fetch_assoc();

    $response_next = sql_query("SELECT url FROM posts WHERE id = (SELECT min(id) FROM posts WHERE id > ?) AND parent=? AND hidden IS NULL;",
        [$row["id"], $row["parent"]]);
    $next = $response_next->fetch_assoc();
}
?>
    <div class="pagination">
        <div class="left">
            <?php
            if (isset($prev) && $prev) {
                echo '<a href="/blog/post/'.$prev["url"].'"><i class="fa-solid fa-caret-left"></i> Previous</a>';
            }
            ?>
        </div>
        <div class="center">
            <div class="text-white-50">
                <p>The end! If you have any questions feel free to ask me anywhere on my <a href="/contact" target="_blank" class="white-link">Contacts</a></p>
            </div>
        </div>
        <div class="right">
            <?php
            if (isset($next) && $next) {
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
                    <img src="" id="previewImage" alt="Unable to load image!">
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/highlight/highlight.min.js"></script>
    <script nonce="<?=$nonce?>">
        hljs.highlightAll();

        // Copy button from code blocks
        function copy_code(element, tooltip) {
            const code = element.parentElement.parentElement.getElementsByTagName("code")[0].innerText;
            navigator.clipboard.writeText(code);

            tooltip.show();
            setTimeout(function() {
                tooltip.hide();
            }, 1000);
        }
        // Add copy_code function to all code blocks
        $(".copy").each(function() {
            var tooltip = new bootstrap.Tooltip(this, {
                title: "Copied!",
                placement: "top",
                trigger: "manual"
            });

            $(this).on("click", function() {
                copy_code(this, tooltip);
            });
        });

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