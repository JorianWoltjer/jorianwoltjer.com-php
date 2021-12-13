<?php require_once("../include/header.php");

if (!$admin) { // Admin only
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if (!isset($_POST['title'], $_POST['description'], $_POST['image'], $_POST['folder'], $_POST['tags'],
    $_POST['text'], $_POST['points'])) {
    returnMessage("all_fields", "/blog/");
}
?>

    <link rel="stylesheet" href="/css/vs2015.css">
    <title>Blog - <?= htmlspecialchars($_POST['title']) ?></title>

<?php
$response_breadcrumbs = sql_query("SELECT T2.id, T2.name 
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
                                    ORDER BY T1.lvl DESC", [$_POST['folder']]);
?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href="/blog"><code>Blog</code></a></li>
            <?php
            while ($row_bc = $response_breadcrumbs->fetch_assoc()) {
                echo "<li class='breadcrumb-item'><a href='folder?id=$row_bc[id]'><code>$row_bc[name]</code></a></li>";
            } ?>
            <li class='breadcrumb-item active' aria-current='page'><h1><code><?= $_POST['title'] ?></code></h1></li>
        </ol>
    </nav>

    <p class="tags">
        <?php
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
        ?>
        <?= $_POST['points'] ? '+'.$_POST['points'].' points' : '' ?>
    </p>

    <div class="text-muted">
        0 seconds ago - <i class="far fa-eye"></i> <?= (isset($_POST["hidden"]) && $_POST["hidden"] === "on") ? "<b>Hidden</b>" : "0 views" ?>
    </div>

    <h1><?= $_POST['title'] ?></h1>
    <div class='blog-content'>
        <?= md_to_html($_POST['text']) ?>
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

    <script src="/highlight/highlight.min.js"></script>
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