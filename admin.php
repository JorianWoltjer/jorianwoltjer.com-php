<?php require_once("include/all.php");
$centerPage = true;

if ($admin) {  // Redirect if already logged in
    returnMessage("login", "/blog");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    sql_query("INSERT INTO login_logs(ip) VALUES (?)", [$_SERVER["REMOTE_ADDR"]]);
    if (isset($_POST['password']) && isset($_POST['recaptcha_response'])) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = '6LfptAMdAAAAALtgTfLT0JXJIcXmWtbPli8fxtfC';
        $recaptcha_response = $_POST['recaptcha_response'];

        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $recaptcha = json_decode($recaptcha);

        if ($recaptcha->score >= 0.5) {
            $admin_secret = sql_query("SELECT value FROM secret WHERE name = 'admin'")->fetch_assoc()['value'];
            if (password_verify($_POST['password'], $admin_secret)) {
                session_start();
                $_SESSION['loggedin'] = true;
                returnMessage("login", "/blog");
            } else {
                returnMessage("wrong_password");
            }
        } else {
            returnMessage("captcha");
        }
    } else {
        returnMessage("all_fields");
    }
}

require_once("include/header.php"); ?>

    <script src="https://www.google.com/recaptcha/api.js?render=6LfptAMdAAAAAIE2whjGTZUlStx--EfRETnzkMUV"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfptAMdAAAAAIE2whjGTZUlStx--EfRETnzkMUV', { action: 'contact' }).then(function (token) {
                var recaptchaResponse = document.getElementById('recaptchaResponse');
                recaptchaResponse.value = token;
            });
        });
    </script>

    <div class="box">
        <?php displayMessage() ?>
        <h1><code>Admin login</code></h1>
        <br>
        <form method="post">
            <input class="form-control" type="password" id="password" name="password" placeholder="Password" style="max-width: 300px; text-align: center">
            <br>
            <input class="btn btn-light" type="submit" value="Submit">
            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
        </form>
    </div>

<?php require_once("include/footer.php"); ?>