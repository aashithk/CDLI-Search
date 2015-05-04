<?php
/*
 * This page is tool to check transliteration and email the results to the user.
 * It calls signfilter.pl.
 * */
function mail_attachment($files, $to, $messagebody)
{

    require_once 'swift-4.1.1/lib/swift_required.php';

//Create the Transport
    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'sslv3')
        ->setUsername('ucla.cdli@gmail.com')
        ->setPassword('cdlicdli');

    // create the mailer using the 'newInstance()' method
    $mailer = Swift_Mailer::newInstance($transport);


    // create a simple message using the 'newInstance()' method
    $message = Swift_Message::newInstance()
        ->setSubject('cdli sign and word cdhecking result')
        ->setFrom(array('cdli@cdli.ucla.edu' => 'cdli'))
        ->setTo(array($to))
        ->setBody('Result files');

    // preparing attachments
    for ($x = 0; $x < count($files); $x++) {
        $message->attach(Swift_Attachment::fromPath($files[$x]));
    }


    if ($mailer->send($message)) {
        echo "Result was sent to $to successfully!";
    } else {
        echo 'No email provided or email was invalid';
    }


}

// set the maximum upload size in bytes
$max = 51200;
$defaultfile = '/Library/WebServer/Documents/cdli/search/signlist/';
//At each page load search for available sign/word files to display.
//This could be cached in a text file to reduce server load but it's ok for now.
if ($handle = opendir($defaultfile)) {
    $signlist = array();
    $wordlist = array();
    $periods = array();
    $printPeriods = array();
    while (false !== ($entry = readdir($handle))) {
        if (preg_match('/.*_.*\.txt\b/', $entry)) {

            list($period, $filetype) = explode('_', $entry);

            if ($filetype == "signlist.txt") {
                $signlist[] = $period;
            }
            if ($filetype == "wordlist.txt") {
                $wordlist[] = $period;
            }
        }
    }
    foreach ($signlist as $per) {
        if (in_array($per, $wordlist)) {
            $periods[] = $per;
            $printPeriods[] = str_replace("-", " ", $per);
        }
    }
    closedir($handle);
}
if (isset($_POST['upload']) && isset($_FILES["trans"])) {
    try {
        $transfile = $_FILES["trans"]["tmp_name"];
        $onlylist = False;
        // if the user upload the custom sign and word lists.
        if (strlen($_FILES["sign_list"]["tmp_name"]) > 0 && strlen($_FILES["word_list"]["tmp_name"]) > 0) {
            $signfile = $_FILES["sign_list"]["tmp_name"];
            $wordfile = $_FILES["word_list"]["tmp_name"];

        } elseif (isset($_POST['period'])) {
            // if period is specified, use the signlist for the period
            $period = $_POST['period'];
            $signfile = $defaultfile . $period . '_signlist.txt';
            $wordfile = $defaultfile . $period . '_wordlist.txt';
        } else {
            // if no period is specified,use empty ones
            $onlylist = True;
            $signfile = $defaultfile . 'empty.txt';
            $wordfile = $defaultfile . 'empty.txt';
        }

        // get tmp dir to write new files
        $destination = sys_get_temp_dir();
        // get timestamp
        $timestamp = time();
        $newwords = $destination . 'newwords' . $timestamp . '.txt';
        $newwordlines = $destination . 'newwordlines' . $timestamp . '.txt';
        $newsigns = $destination . 'newsigns' . $timestamp . '.txt';
        $newsignslines = $destination . 'newsignlines' . $timestamp . '.txt';
        $allsigns = $destination . 'allsigns' . $timestamp . '.txt';
        $allwords = $destination . 'allwords' . $timestamp . '.txt';

        //run perl script which puts the files in thier place
        print exec('perl signfilter.pl '
            . EscapeShellArg($transfile)
            . ' '
            . EscapeShellArg($signfile)
            . ' '
            . EscapeShellArg($wordfile)
            . ' '
            . EscapeShellArg($newwords)
            . ' '
            . EscapeShellArg($newwordlines)
            . ' '
            . EscapeShellArg($newsigns)
            . ' '
            . EscapeShellArg($newsignslines)
            . ' '
            . EscapeShellArg($allwords)
            . ' '
            . EscapeShellArg($allsigns));


        if ($onlylist) {
            $files = array($allwords, $allsigns);
            $message = "Two files";
        } else {
            $files = array($newwords, $newwordlines, $newsigns, $newsignslines, $allwords, $allsigns);
            $message = "Six files";
            clearstatcache();
            if (file_exists($newwords)) {
                $int_fileSize = filesize($newwords);
                if ($int_fileSize > 0) {
                    $fh = fopen($newwords, 'r');
                    $string_newwords = fread($fh, $int_fileSize);
                    fclose($fh);
                } else {
                    // again, if no new words, spit out an error??
                    echo "<font color = 'red'> Error 1: the new words file was empty or didn't upload correctly </font> <BR>";
                }

            } else {
                echo "<font color = 'red'> Error 11: the new words file didn't upload correctly $newwords</font> <BR>";
            }
            clearstatcache();
            if (file_exists($newsigns)) {
                $int_fileSize = filesize($newsigns);
                if ($int_fileSize > 0) {
                    $fh = fopen($newsigns, 'r');
                    $string_newsigns = fread($fh, filesize($newsigns));
                    fclose($fh);
                } else {
                    //so if the file contains no new signs, spit out an error message?
                    echo "<font color = 'red'> Error 2: the new signs file was empty or didn't upload correctly</font> <BR>";
                }
            } else {
                echo "<font color = 'red'> Error 21: the new signs file didn't upload correctly $newsigns</font> <BR>";
            }
        }

        if (isset($_POST['email'])) {
            //echo 'post';
            $useremail = $_POST['email'];
            //echo $useremail;
            if ($useremail <> "") {
                mail_attachment($files, $useremail, $message);
            }
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta charset="utf-8">
    <title>
        Upload File
    </title>
    <link href="sign_style.css" media="screen" rel="stylesheet" type="text/css">
</head>
<body>

<div id="cdliheader">
    <a href="http://cdli.ucla.edu"><img alt="" width="81"
                                        height="51" border="0"
                                        src="cdli_logo.gif"/></a>
    Sign and word checker
</div>
<form action="" method="post" enctype="multipart/form-data" id="upliadsw" name="upliadsw">
    <div id="upload_transliteration" class="upload">
        <div class="upload_left">
            1
        </div>
        <div class="upload_right">
            <label for="image">Upload your transliteration file:</label><input type="file" name="trans" id="image"
                                                                               style="color:white;">
        </div>
    </div>
    <div id="upload_sign_word">
        <div class="upload_left">
            2
        </div>
        <div class="upload_right">
            <input type="radio" name="option" value="lateuruk">
            Use a cdli sign and word list file:<br>

            <div class="sign_word">
                <?php
                foreach ($periods as $i => $per) {
                    echo "<input type=\"radio\" name=\"period\" value=$per> $printPeriods[$i]<br>";
                }
                ?>
            </div>
            <input type="radio" name="option" value="lateuruk2">
            upload your own sign and word list file:<br>

            <div class="sign_word">
                <label for="image2">Upload Sign List File:</label> <input type="file" name="sign_list" id="image2"
                                                                          style="color:white;"><br>
                <label for="image3">Upload Word List File:</label> <input type="file" name="word_list" id="image3"
                                                                          style="color:white;">
            </div>
            <input type="radio" name="option" value="lateuruk3">
            Create a sign and word list from your transliteration file <br/>
        </div>
    </div>
    <div id="process">
        <div class="upload_left">
            3
        </div>
        <div class="upload_right">
            <label for="email">Email results to:</label> <input type="text" value="" name="email" id="email"
                                                                style="width:375px;">

            <div id="process_btn">
                <br/>
                <input type="submit" name="upload" id="upload" value="Process">
            </div>
        </div>
    </div>
</form>
<?php echo $message ?>
<BR>
<BR>
<TABLE cellpadding="20">
    <TR>
        <?php
        if (isset($string_newsigns) && $string_newsigns <> "") {
            echo "<TD>New Signs</TD>";
        }
        if (isset($string_newwords) && $string_newwords <> "") {
            echo "<TD>New words</TD>";
        }
        echo '</TR><TR>';
        if (isset($string_newsigns) && $string_newsigns <> "") {
            echo "<TD ALIGN=LEFT VALIGN=TOP>";
            echo nl2br(trim($string_newsigns));
            echo '</TD>';
        }
        if (isset($string_newwords) && $string_newwords <> "") {
            echo "<TD ALIGN=LEFT VALIGN=TOP>";
            echo nl2br(trim($string_newwords));
            echo '</TD>';
        }
        ?>

    </TR>
</TABLE>
</body>
</html>
