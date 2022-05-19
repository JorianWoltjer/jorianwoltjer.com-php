<?php
$title = "Blog - Search";
$description = "Search through all posts on my blog about cybersecurity. Quickly find what you're looking for by typing in the search bar.";
require_once("../include/header.php");

function text_only($html): array|string|null
{
    $text = preg_replace('/<[^>]*>/', ' ', $html);
    return preg_replace('/\s\s+|\n/', ' ', $text);
}
?>

<h1 class="my-4"><code>Search posts</code></h1>

<noscript><div class="alert alert-danger" role="alert">
    Warning: JavaScript is required for this page to work!
</div></noscript>

<input id="query" type="text" class="form-control" placeholder="Search" oninput="search(this.value)" onblur="saveURL()" autocomplete="off" autofocus>

<div id="results">
<?php
$response = sql_query("SELECT * FROM posts WHERE hidden IS NULL ORDER BY timestamp DESC");

if ($response->num_rows > 0) {
    while ($row = $response->fetch_assoc()) { ?>
        <div class="card card-horizontal">
            <div class="row no-gutters">
                <div class="col-sm-2" style="padding: 0;">
                    <a href="/blog/post/<?= $row['url'] ?>">
                        <img src="/img/blog/<?= $row['img'] ?>" class="card-img-top h-100" style="object-fit: cover;" alt="Post thumbnail">
                    </a>
                </div>
                <div class="col-sm-9" style="display: flex; flex-direction: column;">
                    <div class="card-body">
                        <div id="post-content-search" class="hidden"><?= text_only($row["html"]) ?></div>
                        <p class="card-text tags">
                            <?php
                            $tags = sql_query("SElECT t.name, t.class FROM post_tags pt JOIN tags t on pt.tag = t.id WHERE pt.post = ?", [$row['id']]);

                            while ($tag_row = $tags->fetch_assoc()) {
                                echo "<span class='tag tag-$tag_row[class]'>$tag_row[name]</span>";
                            }
                            ?>
                            <?= $row['points'] ? '+'.$row['points'].' points' : '' ?>
                        </p>
                        <h3 class="card-title">
                            <a href="/blog/post/<?= $row['url'] ?>"><code><?= $row['title'] ?></code></a>
                        </h3>
                        <p class="card-text hidden" id="post-content-preview"><a href="" class="hover-only-link"></a></p>
                        <p class="card-text" id="post-description"><?= $row['description'] ?></p>
                    </div>
                </div>
            </div>
        </div>
<?php }
}?>
</div>
<div id="no-posts" class="hidden">
    <br>
    <p class='lead'>No posts found.</p>
</div>

<script>
    function reduceQuery(query) {
        /* Reduce query to only useful words
         * - Remove words with only 1 character
         * - Remove duplicates
         * - Remove starting substrings of other words (eg. "te" and "test")
         */
        return query.filter((word, i) => {
            const longEnough = word.length > 1;
            const mostSpecific = !query.some(other => other.startsWith(word) && other !== word);
            const firstOccurrence = !query.some(other => other === word && query.indexOf(other) < i);
            return longEnough && mostSpecific && firstOccurrence;
        });
    }

    function stripTags(str) {
        return str.replace( /(<[^>]+)>/ig, '');
    }

    function hide(element) {
        element.classList.add("hidden");
    }
    function show(element) {
        element.classList.remove("hidden");
    }

    function search(query) {
        let results = document.getElementById("results");
        query = reduceQuery(query.split(" "));

        // Remove all previous highlights
        let highlights = results.getElementsByClassName("highlight");
        while (highlights.length > 0) {
            highlights[0].outerHTML = highlights[0].innerHTML;
        }

        // Add new highlights
        if (query.length > 0) {
            for (let i = 0; i < results.children.length; i++) {
                let post = results.children[i];

                // Set innerHTML to new HTML with highlights
                [post.innerHTML, unmatched] = addHighlights(post.innerHTML, query);

                if (unmatched > 0) {  // Hide post if there are any unmatched words
                    post.classList.add("hidden");
                } else {
                    post.classList.remove("hidden");
                }
            }
        } else {
            // If no query show all posts
            for (let i = 0; i < results.children.length; i++) {
                results.children[i].classList.remove("hidden");
            }
        }

        // Replace description with preview if highlighted
        for (let i = 0; i < results.children.length; i++) {
            let post = results.children[i];
            // Find highlights in post content
            let previewHighlight = post.querySelector(".card:not(.hidden) #post-content-search>span.highlight");
            if (previewHighlight) {
                // Show preview
                const previewIndex = post.querySelector("#post-content-search").innerHTML.indexOf(previewHighlight.outerHTML);
                const preview = post.querySelector("#post-content-search").innerHTML.substring(previewIndex, previewIndex + 200);
                // Get first word from preview
                const previewWord = stripTags(preview).match(/\w+/)[0];
                const href = post.querySelector("h3>a").href;

                post.querySelector("#post-content-preview>a").innerHTML = "…" + preview + "…";
                post.querySelector("#post-content-preview>a").href = href + "#:~:text=" + previewWord;
                show(post.querySelector("#post-content-preview"));
                hide(post.querySelector("#post-description"));
            } else {
                // Show description
                hide(post.querySelector("#post-content-preview"));
                show(post.querySelector("#post-description"));
            }
        }

        if (!results.querySelector(".card:not(.hidden)")) {  // Show no-posts message if all posts are hidden
            show(document.getElementById("no-posts"));
        } else {
            hide(document.getElementById("no-posts"));
        }
    }

    function addHighlights(text, words) {
        const maxWordLength = Math.max(...words.map(word => word.length));  // Find length of the longest word
        let foundWords = new Array(words.length).fill(false);
        let inHTML = false;
        for (let i = 0; i < text.length; i++) {
            if (text[i] === ">") {  // If end of HTML tag
                inHTML = false;
                continue
            } else if (inHTML || text[i] === "<") {  // If inside HTML tag
                inHTML = true;
                continue
            } else if (text[i] === " " || text[i] === "\n") {  // If special character
                continue
            }
            const substring = text.substring(i, i+maxWordLength)
            for (let j = 0; j < words.length; j++) {  // Go through all words
                if (substring.toLowerCase().startsWith(words[j].toLowerCase())) {  // If word matches (case-insensitive)
                    const highlighted = `<span class="highlight">${text.substring(i, i+words[j].length)}</span>`
                    text = text.substring(0, i) + highlighted + text.substring(i+words[j].length);
                    i += highlighted.length;  // Skip next few characters because word already found
                    foundWords[j] = true;  // Save index of word that was found
                    break;
                }
            }
            // Skip to next word if none found
            while (text[i] !== " " && text[i] !== "\n" && text[i] !== "<" && text[i] !== '"' && text[i] !== "'") {
                i++;
            }
        }
        const unmatched = foundWords.filter(word => word === false).length;  // Get words that were not found
        return [text, unmatched];
    }

    function saveURL() {
        const query = document.getElementById("query").value;
        if (query.length > 0) {
            history.replaceState({}, "", `?q=${query}`);  // Save search in URL
        } else {
            history.replaceState(null, null, "/blog/search");  // Clear URL bar
        }
    }

    // Remove ::target-text on click
    document.body.addEventListener('click', () => {
        document.documentElement.style.setProperty("--target-text-color", "");
    }, true);

    // Put search query from URL into search bar
    const params = new URLSearchParams(window.location.search);
    document.getElementById("query").value = params.get("q");
    search(document.getElementById("query").value);
</script>

<?php require_once("../include/footer.php"); ?>

