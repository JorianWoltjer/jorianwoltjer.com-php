<?php
$centerPage = true;
$meta_title = "Home";
$meta_description = "I'm a Dutch programmer and Ethical Hacker. I'm interested in security and have a blog all about it, with writeups of challenges, tools and stories. You can also find information about how to contact me or the projects I've done.";
require_once("include/header.php"); ?>

    <style>
        body {
            background: none;
        }
        canvas {
            display: block;
            vertical-align: bottom;
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
    <img src="/img/logo.svg" class="boxed-img" alt="JW Logo">
    <h1 class="my-4"><code>Welcome</code> to my <code>website!</code></h1>

<?php require_once("include/footer.php"); ?>