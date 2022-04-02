<?php
$centerPage = true;
$title = "Home";
$description = "Hello, I am Jorian Woltjer. Welcome to my website!";
require_once("include/header.php"); ?>

    <style>
        body {
            background: none;
        }
        canvas {
            display: block;
            vertical-align: bottom;
        }

        #page-content img {
            max-width: 80%;
            box-shadow: 0 0 50px 0 rgb(0 0 0 / 70%);
            border-radius: 25px;
            padding: 30px 40px;
            width: 35ch;
        }
        code {
            font-size: inherit;
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

    <h1 class="my-4">Hello, I am <code>Jorian Woltjer</code></h1>
    <img src="/img/logo.svg">
    <h1 class="my-4"><code>Welcome</code> to my <code>website!</code></h1>

<?php require_once("include/footer.php"); ?>