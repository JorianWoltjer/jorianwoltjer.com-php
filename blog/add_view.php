<?php require_once("../include/all.php");

// If this gets abused, add rate limiting on IP basis (per post)

// Increment view count if not a bot user agent and post is visible
if (!preg_match('/bot\W/i', $_SERVER["HTTP_USER_AGENT"])) {
    sql_query("UPDATE posts SET views = views+1 WHERE id = ? AND hidden IS NULL", [$_GET["id"]]);
}
