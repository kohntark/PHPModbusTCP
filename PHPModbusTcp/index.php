<?php
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <!-- version 0000 00/00/0000 00:00:00 -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link type="text/css" href="css/main.css" rel="stylesheet" media="screen" />
        <script type="text/javascript" src="js/jquery-1.4.1.min.js"></script>
    </head>
    <body>
        <div style="width:480px;height:50px;margin: 10px auto 15px auto;margin-bottom: 50px;border-style: solid;border-width: 1px;">
            <img src="img/hippo.gif" alt="programming is not funny :o) !!" style="float: left; margin: 0 65px 0 0;" />
            <h2>Debugger PHPModbusTcp</h2>
        </div>

        <div style="margin:15px 0px 15px 15px;">

        <?php
        if (isset($_GET['p'])) {
            include($_GET['p'].php);
        } else include 'main.php';

        ?>
        </div>

    </body>
</html>
