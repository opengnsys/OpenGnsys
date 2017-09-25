<?php   // Access to public menu.
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">

<head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <title>Private menu for OpenGnsys clients</title>
        <style type="text/css">

        body { background: #fff; font-size: 0.7em; }
        a:link, a:visited { text-decoration: none; color:#900; font-weight: bold; }
        a:hover, a:active { color:#d90; }

        h1 {
                font-size: 1.5em;
                width: 100%;
                vertical-align: bottom;
                color: #555;
                background: transparent url('images/opengnsys.png')  no-repeat top left;
                padding: 2em 0 1.5em 12em;
                margin-bottom: 1em;
        }

        div.admin {
                margin-left: 5em;
                float: left;
        }

        </style>
</head>

<body>
        <h1>Option Menu</h1>

        <div class="admin">
            <p>Link to return to the public zone</p>
            <a href="../varios/menucliente.php?iph=<?php echo $ip ?>">Return</a>
        </div>
</body>
</html>
