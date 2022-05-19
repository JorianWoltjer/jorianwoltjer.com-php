<?php $centerPage = true;
$meta_title = "School Websites";
$meta_description = "A list of buttons that link to useful school websites for Hanzehogeschool Groningen. Mostly used by me and my friends to quickly get to the websites we use often.";
require_once("../include/header.php"); ?>

    <h1 class="my-4"><code>School Websites</code></h1>
    <br>
    <div class="buttons" id="buttons">
        <a href="https://digirooster.hanze.nl/"><div class="button red">
            <div class="button-icon"><i class="fa-solid fa-calendar-alt"></i></div>
            <div class="button-text">Digirooster</div>
        </div></a>
        <a href="https://www.hanze.nl/nld/voorzieningen/voorzieningen/hanzemediatheek"><div class="button orange">
            <div class="button-icon"><i class="fa-solid fa-book"></i></div>
            <div class="button-text">Mediatheek</div>
        </div></a>
        <a href="https://www.hanze.nl/"><div class="button yellow">
            <div class="button-icon"><i class="fa-solid fa-home"></i></div>
            <div class="button-text">Hanze.nl</div>
        </div></a>
        <a href="https://blackboard.hanze.nl/webapps/bb-auth-provider-shibboleth-bb_bb60/execute/shibbolethLogin?returnUrl=https%3A%2F%2Fblackboard.hanze.nl%2Fwebapps%2Fportal%2Fexecute%2FdefaultTab&authProviderId=_109_1"><div class="button green">
            <div class="button-icon"><i class="fa-solid fa-chalkboard"></i></div>
            <div class="button-text">Blackboard</div>
        </div></a>
        <a href="https://hanze.osiris-student.nl/"><div class="button blue">
            <div class="button-icon"><i class="fa-solid fa-poll"></i></div>
            <div class="button-text">Osiris</div>
        </div></a>
        <a href="https://www.gradescope.com/login"><div class="button purple">
            <div class="button-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="button-text">GradeScope</div>
        </div></a>
        <a href="https://www.sv-realtime.nl/home"><div class="button gray">
            <div class="button-icon"><i class="fa-solid fa-clock"></i></div>
            <div class="button-text">RealTime</div>
        </div></a>
    </div>

<script>
    document.addEventListener('keydown', keyPress);

    function keyPress(e) {
        const n = parseInt(e.key);
        if (1 <= n && n <= 7) {  // Range 1-7
            // Set href to nth button
            document.location.href = document.getElementById("buttons").children[n - 1].href;
        }
    }
</script>

<?php require_once("../include/footer.php"); ?>