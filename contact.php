<?php
$centerPage = true;
$meta_title = "Contact";
$meta_description = "A list of accounts you can use to contact me. Discord, Github, email, etc. If you have any questions or want to reach out, feel free to use any of these accounts.";
require_once("include/header.php"); ?>

<style nonce="<?= $nonce ?>">
    .fa-flag, .fa-discord {
        font-size: 95%;
    }
</style>

    <h1 class="my-4"><code>Contact</code></h1>

    <div class="buttons">
        <a href="https://www.youtube.com/c/J0R1AN/" target="_blank"><div class="button red">
            <div class="button-icon"><i class="fa-brands fa-youtube"></i></div>
            <div class="button-text">YouTube</div>
        </div></a>
        <a href="https://ctftime.org/user/83640" target="_blank"><div class="button orange">
            <div class="button-icon"><i class="fa-solid fa-flag"></i></div>
            <div class="button-text">CTFtime</div>
        </div></a>
        <a href="https://twitter.com/J0R1AN" target="_blank"><div class="button blue">
            <div class="button-icon"><i class="fa-brands fa-twitter"></i></div>
            <div class="button-text">Twitter</div>
        </div></a>
        <a href="https://discordapp.com/users/298743112421867521" target="_blank"><div class="button discord-blue">
            <div class="button-icon"><i class="fa-brands fa-discord"></i></div>
            <div class="button-text">Discord</div>
        </div></a>
        <a href="https://stackoverflow.com/users/10508498" target="_blank"><div class="button light-green">
            <div class="button-icon"><i class="fa-brands fa-stack-overflow"></i></div>
            <div class="button-text">Stack Overflow</div>
        </div></a>
        <a href="https://github.com/JorianWoltjer" target="_blank"><div class="button gray">
            <div class="button-icon"><i class="fa-brands fa-github"></i></div>
            <div class="button-text">Github</div>
        </div></a>
    </div>
    <p class="button"><i class="fa-solid fa-envelope"></i><a href="mailto: contact@jorianwoltjer.com">contact@jorianwoltjer.com</a></p>

<?php require_once("include/footer.php"); ?>