<?php
if (!isset($create_post)) $create_post = false;

$admin_required = true;
$title = ($create_post ? "Create" : "Edit")." post";
$description = "Form to ".($create_post ? "Create" : "Edit")." a post on my blog.";
require_once("../include/all.php");

if (!$create_post) {
    $response = sql_query("SELECT * FROM posts WHERE id = ?", [$_GET['id']]);
    $row = $response->fetch_assoc();

    if ($response->num_rows === 0) {
        returnMessage("error_post", "/blog/");
    }
} else {
    $row = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {  # On submit
    if (isset($_POST['title'], $_POST['description'], $_POST['image'], $_POST['folder'], $_POST['tags'],
        $_POST['text'], $_POST['points'])) {

        $html = md_to_html($_POST["text"]);
        $featured = isset($_POST["featured"]) && $_POST["featured"] === "on";
        $hidden = isset($_POST["hidden"]) && $_POST["hidden"] === "on";

        if ($featured and $hidden) {
            returnMessage("error_featured_and_hidden", "");
        }

        $url = text_to_url($_POST["title"]);
        $parent = sql_query("SELECT url FROM folders WHERE id=?", [$_POST["folder"]]);
        $parent_url = $parent->fetch_assoc()["url"];
        $url = $parent_url."/".$url;

        if ($hidden) {
            $hash = $row["hidden"] ?? random_bytes(32);
            if ($create_post) {
                sql_query("INSERT INTO posts(parent, url, title, description, img, markdown, html, points, featured, hidden, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())",
                    [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                        $html, $_POST["points"], $featured, $hash]);
            } else {
                sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=? WHERE id=?",
                    [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                        $html, $_POST["points"], $featured, $hash, $row['id']]);
            }
        } else if (!$create_post && $row["hidden"]) {  // If changed from hidden to public
            sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=NULL, timestamp=CURRENT_TIMESTAMP() WHERE id=?",
                [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                    $html, $_POST["points"], $featured, $row['id']]);
        } else {
            if ($create_post) {
                sql_query("INSERT INTO posts(parent, url, title, description, img, markdown, html, points, featured, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())",
                    [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                        $html, $_POST["points"], $featured]);
            } else {
                sql_query("UPDATE posts SET parent=?, url=?, title=?, description=?, img=?, markdown=?, html=?, points=?, featured=?, hidden=NULL WHERE id=?",
                    [$_POST["folder"], $url, $_POST["title"], $_POST["description"], $_POST["image"], $_POST["text"],
                        $html, $_POST["points"], $featured, $row['id']]);
            }
        }

        // Save id of post for later
        $post_id = $row['id'] ?? $dbc->insert_id;

        // Convert tags to corresponding ids
        $all_tags = sql_query("SELECT id, name FROM tags")->fetch_all();

        $tag_to_id = array();
        foreach ($all_tags as $tag) {
            $tag_to_id[$tag[1]] = $tag[0];
        }

        if (!$create_post) {  // Delete previous tags
            sql_query("DELETE FROM post_tags WHERE post=?", [$row['id']]);
        }
        // Create tag entries in database
        $stmt = $dbc->prepare("INSERT INTO post_tags(post, tag) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $tag_id);

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
require_once("../include/header.php");
?>

    <h1 class="my-4"><code><?= $create_post ? "Create" : "Edit" ?> post</code></h1>

    <?php displayMessage() ?>

    <form method="POST" id="form">
        <label for="title">Title</label>
        <input class="form-control" id="title" type="text" name="title" required autocomplete="off" autofocus value="<?= $row["title"] ?? "" ?>">
        <br>
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" spellcheck="true" rows="2" required><?= $row["description"] ?? "" ?></textarea>
        <br>
        <label for="image">Image</label>
        <input class="form-control" id="image" type="text" name="image" required autocomplete="off" value="<?= $row["img"] ?? "../placeholder.png" ?>">
        <br>
        <img id="preview" src="" alt="Unable to load image!" class="rounded" width="300px">
        <br>
        <br>
        <label for="folder">Folder</label>
        <select class="form-control" id="folder" name="folder">
            <?php
            $response = sql_query("SELECT id, title FROM folders");

            while($row_folder = $response->fetch_assoc()) {
                if ((isset($row['parent']) && $row_folder['id'] === $row['parent']) || (isset($_GET["parent"]) && $row_folder["id"] == $_GET["parent"])) {
                    echo "<option value='$row_folder[id]' selected>$row_folder[title]</option>";
                } else {
                    echo "<option value='$row_folder[id]'>$row_folder[title]</option>";
                }
            }
            ?>
        </select>
        <br>
        <p class="tags" id="tags">
            <label for="tag-add" style="margin-right: 10px;">Tags:</label>
            <?php
            if (!$create_post) {
                $tags = sql_query("SELECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$_GET['id']]);

                $post_tags = array();
                foreach ($tags->fetch_all() as $tag) {
                    $post_tags[] = $tag[0];
                    echo '<span class="tag selected-tag tag-' . $tag[1] . '">' . $tag[0] . '<i class="fa-solid fa-times-circle tag-delete" onclick="delete_tag(this.parentElement)"></i></span>';
                }
            }
            ?>
            <input class="tag tag-add" id="tag-add" list="tags-list" placeholder="+ Add" oninput="add_tag(this)" onclick="this.value = ''" autocomplete="off">
            <datalist id="tags-list">
                <?php
                $response = sql_query("SELECT name, class FROM tags");

                while($row_tag = $response->fetch_assoc()) {
                    if ($create_post || !in_array($row_tag['name'], $post_tags)) {
                        echo "<option value='$row_tag[name]'>";
                    }
                }
                ?>
            </datalist>
        </p>
        <input type="hidden" name="tags[]">
        <div id="tag-inputs"></div>
        <label for="text">Text (Markdown)</label>
        <pre><textarea class="form-control" id="text" name="text" spellcheck="true" rows="10" required><?= $row["markdown"] ?? "" ?></textarea></pre>
        <br>
        <label for="points">Points (optional)</label>
        <input class="form-control" id="points" type="number" name="points" autocomplete="off" value="<?= $row["points"] ?? "" ?>">
        <br>
        <div class="form-check">
            <input class="form-check-input" id="featured" type="checkbox" name="featured"<?= ($row["featured"] ?? "") ? " checked" : "" ?>>
            <label class="form-check-label" for="featured">Featured</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" id="hidden" type="checkbox" name="hidden"<?= ($row["hidden"] ?? null) !== null ? " checked" : "" ?>>
            <label class="form-check-label" for="hidden">Hidden</label>
        </div>
        <br>
        <br>
        <input class="btn btn-primary" type="submit" name="submit" value="Save" onclick="this.form.button=this; this.form.target=''">
        <input class="btn btn-secondary" type="submit" name="submit" value="Preview" onclick="this.form.button=this; this.form.target='_blank'" formaction="preview">
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
            const option_element = document.querySelector("#tags-list option[value='"+CSS.escape(value)+"']");
            const existing_tag = Array.from(document.querySelectorAll('span.tag')).some(el => el.textContent ===value);
            // If not in datalist or already added
            if (!option_element || existing_tag) {
                return true;
            }
            option_element.remove();

            const tag = document.createElement("span");
            tag.className = "tag selected-tag tag-"+tag_class[value];
            tag.innerText = element.value

            const delete_button = document.createElement("i")
            delete_button.className = "fa-solid fa-times-circle tag-delete"
            delete_button.onclick = function() { delete_tag(tag) };

            tag.appendChild(delete_button)
            document.getElementById("tags").insertBefore(tag, document.getElementById("tag-add"))
            element.value = ""
        }

        function delete_tag(element) {
            const option_element = document.createElement("option");
            option_element.value = element.textContent;
            document.getElementById("tags-list").appendChild(option_element);
            element.parentNode.removeChild(element);
        }

        document.getElementById("form").onsubmit = function () {
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

        $('input').on('keydown', function (e) {
            if (e.key === 'Enter') {  // Don't submit on Enter
                return false;
            }
        });

        $('#image').on("change", function() {
            const src = $(this).val();
            $("#preview").attr('src', "/img/blog/"+src);
        }).change();

        document.getElementById('text').addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;

                // set textarea value to: text before caret + tab + text after caret
                this.value = this.value.substring(0, start) +
                    "    " + this.value.substring(end);

                // put caret at right position again
                this.selectionStart =
                    this.selectionEnd = start + 4;
            }
        });

        document.getElementById('text').addEventListener('paste', (event) => {
            event.preventDefault();
            let paste = (event.clipboardData || window.clipboardData).getData('text');

            // Take title from paste
            const titleRegex = /^# (.*)(\r?\n)+/
            const match = titleRegex.exec(paste)
            if (match) {
                document.getElementById("title").value = match[1];
                paste = paste.replace(titleRegex, "");
            }

            document.execCommand("insertText", false, paste)
        })
    </script>

<?php require_once("../include/footer.php"); ?>