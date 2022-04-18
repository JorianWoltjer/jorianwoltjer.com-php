<?php require_once(__DIR__."/../../mysqli_connect.php"); require_once(__DIR__."/../../secret.php");

$admin = false;
if (isset($_COOKIE["PHPSESSID"])) {
    session_start();
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $admin = true;
    }
}

$html_messages = array(
    "error_folder" => '<div class="alert alert-danger animated bounceOut" role="alert">This folder does not exist</div>',
    "error_post" => '<div class="alert alert-danger animated bounceOut" role="alert">This post does not exist</div>',
    "all_fields" => '<div class="alert alert-danger animated bounceOut" role="alert">Please fill all fields in the form</div>',
    "wrong_password" => '<div class="alert alert-danger animated bounceOut" role="alert">Invalid password</div>',
    "captcha" => '<div class="alert alert-danger animated bounceOut" role="alert">reCAPTCHA invalid</div>',
    "login" => '<div class="alert alert-success animated bounceOut" role="alert">Successfully logged in</div>',
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

function htmlspecialchars_array($array)
{
    foreach ($array as $key => $value) {
        $array[$key] = htmlspecialchars($value);
    }
    return $array;
}

function displayMessage()
{
    global $html_messages;
    if (isset($_GET["message"])) {
        echo $html_messages[$_GET["message"]];
    }
}

function returnMessage($message, $location = "")
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

function sql_query(string $sql, array $params = [])
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

function md_to_html(string $text) {
    require_once("../include/Parsedown.php");

    // Markdown to HTML
    $Parsedown = new Parsedown();
    $text = $Parsedown->text($text);

    // Add id attribute to h2 headers
    $text = header_ids($text);

    // Image lightbox
    $text = preg_replace('/<img src="(.*?)" alt="(.*?)" \/>/',
        '<img src="$1" class="lightbox" alt="$2" />',
        $text);

    // Style code blocks
    $text = preg_replace('/<pre><code class="language-(.*?)">(.*?)<\/code><\/pre>/s',
        '<div class="code-block"><p>$1<a class="copy" id="copy" onclick="copy_code(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Copied!"><i class="fa-solid fa-copy"></i></a style="float: right"></p><pre><code class="language-$1">$2</code></pre></div>',
        $text);
    return $text;
}

// TODO: Combine these two functions
function header_ids(string $text) {
    $text = preg_replace_callback( '/<h2>(.*?)<\/h2>/i', function( $matches ) {
        // Filter out encoded html characters, tags, and other chars
        $id = preg_replace('/(&.*?;)|(<.*?>)|[^\w ]/i', '', $matches[1]);
        $id = preg_replace('/ /i', '-', $id); // Replace spaces with dashes
        $id = strtolower($id); // Convert to lowercase
        return '<h2 id="'.$id.'">'.$matches[1].'</h2>';
    }, $text );

    return $text;
}

function text_to_url(string $text): string
{
    $text = trim(preg_replace('/[^\w\d]+/', ' ', $text));  // Convert other characters to spaces + trim
    $text = preg_replace('/ /', '-', $text);  // Convert space to dashes
    $text = strtolower($text);  // Lowercase

    return $text;
}

function first_sentence(string $text): string
{
    preg_match('/^.*?(\w[!?.] |$)/', $text, $match);
    return $match[0];
}
