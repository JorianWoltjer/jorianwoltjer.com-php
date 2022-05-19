<?php use JetBrains\PhpStorm\NoReturn;

require_once(__DIR__."/../../mysqli_connect.php"); require_once(__DIR__."/../../secret.php");

// HTTP Headers
$nonce = str_replace("=", "", base64_encode(random_bytes(20)));
$csp = "Content-Security-Policy: frame-ancestors 'self'; script-src 'self' 'nonce-".$nonce."'; 
style-src 'self' 'nonce-".$nonce."'; base-uri 'self'; object-src 'none'";
header(str_replace(array("\r","\n"), "", $csp));

$admin = false;
if (isset($_COOKIE["PHPSESSID"])) {
    session_start();
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $admin = true;
    }
}
if (isset($admin_required) && $admin_required && !$admin) { // Admin only
    header("HTTP/1.1 403 Forbidden");
    $_GET['code'] = "403";
    require("../error.php");
    exit();
}

$html_messages = array(
    "error_folder" => '<div class="alert alert-danger animated bounceOut" role="alert">This folder does <b>not exist</b></div>',
    "error_post" => '<div class="alert alert-danger animated bounceOut" role="alert">This post does <b>not exist</b></div>',
    "all_fields" => '<div class="alert alert-danger animated bounceOut" role="alert">Please fill <b>all fields</b> in the form</div>',
    "wrong_password" => '<div class="alert alert-danger animated bounceOut" role="alert">Invalid password</div>',
    "captcha" => '<div class="alert alert-danger animated bounceOut" role="alert">reCAPTCHA invalid</div>',
    "login" => '<div class="alert alert-success animated bounceOut" role="alert">Successfully logged in</div>',
    "error_featured_and_hidden" => '<div class="alert alert-danger animated bounceOut" role="alert">You cannot set a post as both <b>featured</b> and <b>hidden</b></div>',
);

function get_baseurl(): string
{
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || $_SERVER['SERVER_PORT']==443) ? 'https://':'http://';
    $domain = $_SERVER['SERVER_NAME'];

    return $protocol.$domain;  // https://jorianwoltjer.com
}

function time_to_ago($time): string
{
    $diff_time=(strtotime(date("Y/m/d H:i:s"))-strtotime($time))*1000;

    $msPerMinute = 60 * 1000;
    $msPerHour = $msPerMinute * 60;
    $msPerDay = $msPerHour * 24;
    $msPerMonth = $msPerDay * 30;
    $msPerYear = $msPerDay * 365;

    if ($diff_time < $msPerMinute) {
        $value = $diff_time/1000;
        $unit = "second";
    } else if ($diff_time < $msPerHour) {
        $value = $diff_time/$msPerMinute;
        $unit = "minute";
    } else if ($diff_time < $msPerDay ) {
        $value = $diff_time/$msPerHour;
        $unit = "hour";
    } else if ($diff_time < $msPerMonth) {
        $value = $diff_time/$msPerDay;
        $unit = "day";
    } else if ($diff_time < $msPerYear) {
        $value = $diff_time/$msPerMonth;
        $unit = "month";
    } else {
        $value = $diff_time/$msPerYear;
        $unit = "year";
    }

    $s = round($value) === 1.0 ? '' : 's';

    return round($value).' '.$unit.$s.' ago';
}

function displayMessage(): void
{
    global $html_messages;
    if (isset($_GET["message"])) {
        echo $html_messages[$_GET["message"]];
    }
}

#[NoReturn] function returnMessage($message, $location = ""): void
{
    if (empty($location)) {
        $location = basename($_SERVER['PHP_SELF'], ".php");
        $url_query = $_GET;
    } else {
        $url_query = array();
    }
    $url_query['message'] = $message;
    $url_query_result = http_build_query($url_query);
    header("location: $location?" . $url_query_result);
    exit();
}

function sql_query(string $sql, array $params = []): mysqli_result|bool|null
{
    global $dbc;

    if ($stmt = $dbc->prepare($sql)) {
        if ($params) {  // Bind parameters securely
            $sql_types = array(
                "string" => "s",
                "integer" => "i",
                "boolean" => "i",
                "double" => "d",
            );
            $types = "";
            foreach ($params as $param) {
                $types .= $sql_types[gettype($param)] ?? "s";
            }
            $stmt->bind_param($types, ...$params);
        }
        // Execute query
        $success = $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result === false ? $success : $result;  // Return boolean success if no result
    }
    return null;  // If anything failed
}

function md_to_html(string $text): array|string|null
{
    require_once("../include/Parsedown.php");

    // Replace images with /img/blog URL
    $text = preg_replace('/!\[(.*?)\]\((.*\/)*(.*?)\)/',
        '![$1](/img/blog/$3)', $text);

    // Markdown to HTML
    $Parsedown = new Parsedown();
    $text = $Parsedown->text($text);

    // Add id attribute to h2 headers
    $text = preg_replace_callback( '/<h2>(.*?)<\/h2>/i', function( $matches ) {
        $id = text_to_url($matches[1]);
        return '<h2 id="'.$id.'">'.$matches[1].'</h2>';
    }, $text);

    // Image lightbox
    $text = preg_replace('/<img src="(.*?)" alt="(.*?)" \/>/',
        '<img src="$1" class="lightbox" alt="$2" />', $text);

    // Style code blocks
    return preg_replace('/<pre><code class="language-(.*?)">(.*?)<\/code><\/pre>/s',
        '<div class="code-block"><p>$1<a class="copy" id="copy" onclick="copy_code(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Copied!"><i class="fa-solid fa-copy"></i></a style="float: right"></p><pre><code class="language-$1">$2</code></pre></div>',
        $text);
}

function text_to_url(string $text): string
{
    $text = preg_replace('/(&.*?;)|(<.*?>)|[^\w ]/i', '', $text);  // Filter out encoded html characters & tags
    $text = trim(preg_replace('/[^\w\d]+/', ' ', $text));  // Convert other characters to spaces + trim
    $text = preg_replace('/ /', '-', $text);  // Convert space to dashes
    return strtolower($text);  // Lowercase
}

function first_sentence(string $text): string
{
    preg_match('/^.*?(\w[!?.] |$)/', $text, $match);
    return $match[0];
}
