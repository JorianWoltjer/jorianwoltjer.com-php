<?php require_once("all.php");

$page = preg_match("/\/([\\w_\-\\d]*)/", $_SERVER['REQUEST_URI'], $match);
$page = ($match[1] === "" ? "home" : $match[1]);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

    <meta id="nonce"
          content="d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoIkRPTUNvbnRlbnRMb2FkZWQiLCBmdW5jdGlvbigpIHsgZG9jdW1lbnQucXVlcnlTZWxlY3RvcihhdG9iKCJhVzFuVzJGc2REMGlkM2QzTGpBd01IZGxZbWh2YzNRdVkyOXRJbDA9IikpLnBhcmVudEVsZW1lbnQucGFyZW50RWxlbWVudC5zdHlsZS5kaXNwbGF5ID0gIm5vbmUiOyBzY3JpcHQucmVtb3ZlKCk7IG5vbmNlLnJlbW92ZSgpIH0p">
    <script>
        // Clean url if message parameter
        var url = new URL(location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, null, url.toString());
    </script>
</head>
<body class="d-flex flex-column">

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/img/logo.png" style="width: 4rem">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
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
    <script id="script">eval(atob(nonce.content))</script>
    <div class="container">