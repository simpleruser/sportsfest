
<div><form action="http://sportsfest.iciely.com/login" method="post" onsubmit="this.login.disabled=true;">
<?
//print_r($_SESSION);
//print_r($_POST);
//print_r($_GET);

require_once('class.openid.php');

if($_SESSION["loginneeded"]){
    echo "<div class='error'>To access the requested page you need to login with a signed up dreamwidth account!</div>";
    $_SESSION["loginneeded"] = false;
    $_SESSION["requesteddest"] = $_SERVER["REQUEST_URI"];
}
//echo "here1";
    require_once(DATABASE_FUNCTIONS);
//echo "here";
if ($_POST['openid_action'] == "login"){ // Get identity from user and redirect browser to OpenID Server
    $conn = getNewConnection();
    
    $userSignedUp = checkSignedUp($conn, $_POST['openid_url']);
    if(!$userSignedUp){
        echo "<div class='error'>There is no signup for the dreamwidth username " . $_POST['openid_url'] . ". Please <a href='/signup'>sign up</a> before logging in. If you believe this is in error, bug icie.</div>";
    } else{
    $_SESSION["requestedlogin"] = $_POST['openid_url'];
	$openid = new SimpleOpenID;
    
	$openid->SetIdentity(trim($_POST['openid_url']) . ".dreamwidth.org");
	$openid->SetTrustRoot('http://' . $_SERVER["HTTP_HOST"]);
	$openid->SetRequiredFields(array('email','fullname'));
	$openid->SetOptionalFields(array('dob','gender','postcode','country','language','timezone'));
	if ($openid->GetOpenIDServer()){
		$openid->SetApprovedURL('http://sportsfest.iciely.com/login');  	// Send Response from OpenID server to this script
		$openid->Redirect(); 	// This will redirect user to OpenID Server
	}else{
		$error = $openid->GetError();
		echo "ERROR CODE: " . $error['code'] . "<br>";
		echo "ERROR DESCRIPTION: " . $error['description'] . "<br>";
	}
	exit;}
}
else if($_GET['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
    
	$openid = new SimpleOpenID;
	$openid->SetIdentity($_GET['openid_identity']);
	$openid_validation_result = $openid->ValidateWithServer();
	if ($openid_validation_result == true){ 		
        $_SESSION["loggedin"] = true;
        $userurl = $_GET['openid_identity'];
        $userurl = substr($userurl, strpos($userurl, "//") + 2, - (strlen($userurl) - strpos($userurl, ".dreamwidth.org") ));
        
        $_SESSION["DWuser"] = $userurl;
        $_SESSION["loggedin"] = TRUE;
        if(in_array($userurl, CONFIG_ARRAY["event"]["mods"])){
            $_SESSION["admin"] = TRUE;
        }
        $conn = getNewConnection();
        $res = $conn->query("select * from users where name = '$userurl' and to_date is null");
        if($res->num_rows > 0) $_SESSION["userdetails"] = $res->fetch_assoc();
//        print_r($_SESSION["userdetails"]);
        echo "Dreamwidth user $userurl signed in.<br/><br/>";
        
        if( $_SESSION["requesteddest"]){
            
//            echo $_SESSION["requesteddest"];
            $tourl = $_SESSION["requesteddest"];
            $_SESSION["requesteddest"] = false;
 	echo " You will be redirected to the requested page shortly. Click <a href='$tourl'>here</a> to go now.";
echo"<script>
//Using setTimeout to execute a function after 5 seconds.
setTimeout(function () {
   //Redirect with JavaScript
   window.location.href= 'http://sportsfest.iciely.com$tourl';
}, 5000);
</script>";
        }

	}else if($openid->IsError() == true){			// ON THE WAY, WE GOT SOME ERROR
		$error = $openid->GetError();
		echo "ERROR CODE: " . $error['code'] . "<br>";
		echo "ERROR DESCRIPTION: " . $error['description'] . "<br>";
	}else{											// Signature Verification Failed
		echo "INVALID AUTHORIZATION";
	}
}else if ($_GET['openid_mode'] == 'cancel'){ // User Canceled your Request
	echo "USER CANCELED REQUEST";
}
?>

<div><span class='field'>Your dreamwidth ID </span><input type="hidden" name="openid_action" value="login">
<input type="text" name="openid_url" class="openid_login"<?php
            echo "value='$userurl'";
       ?>>
       
       <input type="submit" name="login" value="login &gt;&gt;"></div>
</form>
</div>
