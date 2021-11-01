<?php require_once ('include/all.php');
header("Content-type: text/xml");
echo'<?xml version="1.0" encoding="UTF-8"?>';
echo'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
?>
<url>
  <loc><?= get_baseurl() ?>/</loc>
  <lastmod>2021-08-13T16:52:51+00:00</lastmod>
</url>
<url>
  <loc><?= get_baseurl() ?>/blog/</loc>
  <lastmod>2021-08-13T16:52:51+00:00</lastmod>
</url>
<url>
  <loc><?= get_baseurl() ?>/projects/</loc>
  <lastmod>2021-08-13T16:52:51+00:00</lastmod>
</url>
<url>
  <loc><?= get_baseurl() ?>/contact</loc>
  <lastmod>2021-08-13T16:52:51+00:00</lastmod>
</url>
<url>
  <loc><?= get_baseurl() ?>/projects/school_websites</loc>
  <lastmod>2021-08-13T16:52:51+00:00</lastmod>
</url>
<?php
$response = sql_query("SELECT url, timestamp FROM folders");

while ($row = $response->fetch_assoc()) {
    $lastmod = date(DATE_ATOM, strtotime($row["timestamp"]));

    echo "<url>
  <loc>".get_baseurl()."/blog/folder/$row[url]</loc>
  <lastmod>$lastmod</lastmod>
</url>";
}

$response = sql_query("SELECT url, timestamp FROM posts WHERE hash IS NULL");

while ($row = $response->fetch_assoc()) {
    $lastmod = date(DATE_ATOM, strtotime($row["timestamp"]));

    echo "<url>
  <loc>".get_baseurl()."/blog/post/$row[url]</loc>
  <lastmod>$lastmod</lastmod>
</url>";
}
?>

</urlset>