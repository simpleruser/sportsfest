<?php
    require_once(realpath(dirname(__FILE__) . "/../config.php"));

?>
<!DOCTYPE html>
    <html>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <head>
            <title>
            <?php
                if (isset($title)){
                    echo $title;
                } else {
                    echo CONFIG_ARRAY["misc"]["default_title"];
                }
                ?>
            </title>
            <link rel="stylesheet" type="text/css" href= "<? if(isset($style)) echo $style;
                  else echo CONFIG_ARRAY["paths"]["css"]["default"];
                                                          ?>">
        </head>
        <body>
        <div><div class="header"><div><a href="https://sportsfest.dreamwidth.org/">Sportsfest 2018</a> &mdash; a sports anime fanwork event. </div><div id="navigation"><a href="/">home</a><a href="/roster">roster</a><?php
                if (CONFIG_ARRAY["event"]["signups"] === "open"){echo "<a href='/signup'>signup</a>";}
                if ($_SESSION["loggedin"]){
                    echo "<a href='/userprofile'>" . $_SESSION["DWuser"] . "</a>";
                    if ($_SESSION["admin"]){
                        echo "<a href='/admin'>admin</a>";
                    }
                    echo "<a href='/logout'>logout</a>";
                } else {
                    echo "<a href='/login'>login</a>";
                }
                
            ?></div></div></div>