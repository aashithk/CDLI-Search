<?php
require_once "bootstrap.php";
?>


<html>
<head>
    <link rel="stylesheet" href="vendor/css/codemirror/codemirror.css">
    <link rel="stylesheet" href="vendor/css/codemirror/codemirror-theme.css">
</head>
<body>


<textarea id="trans"><?php echo getEM()->find("Trans", $_GET["id"])->getWholetext() ?></textarea>
<script src="vendor/js/codemirror/codemirror.js"></script>
<script src="vendor/js/codemirror/cdli.js"></script>
<script>

    //
    <!-- Create a simple CodeMirror instance -->

    var editor = CodeMirror.fromTextArea(document.getElementById("trans"), {
        lineNumbers: true,
        theme: "cdli",
        mode: "cdli"
    });
    editor.setSize("100%", "800");
</script>
</html>