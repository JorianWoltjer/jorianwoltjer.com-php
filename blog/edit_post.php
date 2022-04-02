<?php
$title = "Edit post";
$description = "Form to edit a post on my blog.";
require_once("../include/header.php");

if (!$admin) { // Admin only
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$response = sql_query("SELECT * FROM posts WHERE id = ?", [$_GET['id']]);
$row = $response->fetch_assoc();

if ($response->num_rows === 0) {
    returnMessage("error_post", "/blog/");
}
?>

    <h1 class="my-4"><code>Edit post</code></h1>

    <form method="POST" id="form">
        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['title'], $_POST['description'], $_POST['image'], $_POST['folder'], $_POST['tags'],
                $_POST['text'], $_POST['points'])) {

                $html = md_to_html($_POST["text"]);
                $featured = isset($_POST["featured"]) && $_POST["featured"] === "on";
                $hidden = isset($_POST["hidden"]) && $_POST["hidden"] === "on";
                $url = text_to_url($_POST["title"]);

                $parent = sql_query("SELECT url FROM folders WHERE id=?", [$_POST["folder"]]);
                $parent_url = $parent->fetch_assoc()["url"];
                $url = $parent_url."/".$url;

                if ($hidden) {
                    $hash = $row["hidden"] ?? random_bytes(32);
                    sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=? WHERE id=?",
                        [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                            $html, $_POST["points"], $featured, $hash, $row['id']]);
                } else if ($row["hidden"]) {  // If changed from hidden to public
                    sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=NULL, timestamp=CURRENT_TIMESTAMP() WHERE id=?",
                        [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                            $html, $_POST["points"], $featured, $row['id']]);
                } else {
                    sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=NULL WHERE id=?",
                        [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                            $html, $_POST["points"], $featured, $row['id']]);
                }

                // Convert tags to corresponding ids
                $all_tags = sql_query("SELECT id, name FROM tags")->fetch_all();

                $tag_to_id = array();
                foreach ($all_tags as $tag) {
                    $tag_to_id[$tag[1]] = $tag[0];
                }

                // Create tag entries in database
                sql_query("DELETE FROM post_tags WHERE post=?", [$row['id']]);
                $stmt = $dbc->prepare("INSERT INTO post_tags(post, tag) VALUES (?, ?)");
                $stmt->bind_param("ii", $row['id'], $tag_id);

                foreach ($_POST["tags"] as $tag) {
                    if (array_key_exists($tag, $tag_to_id)) {
                        $tag_id = $tag_to_id[$tag];
                        $stmt->execute();
                    }
                }
                $stmt->close();

                // Redirect to new post
                if ($hidden) {
                    header("Location: /blog/post/" . $url . "?hidden=" . bin2hex($hash));
                } else {
                    header("Location: /blog/post/" . $url);
                }
                exit();
            }
        }
        ?>
        <label for="title">Title</label>
        <input class="form-control" id="title" type="text" name="title" required autocomplete="off" autofocus value="<?= $row["title"] ?>">
        <br>
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" spellcheck="true" rows="2" required><?= $row["description"] ?></textarea>
        <br>
        <label for="image">Image</label>
        <input class="form-control" id="image" type="text" name="image" required autocomplete="off" value="<?= $row["img"] ?>">
        <br>
        <img id="preview" src="" alt="Unable to load image!" class="rounded" width="300px">
        <br>
        <br>
        <label for="folder">Folder</label>
        <select class="form-control" id="folder" name="folder">
            <?php
            $response = sql_query("SELECT id, name FROM folders");

            while($row_folder = $response->fetch_assoc()) {
                if ($row_folder['id'] === $row['parent']) {
                    echo "<option value='$row_folder[id]' selected>$row_folder[name]</option>";
                } else {
                    echo "<option value='$row_folder[id]'>$row_folder[name]</option>";
                }
            }
            ?>
        </select>
        <br>
        <p class="tags" id="tags">
            <span id="tags-tabel" style="margin-right: 10px;">Tags:</span>
            <?php
            $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$_GET['id']]);

            foreach ($tags->fetch_all() as $tag) {
                echo '<span class="tag selected-tag tag-'.$tag[1].'">'.$tag[0].'<i class="fas fa-times-circle tag-delete" onclick="delete_tag(this.parentElement)"></i></span>';
            }
            ?>
            <input class="tag tag-add" id="tag-add" list="tags-list" placeholder="+ Add" oninput="add_tag(this)" onclick="this.value = ''" autocomplete="off">
            <datalist id="tags-list">
                <?php
                $response = sql_query("SELECT name, class FROM tags");

                while($row_tag = $response->fetch_assoc()) {
                    echo "<option value='$row_tag[name]'>";
                }
                ?>
            </datalist>
        </p>
        <input type="hidden" name="tags[]">
        <div id="tag-inputs"></div>
        <label for="text">Text (Markdown)</label>
        <pre><textarea class="form-control" id="text" name="text" spellcheck="true" rows="10" required><?= $row["markdown"] ?></textarea></pre>
        <br>
        <label for="points">Points (optional)</label>
        <input class="form-control" id="points" type="number" name="points" autocomplete="off" value="<?= $row["points"] ?>">
        <br>
        <div class="form-check">
            <input class="form-check-input" id="featured" type="checkbox" name="featured"<?= $row["featured"] ? " checked" : "" ?>>
            <label class="form-check-label" for="featured">Featured</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" id="hidden" type="checkbox" name="hidden"<?= $row["hidden"] !== NULL ? " checked" : "" ?>>
            <label class="form-check-label" for="hidden">Hidden</label>
        </div>
        <br>
        <br>
        <input class="btn btn-light" type="submit" name="submit" id="submit_post" value="Save" onclick="this.form.button=this; this.form.target=''">
        <input class="btn btn-secondary" type="submit" name="submit" id="submit_post" value="Preview" onclick="this.form.button=this; this.form.target='_blank'" formaction="preview">
    </form>

    <script>
        tag_class = {
            <?php
            mysqli_data_seek($response, 0);  // Move pointer back

            while($row_tag = $response->fetch_assoc()) {
                echo "\"$row_tag[name]\": \"$row_tag[class]\",\n";
            }
            ?>
        }

        function add_tag(element) {
            const value = element.value;
            // If not in datalist or already added
            if (!document.querySelector("#tags-list option[value='"+CSS.escape(value)+"']") || document.getElementsByClassName("tag-"+tag_class[value]).length) {
                return true;
            }

            const tag = document.createElement("span");
            tag.className = "tag selected-tag tag-"+tag_class[value];
            tag.innerText = element.value

            const delete_button = document.createElement("i")
            delete_button.className = "fas fa-times-circle tag-delete"
            delete_button.onclick = function() { delete_tag(tag) };

            tag.appendChild(delete_button)
            document.getElementById("tags").insertBefore(tag, document.getElementById("tag-add"))
            element.value = ""
        }

        function delete_tag(element) {
            const tag = element
            tag.parentNode.removeChild(tag);
        }

        document.getElementById("form").onsubmit = function (e) {
            document.getElementById("tag-inputs").innerHTML = ""
            const tags = document.getElementsByClassName("selected-tag");

            for (let tag of tags) {
                const input = document.createElement("input");
                input.type = "hidden"
                input.name = "tags[]"
                input.value = tag.innerText

                document.getElementById("tag-inputs").appendChild(input)
            }

            return true;
        }

        $(document).ready(function () {
            $('input').on('keydown', function (e) {
                if (e.keyCode === 13) {
                    e.keyCode = 9;
                    return false;
                }
            });
        });

        $('#image').change(function() {
            const src = $(this).val();
            $("#preview").attr('src', "/img/blog/"+src);
        }).change();

        document.getElementById('text').addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;

                // set textarea value to: text before caret + tab + text after caret
                this.value = this.value.substring(0, start) +
                    "    " + this.value.substring(end);

                // put caret at right position again
                this.selectionStart =
                    this.selectionEnd = start + 4;
            }
        });
    </script>

<?php require_once("../include/footer.php"); ?>