<?php

    $sessionStart = session_start();
    if($sessionStart !== TRUE){
        echo "<div>something is iffy, you may need to check your signup goes through by emailing icie or the mods</div>";
    } else {
        
        //if new session
        if(!$_SESSION["session_id"]){
            $_SESSION["session_id"] = session_id();
        } else {
            session_id($_SESSION["session_id"]);
        }
    }
require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));
require_once(RESOURCE_PATH . "/pageFactory.php");

if($_GET['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
    
    require('class.openid.php');
	$openid = new SimpleOpenID;
	$openid->SetIdentity($_GET['openid_identity']);
	$openid_validation_result = $openid->ValidateWithServer();
	if ($openid_validation_result == true){
        
        $userurl =  $openid->GetIdentity();
        
        //get just the dreamwidth ID
        $userurl = substr($userurl, strpos($userurl, "//") + 2, - (strlen($userurl) - strpos($userurl, ".dreamwidth.org") ));
        
        
        require_once(DATABASE_FUNCTIONS);
        $conn = getNewConnection();
        if($userSignedUp = checkSignedUp($conn, $userurl)){
        
            $_SESSION["DWuser"] = $userurl;
            $_SESSION["loggedin"] = TRUE;
            if(in_array($userurl, CONFIG_ARRAY["event"]["mods"])){
                $_SESSION["admin"] = TRUE;
            } 
        }
        

        
	}
}

$uri = $_SERVER["REQUEST_URI"];
    $page_load = "homepage.php";
    if(strpos($uri, "/login") === 0){
        $page_load = "login.php";
    }
    if(strpos($uri, "/signup") === 0){
        $page_load = "signup.php";
    }
    if(strpos($uri, "/teamswitch") === 0){
        $page_load = "signup.php";
    }
    if(strpos($uri, "/roster") === 0){
        $page_load = "roster.php";
    }
    if(strpos($uri, "/admin") === 0){
        if($_SESSION["admin"]) $page_load = "admin.php";
        else $page_load = "notadmin.php";
    }
    if(strpos($uri, "/grandstand") === 0){
        $page_load = "grandstand.php";
    }
if(strpos($uri,"/teamswitch") === 0){
    if($_SESSION["loggedin"]){
        $page_load = "signup.php";
    } else {
        $page_load = "login.php";
        $_SESSION["loginneeded"] = true;
    }

}

    if(strpos($uri, "/userprofile") === 0){
        
        if($_SESSION["loggedin"]) {
            $page_load = "userprofile.php";
            
        }else {
            $page_load = "login.php";
            $_SESSION["loginneeded"] = true;
        }
    }
    if(strpos($uri, "/logout") === 0){
        session_start();
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
        session_regenerate_id(true);
        $page_load = "logout.php";
    }
    
    $variables = array(
        //'setInIndexDotPhp' => $setInIndexDotPhp
    );
    renderLayoutWithContentFile($page_load, $variables);
?>
