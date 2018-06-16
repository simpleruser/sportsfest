<?php
$config = array(
    "db" => array(
        "db1" => array(
            "dbname" => "sportsfest2018",
            "username" => "sportsfestadmin",
            "password" => "sportsfesthell!",
            "host" => "sql.sportsfest.iciely.com"
        )
    ),
    "urls" => array(
        "baseUrl" => "http://sportsfest.iciely.com"
    ),
    "paths" => array(
        "resources" => $_SERVER["DOCUMENT_ROOT"] . "/resources",
        "css" => array(
            "default" => "/public/css/style.css"
        ),
        "images" => array(
            "content" => $_SERVER["DOCUMENT_ROOT"] . "/images/content",
            "layout" => $_SERVER["DOCUMENT_ROOT"] . "/images/layout"
        )
    ),
    "misc" => array(
        "default_title" => "Sports Festival 2018"
    
    ),
    "event" => array(
        "signups" => "open",
        "minorpolicy" => "https://sportsfest.dreamwidth.org/1735.html",
        "fandomlist" => "https://docs.google.com/spreadsheets/d/1rP6t6h1l7-By-xDsF3o991e8X1Gi0w10pspqg3K9k_A/edit#gid=0",
        "participantguidelines" => "https://sportsfest.dreamwidth.org/2122.html",
        "captain" => "https://sportsfest.dreamwidth.org/3910.html",
        "taglink" => "https://sportsfest.dreamwidth.org/3623.html",
        "rulescheck" => "ganbatte",

        "mods" => array("soveryaverageme",
"pugglemuggle",
"hydehog",
"uwu_anon",
"deducingontheroof",
"garciraki",
    "aicqt"

),
        "fandomlower" => array("15: meisetsu kougyou koukou rugby-bu"
,"all out!!"
,"aoharu x kikanjuu"
,"area no kishi"
,"ashita no joe"
,"azusa, otetsudai shimasu!"
,"baby steps"
,"ballroom e youkoso"
,"captain tsubasa"
,"chihayafuru"
,"clean freak! aoyama-kun"
,"cross game"
,"daiya no ace!"
,"days"
,"dive!!"
,"food wars!"
,"free!"
,"ginga e kickoff!!"
,"h2"
,"haikyuu!!"
,"hajime no ippo"
,"hanebado!"
,"hikaru no go"
,"inazuma eleven"
,"keijo!!!!!!!!"
,"kuroko no basket"
,"major"
,"one outs"
,"ookiku furikabutte"
,"ping pong: the animation"
,"prince of stride"
,"prince of tennis"
,"princess nine"
,"robot x laserbeam"
,"rookies"
,"saki"
,"slam dunk"
,"stella"                          
,"stella women’s academy, high school division class c³"
,"teppu"
,"tsuritama"
,"two car"
,"uma musume: pretty derby "
,"yowamushi pedal"
,"yuri!!! on ice")
        ,
        "fandom" => array("15: Meisetsu Kougyou Koukou Rugby-bu"
,"All Out!!"
,"Aoharu x Kikanjuu"
,"Area no Kishi"
,"Ashita no Joe"
,"Azusa, Otetsudai Shimasu!"
,"Baby Steps"
,"Ballroom e Youkoso"
,"Captain Tsubasa"
,"Chihayafuru"
,"Clean Freak! Aoyama-kun"
,"Cross Game"
,"Daiya no Ace!"
,"Days"
,"Dive!!"
,"Food Wars!"
,"Free!"
,"Ginga e Kickoff!!"
,"H2"
,"Haikyuu!!"
,"Hajime no Ippo"
,"Hanebado!"
,"Hikaru no Go"
,"Inazuma Eleven"
,"Keijo!!!!!!!!"
,"Kuroko no Basket"
,"Major"
,"One Outs"
,"Ookiku Furikabutte"
,"Ping Pong: The Animation"
,"Prince of Stride"
,"Prince of Tennis"
,"Princess Nine"
,"Robot x Laserbeam"
,"Rookies"
,"Saki"
,"Slam Dunk"
,"Stella"                          
,"Stella Women’s Academy, High School Division Class C³"
,"Teppu"
,"Tsuritama"
,"Two Car"
,"Uma Musume: Pretty Derby "
,"Yowamushi Pedal"
,"Yuri!!! On Ice")
    )
    
);

defined("CONFIG_ARRAY")
    or define("CONFIG_ARRAY", $config);
/*
    I will usually place the following in a bootstrap file or some type of environment
    setup file (code that is run at the start of every page request), but they work 
    just as well in your config file if it's in php (some alternatives to php are xml or ini files).
*/
 
/*
    Creating constants for heavily used paths makes things a lot easier.
    ex. require_once(LIBRARY_PATH . "Paginator.php")
*/
defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));

defined("RESOURCE_PATH")
    or define("RESOURCE_PATH", realpath(dirname(__FILE__) ));
     
defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

defined("DATABASE_FUNCTIONS")
    or define("DATABASE_FUNCTIONS", realpath(dirname(__FILE__) . '/event/database.php'));
/*
    Error reporting.
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);

?>
