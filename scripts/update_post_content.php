<?php $admin_required = true;

require_once("../include/all.php");

$response = sql_query("SELECT * FROM posts");

// Update Markdown to HTML for all posts
$count = 0;
while ($row = $response->fetch_assoc()) {
    $html = md_to_html($row['markdown']);

    if ($html !== $row['html']) {
        sql_query("UPDATE posts SET html=? WHERE id=?", [$html, $row['id']]);
        $count++;
    }
}

echo "Updated $count posts";
