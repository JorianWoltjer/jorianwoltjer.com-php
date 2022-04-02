<?php require_once("all.php");

$page = preg_match("/\/([\\w_\-\\d]*)/", $_SERVER['REQUEST_URI'], $match);
$page = ($match[1] === "" ? "home" : $match[1]);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (isset($title) and isset($description)) { ?>
        <title><?= $title ?></title>
        <meta property="og:title" content="<?= $title ?>">
        <meta name="twitter:title" content="<?= $title ?>">
        <meta name="description" content="<?= $description ?>">
        <meta property="og:description" content="<?= $description ?>">
        <meta name="twitter:description" content="<?= $description ?>">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary">
        <meta property="og:url" content="<?= htmlspecialchars(get_baseurl().$_SERVER["REQUEST_URI"]) ?>">
        <meta property="twitter:url" content="<?= htmlspecialchars(get_baseurl().$_SERVER["REQUEST_URI"]) ?>">
        <meta property="og:image" content="<?= htmlspecialchars(get_baseurl()) ?>/img/round_logo.png">
        <meta name="twitter:image" content="<?= htmlspecialchars(get_baseurl()) ?>/img/round_logo.png">
        <meta property="twitter:domain" content="<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>">
    <?php } ?>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

    <script>
        // Clean url if message parameter
        var url = new URL(location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, null, url.toString());
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body class="d-flex flex-column">

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/img/logo.svg" style="width: 4rem">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($admin) { ?>
                    <li class="nav-item">
                        <a class="nav-link gray" id="logout" href="/logout"
                           onclick="return confirm('Are you sure you want to log out?')">Logout</a>
                        <script>document.getElementById("logout").href = "/logout?return=" + encodeURIComponent((location.pathname + location.search).substr(1))</script>
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