<?php require_once ('../include/all.php');

header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="utf-8"?>';  // `echo` to escape '<?'
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
<title>Blog | Jorian Woltjer</title>
<link><?= get_baseurl() ?>/blog</link>
<description>A blog with cybersecurity-related articles. Writeups of challenges in Capture The Flag (CTF) events, stories about hacking and guides with code examples and detailed explanations.</description>
<image>
    <title>Blog | Jorian Woltjer</title>
    <url><?= get_baseurl() ?>/img/round_logo_small.png</url>
    <link><?= get_baseurl() ?>/blog</link>
</image>
<?php
    $response = sql_query("SELECT id, title, description, url, img, timestamp FROM posts WHERE hidden IS NULL ORDER BY timestamp DESC");

    while ($row = $response->fetch_assoc()) {
        $pubDate = date('r', strtotime($row["timestamp"]));

        echo "<item>
  <media:thumbnail url=\"".get_baseurl()."/img/blog/$row[img]\" />
  <title>".htmlspecialchars($row["title"])."</title>
  <description>".htmlspecialchars($row["description"])."</description>
  <link>".get_baseurl()."/blog/post/$row[url]</link>
  <guid isPermaLink=\"true\">".get_baseurl()."/blog/post?id=$row[id]</guid>
  <pubDate>$pubDate</pubDate>
</item>";
    }
?>

</channel>
</rss>