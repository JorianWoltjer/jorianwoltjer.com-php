<?php $centerPage = true;
require_once("include/header.php"); ?>

    <style>
        body {
            background: none;
        }

        canvas {
            display: block;
            vertical-align: bottom;
        }
    </style>

    <div id="particles-js"></div>
    <script src="/assets/particles/particles.min.js"></script>
    <script>
        /* particlesJS.load(@dom-id, @path-json, @callback (optional)); */
        particlesJS.load('particles-js', '/assets/particles/config.json', function() {
            console.log('callback - particles.js config loaded');
        });
    </script>

    <h1 class="my-4 index-page"><?= randomCode("Hello, I am Jorian Woltjer"); ?></h1>
    <img src="/img/logo.png" class="index-page">
    <h1 class="my-4 index-page"><?= randomCode("Welcome to my website!"); ?></h1>

<?php require_once("include/footer.php"); ?>