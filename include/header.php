<?php require_once("all.php");

$page = preg_match("/\/([\\w_\-\\d]*)/", $_SERVER['REQUEST_URI'], $match);
$page = ($match[1] === "" ? "home" : $match[1]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (isset($meta_title)) { ?>
        <title><?= htmlspecialchars($meta_title) ?> | Jorian Woltjer</title>
        <meta property="og:title" content="<?= htmlspecialchars($meta_title) ?> | Jorian Woltjer">
        <meta name="twitter:title" content="<?= htmlspecialchars($meta_title) ?> | Jorian Woltjer">
    <?php if (isset($meta_description)) { ?>
        <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($meta_description) ?>">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="<?= (isset($meta_large_card) && $meta_large_card) ? "summary_large_image" : "summary" ?>">
        <meta property="og:url" content="<?= htmlspecialchars(get_baseurl().$_SERVER["REQUEST_URI"]) ?>">
        <meta property="twitter:url" content="<?= htmlspecialchars(get_baseurl().$_SERVER["REQUEST_URI"]) ?>">
        <meta property="og:image" content="<?= htmlspecialchars(get_baseurl()) . ($meta_image ?? "/img/round_logo.png") ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars(get_baseurl()) . ($meta_image ?? "/img/round_logo.png") ?>">
        <meta property="twitter:domain" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>">
        <meta property="og:site_name" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>">
    <?php }} ?>

    <!-- CSS -->
    <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="/assets/fontawesome/css/all.min.css" rel="stylesheet">
    <link nonce="<?=$nonce?>" href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">

    <script nonce="<?=$nonce?>">
        // Clean url if message parameter
        const url = new URL(location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, null, url.toString());
    </script>
    <script src="/assets/jquery/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="/assets/bootstrap/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body class="d-flex flex-column">

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/img/logo.svg" alt="JW Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($admin) && $admin) { ?>
                    <li class="nav-item">
                        <a class="nav-link gray" id="logout" href="/logout">Logout</a>
                        <script nonce="<?=$nonce?>">
                            document.getElementById('logout').addEventListener('click', function (e) {
                                e.preventDefault();
                                if (confirm('Are you sure you want to log out?')) {
                                    window.location.href = "/logout?return=" + encodeURIComponent((location.pathname + location.search).substring(1));
                                }
                            });
                        </script>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link<?= $page == 'home' ? ' active' : '' ?>" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $page == 'blog' ? ' active' : '' ?>" href="/blog/">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $page == 'projects' ? ' active' : '' ?>" href="/projects/">Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $page == 'contact' ? ' active' : '' ?>" href="/contact">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<div id="page-content"<?= !isset($centerPage) ? "" : " class='center-page'" ?>>
    <div class="container">