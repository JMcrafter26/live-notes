<?php

// Base URL of the website, without trailing slash.
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

// Path to the directory to save the notes in, without trailing slash.
// Should be outside of the document root, if possible.
$save_path = '_tmp';
$useEncryption = true; // Important: If you change this after you have created some notes, you will not be able to read them anymore.

// Disable caching.
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// If no name is provided or it contains invalid characters or it is too long.
if (!isset($_GET['note']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note']) || strlen($_GET['note']) > 64) {

    // Generate a name with 6 random unambiguous characters. Redirect to it.
    header("Location: $base_url/" . substr(str_shuffle('234579abcdefghjkmnpqrstwxyz'), -6));
    die;
}

$path = $save_path . '/' . $_GET['note'];

if (isset($_POST['text'])) {
    // if file exists, get the encryption key
    if ($encryption == true) {
        if (file_exists($path)) {
            $key = file_get_contents($path);
            $key = json_decode($key, true);
            $key = $key['key'];
        } else {
            // generate a random key
            $key = openssl_random_pseudo_bytes(32);
        }

        $content = $_POST['text'];
        $iv = '1234567890123456';
        $content = openssl_encrypt($content, 'aes-256-cbc', $key, 0, $iv);

        $json = array(
            'id' => $_GET['note'],
            'content' => $content,
            'key' => $key,
            'last_modified' => time()
        );

        $content = json_encode($json, JSON_PRETTY_PRINT);
    } else {
        $content = $_POST['text'];

        $json = array(
            'id' => $_GET['note'],
            'content' => $content,
            'last_modified' => time()
        );

        $content = json_encode($json, JSON_PRETTY_PRINT);
    }

    file_put_contents($path, $content);

    // If provided input is empty, delete file.
    if (!strlen($_POST['text'])) {
        unlink($path);
    }
    die;
}

// if url contains ?raw, set $_GET['raw'] to true.
// echo $_SERVER['request_method'] . ' ' . $_SERVER['REQUEST_URI'];
if (strpos($_SERVER['REQUEST_URI'], '?raw') !== false) {
    $raw = true;
} elseif (strpos($_SERVER['REQUEST_URI'], '?json') !== false) {
    $raw = 'json';
}


/* This is the code that is executed when the user agent is curl or wget. It returns the content of the
note in plain text. */
if (isset($raw) || strpos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Wget') === 0) {
    if (is_file($path)) {
        header('Content-type: text/plain');
        $json = file_get_contents($path);
        $json = json_decode($json, true);
        $content = $json['content'];
        if ($encryption == true) {
            $key = $json['key'];
            $iv = '1234567890123456';
            $content = openssl_decrypt($content, 'aes-256-cbc', $key, 0, $iv);
        }
        if ($raw === 'json') {
            print json_encode(array(
                'id' => $_GET['note'],
                'content' => $content,
                'last_modified' => date('Y-m-d H:i:s', $json['last_modified'])
            ), JSON_PRETTY_PRINT);
        } else {
            print $content;
        }
    } else {
        header('HTTP/1.0 404 Not Found');
    }
    die;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A minimalistic note taking web app, that automatically saves your notes as you type and allows you to share them with others in real time." />
    <meta name="keywords" content="note, notes, note taking, note taking app, live notes, live note taking, share notes, share note" />
    <meta name="author" content="JMcrafter26" />
    <meta name="theme-color" content="#383934" />
    <title><?php print $_GET['note']; ?></title>
    <link rel="icon" href="<?php print $base_url; ?>/favicon.ico" sizes="any">
    <link rel="stylesheet" href="<?php print $base_url; ?>/styles.css">
</head>

<body>
    <div class="container">
        <textarea id="content">
            <?php
            /* Decrypting the content of the note. */
            if (is_file($path)) {
                $json = file_get_contents($path);
                $json = json_decode($json, true);
                $content = $json['content'];
                if ($encryption == true) {
                    $key = $json['key'];
                    $iv = '1234567890123456';
                    $content = openssl_decrypt($content, 'aes-256-cbc', $key, 0, $iv);
                }
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
                print $content;
            } ?>
                                </textarea>
    </div>
    <pre id="printable"></pre>
    <script src="<?php print $base_url; ?>/script.js"></script>
</body>
</html>