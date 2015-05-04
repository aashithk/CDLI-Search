<?php

function mail_attachment($files, $to)
{


    $from = 'cdli@cdli.ucla.edu';
    $subject = 'CDLI filter result';
    $message = "My message";
    $headers = "From: $from";

    // boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    // multipart boundary
    $message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    $message .= "--{$mime_boundary}\n";

    // preparing attachments
    for ($x = 0; $x < count($files); $x++) {
        $file = fopen($files[$x], "rb");
        $data = fread($file, filesize($files[$x]));
        fclose($file);
        $data = chunk_split(base64_encode($data));
        $filenameshort = basename($files[$x]);
        $filenameshort = preg_replace('/[0-9]/i', "", $filenameshort);
        $message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$filenameshort\"\n" .
            "Content-Disposition: attachment;\n" . " filename=\"$filenameshort\"\n" .
            "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        $message .= "--{$mime_boundary}\n";
    }

    $ok = @mail($to, $subject, $message, $headers);
    if ($ok) {
        echo "<p>mail sent to $to!</p>";
    } else {
        echo "<p>mail could not be sent!</p>";
    }

}

// set the maximum upload size in bytes
$max = 51200;
if (isset($_POST['upload'])) {
    // define the path to the upload folder
    require_once('Upload.php');
    try {
        $upload = new Ps2_Upload($destination);
        $upload->move();
        $result = $upload->getMessages();
        $fname = $upload->getFilenames();

        $transfile = $fname[0];
        $signfile = $fname[1];
        $wordfile = $fname[2];
        $timestamp = time();
        //echo $timestamp;
        $newwords = $destination . 'newwords' . $timestamp . '.txt';
        $newwordlines = $destination . 'newwordlines' . $timestamp . '.txt';
        $newsigns = $destination . 'newsigns' . $timestamp . '.txt';
        $newsignslines = $destination . 'newsignlines' . $timestamp . '.txt';

        exec('perl signfilter.pl ' . $destination . EscapeShellArg($transfile) . ' ' . $destination . EscapeShellArg($signfile) . ' ' . $destination . EscapeShellArg($wordfile) . ' ' . EscapeShellArg($newwords) . ' ' . EscapeShellArg($newwordlines) . ' ' . EscapeShellArg($newsigns) . ' ' . EscapeShellArg($newsignslines));
        //echo 'perl signfilter.pl '.$destination.EscapeShellArg($transfile).' '.$destination.EscapeShellArg($signfile).' '.$destination.EscapeShellArg($wordfile).' '.EscapeShellArg($newwords).' '.EscapeShellArg($newwordlines).' '.EscapeShellArg($newsigns).' '.EscapeShellArg($newsignslines);
        $files = array($newwords, $newwordlines, $newsigns, $newsignslines);
        if (isset($_POST['email'])) {
            //echo 'post';
            $useremail = $_POST['email'];
            //echo $useremail;
            mail_attachment($files, $useremail);
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}


if (isset($_POST['usesw'])) {
    // define the path to the upload folder
    require_once('Upload.php');
    try {
        $upload = new Ps2_Upload($destination);
        $upload->move();
        $result = $upload->getMessages();
        $fname = $upload->getFilenames();

        $transfile = $fname[0];
        $signfile = 'signlist.txt';
        $wordfile = 'wordlist.txt';

        $timestamp = time();
        //echo $timestamp;
        $newwords = $destination . 'newwords' . $timestamp . '.txt';
        $newwordlines = $destination . 'newwordlines' . $timestamp . '.txt';
        $newsigns = $destination . 'newsigns' . $timestamp . '.txt';
        $newsignslines = $destination . 'newsignlines' . $timestamp . '.txt';

        $mailed = FALSE;
        exec('perl signfilter.pl ' . $destination . EscapeShellArg($transfile) . ' ' . $destination . EscapeShellArg($signfile) . ' ' . $destination . EscapeShellArg($wordfile) . ' ' . EscapeShellArg($newwords) . ' ' . EscapeShellArg($newwordlines) . ' ' . EscapeShellArg($newsigns) . ' ' . EscapeShellArg($newsignslines));
        //echo 'perl signfilter.pl '.$destination.EscapeShellArg($transfile).' '.$destination.EscapeShellArg($signfile).' '.$destination.EscapeShellArg($wordfile).' '.EscapeShellArg($newwords).' '.EscapeShellArg($newwordlines).' '.EscapeShellArg($newsigns).' '.EscapeShellArg($newsignslines);
        $files = array($newwords, $newwordlines, $newsigns, $newsignslines);

        if (isset($_POST['email'])) {
            //echo 'post';
            $useremail = $_POST['email'];
            //echo $useremail;
            //$mailed = mail($useremail, 'CDLI singlist filter result', 'Result attached1 ');
            mail_attachment($files, $useremail);
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset=utf-8">
    <title>Upload File</title>

    <style type="text/css">
        body {
            background-color: #000000;
            color: #FFFFFF;
        }

        h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
    </style>

</head>

<body>

<div id="inputlist" class="uploadfile">
    <form action="" method="post" enctype="multipart/form-data" id="upliadsw">
        <h1>Upload your own testing file and lists</h1>

        <label for="image">Upload Transliteration File:</label>
        <input type="file" name="image[]" id="image">

        <label for="image2">Upload Sign List File:</label>
        <input type="file" name="image[]" id="image2">

        <label for="image3">Upload Word List File:</label>
        <input type="file" name="image[]" id="image3">

        <label for="email">email:</label>
        <input type="text" value="" name="email" id="email">

        <input type="submit" name="upload" id="upload" value="Upload">

    </form>
</div>


<form action="" method="post" enctype="multipart/form-data" id="usesw">
    <p>
        <label for="image">Upload Transliteration File:</label>
        <input type="file" name="image[]" id="image">
    </p>
    <input type="radio" name="period" value="uruk3"/> UrukIII<br/>
    <input type="radio" name="period" value="uruk4"/> UrukIV<br/>
    <input type="radio" name="period" value="ed3b"/> EDIII b<br/>
    <input type="radio" name="period" value="ur3"/> UrIII<br/>

    <p>
        <label for="email">email:</label>
        <input type="text" value="" name="email" id="email">
    </p>

    <p>
        <input type="submit" name="usesw" id="usesw" value="Upload">
    </p>
</form>

</body>
</html>
