<?php
$admin_required = true;
$title = "Create folder";
$description = "Form to create a folder for posts on my blog.";
require_once("../include/header.php");
?>

    <h1 class="my-4"><code>Create folder</code></h1>

    <form method="POST" id="form">
        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['title'], $_POST['description'], $_POST['image'], $_POST['parent'])) {

                $url = text_to_url($_POST["title"]);
                $parent = sql_query("SELECT url FROM folders WHERE id=?", [$_POST["parent"]]);
                $parent_url = $parent->fetch_assoc()["url"];
                $url = $parent_url."/".$url;

                sql_query("INSERT INTO folders(parent, url, title, description, img, timestamp) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP())",
                    [$_POST['parent'], $url, $_POST['title'], $_POST['description'], $_POST['image']]);

                header("Location: /blog/folder/".$url);
            }
        }
        ?>
        <label for="title">Title</label>
        <input class="form-control" id="title" type="text" name="title" required autocomplete="off" autofocus>
        <br>
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" spellcheck="true" rows="2" required></textarea>
        <br>
        <label for="image">Image</label>
        <input class="form-control" id="image" type="text" name="image" value="../placeholder.png" required autocomplete="off">
        <br>
        <img id="preview" src="" alt="Unable to load image!" class="rounded" width="300px">
        <br>
        <br>
        <label for="parent">Parent folder</label>
        <select class="form-control" id="parent" name="parent">
            <?php
            $response = sql_query("SELECT id, title FROM folders");

            while($row = $response->fetch_assoc()) {
                echo "<option value='$row[id]'>$row[title]</option>";
            }
            ?>
        </select>
        <br>
        <input class="btn btn-primary" type="submit" name="submit" value="Create">
    </form>

    <script>
        $('#image').on("change", function() {
            const src = $(this).val();
            $("#preview").attr('src', "/img/blog/"+src);
        }).change();
    </script>

<?php require_once("../include/footer.php"); ?>