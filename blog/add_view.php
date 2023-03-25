<?php require_once("../include/all.php");

if (preg_match('/bot\W/i', $_SERVER["HTTP_USER_AGENT"])) {
    die("Bots cannot add views");
}
if (!isset($_GET["id"]) || sql_query("SELECT id FROM posts WHERE id = ? AND hidden IS NULL", [$_GET["id"]])->num_rows == 0) {
    die("Invalid post ID");
}

// I don't even want the possibility of recovering your IP address
// Trying to crack this won't be fun
$unique = $_SERVER["REMOTE_ADDR"] . date("Y-m-d H:00:00");  // Should be unique per user, per hour, but unspoofable
$unique_hash = hash_pbkdf2("sha256", $unique, "salt_4e7be08c", 10000, 32, true);

$count_response = sql_query("SELECT count(id) FROM views WHERE post_id = ? AND unique_hash = ?", [$_GET["id"], $unique_hash]);

if ($count_response->fetch_row()[0] >= 5) {
    http_response_code(429);
    die("Already viewed 5 times this hour");
}
sql_query("INSERT INTO views (post_id, unique_hash) VALUES (?, ?)", [$_GET["id"], $unique_hash]);
// views table is reset every hour by cron.php, so we need to remember the count only
sql_query("UPDATE posts SET views = views+1 WHERE id = ?", [$_GET["id"]]);
