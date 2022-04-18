<?php
$centerPage = true;

$error_codes = array(
    '404' => 'Not Found',
    '403' => 'Forbidden',
    '500' => 'Internal Server Error'
);

// Get error code from parameter
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $title = $error_codes[$code] ?? 'Error';
    if ($title != 'Error') {
        $title = $code." ".$title;  // "404 Not Found"
    }
} else {
    $title = 'Error';
}
require("include/header.php"); ?>

<style>

</style>

<i class="fa-solid fa-triangle-exclamation big-icon"></i>
<h1 class="my-4"><code><?= htmlspecialchars($title) ?></code></h1>
<p class="lead">
    There was an error loading this page.<br>
    You can try going back to <a href="/">Home</a> or the <a href="#" onclick="history.back()">Previous Page</a>.
</p>

<?php require("include/footer.php"); ?>