<?php
$centerPage = true;
$title = "Contact";
$description = "A list of accounts that can be used to contact me.";
require_once("include/header.php"); ?>

    <h1 class="my-4"><code>Contact</code></h1>

    <div class="buttons">
        <a href="https://www.youtube.com/c/J0R1AN/" target="_blank"><div class="button red">
            <div class="button-icon"><i class="fab fa-youtube"></i></div>
            <div class="button-text">YouTube</div>
        </div></a>
        <a href="https://www.reddit.com/user/JorianID" target="_blank"><div class="button orange">
            <div class="button-icon"><i class="fab fa-reddit-alien"></i></div>
            <div class="button-text">Reddit</div>
        </div></a>
        <a href="https://twitter.com/J0R1AN" target="_blank"><div class="button blue">
            <div class="button-icon"><i class="fab fa-twitter"></i></div>
            <div class="button-text">Twitter</div>
        </div></a>
        <a id="copy" href="" onclick="return copy_discord(this)"  data-bs-toggle="tooltip" data-bs-placement="right" title="" data-bs-original-title="Copied!" aria-label="Copied!"><div class="button dark-blue">
            <div class="button-icon"><i class="fab fa-discord"></i></div>
            <div class="button-text">Jorian#0135</div>
        </div></a>
        <a href="https://stackoverflow.com/users/10508498" target="_blank"><div class="button green">
            <div class="button-icon"><i class="fab fa-stack-overflow"></i></div>
            <div class="button-text">Stack Overflow</div>
        </div></a>
        <a href="https://github.com/JorianWoltjer" target="_blank"><div class="button gray">
            <div class="button-icon"><i class="fab fa-github"></i></div>
            <div class="button-text">Github</div>
        </div></a>
    </div>
    <p class="button"><i class="fa-solid fa-envelope"></i><a href="mailto: jorianwoltjer@hotmail.com">jorianwoltjer@hotmail.com</a></p>

<script>
    function copy_discord(e) {
        var tooltip = new bootstrap.Tooltip(document.getElementById("copy"))
        navigator.clipboard.writeText(e.innerText);
        tooltip.show();
        setTimeout(function() {
            tooltip.dispose();
        }, 2000);
        return false;
    }
</script>

<?php require_once("include/footer.php"); ?>