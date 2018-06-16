
    <form method="post"  accept-charset="UTF-8">
        
<?php
    require_once('class.openid.php');
//        echo "here1";
    require_once(DATABASE_FUNCTIONS);
//        echo "here2";
//    echo "<div><p>Session</p>";
//    print_r($_SESSION);
//    echo "</div>";
//        print_r($_SESSION);
    $formSubmit = $username = $email = $over18 = $newteam = $teamType= $newteamname = $characters = $fandom = $captain = $contentnotes = $rulesCheck = $teamNick = "";
    $loadedForm = array();
    $_SESSION["signuperrors"] = array();
        
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $formSubmit = TRUE;
        $loadedForm = array("username" =>  parse_input($_POST["username"])
                            , "email" => parse_input($_POST["email"])
                            , "over18" =>  parse_input($_POST["over18"])
                            , "newteam" => parse_input($_POST["new-team"])
                            , "teamType" => parse_input($_POST["teamType"])
                            , "newteamname" => parse_input($_POST["team"])
                            , "characters" => parse_input($_POST["characters"])
                            , "fandom" => parse_input($_POST["fandom"])
                            , "captain" => parse_input($_POST["captain"])
                            , "contentnotes" => parse_input($_POST["contentnotes"])
                            , "rulesCheck" => parse_input($_POST["rules-check"])
                            , "teamNick" => parse_input($_POST["teamNick"])
                        );

    //submit signup details to server regardless of validity
//        record_signup($username, $email, $over18, $newteam, $teamType, $newteamname, $characters, $fandom, $captain, $contentnotes, $rulesCheck, session_id());
        record_signup($loadedForm["username"]
                      , $loadedForm["email"]
                      , $loadedForm["over18"]
                      , $loadedForm["newteam"]
                      , $loadedForm["teamType"]
                      , $loadedForm["newteamname"]
                      , $loadedForm["characters"]
                      , $loadedForm["fandom"]
                      , $loadedForm["captain"]
                      , $loadedForm["contentnotes"]
                      , $loadedForm["rulesCheck"]
                      , $loadedForm["teamNick"]
                      , session_id()
                     );
        $_SESSION["signupdetails"] = $loadedForm;
      
    } 
        
        $loadedForm =  $_SESSION["signupdetails"];

        $username = $loadedForm["username"];
        $email = $loadedForm["email"];
        $over18 = $loadedForm["over18"];
        $newteam = $loadedForm["newteam"];
        $teamType = $loadedForm["teamType"];
        $newteamname = $loadedForm["newteamname"];
        $characters = $loadedForm["characters"];
        $fandom = $loadedForm["fandom"];
        $captain = $loadedForm["captain"];
        $contentnotes = $loadedForm["contentnotes"];
        $rulesCheck = $loadedForm["rulesCheck"];
        $teamNick = $loadedForm["teamNick"];   
    
        
if ($_POST['openid_action'] == "login" && !($loadedForm["username"] == $_SESSION["DWuser"] && $_SESSION["loggedin"])){ // Get identity from user and redirect browser to OpenID Server

	$openid = new SimpleOpenID;
    $returnUrl = CONFIG_ARRAY["urls"]["baseUrl"] . "/signup";
    $username = trim($loadedForm["username"]);
    $username = strtolower(preg_replace("(_)","-",$username));
    $identity = $username . ".dreamwidth.org";
	$openid->SetIdentity($identity);
	$openid->SetTrustRoot('http://' . $_SERVER["HTTP_HOST"]);
	$openid->SetRequiredFields(array('email','fullname'));
	$openid->SetOptionalFields(array('dob','gender','postcode','country','language','timezone'));
	if ($openid->GetOpenIDServer()){
		$openid->SetApprovedURL($returnUrl);  	// Send Response from OpenID server to this script
		$openid->Redirect(); 	// This will redirect user to OpenID Server
	}else{
		$error = $openid->GetError();
        if($error['code'] == "OPENID_NOSERVERSFOUND"){
            echo "<div class='error'>Check dreamwidth ID! The username <a href='http://$username.dreamwidth.org' target=_blank>$username</a> does not appear to exist.</div>";
        } else{
            echo "<div class='error'>Oh no! Something's gone wrong with authenticating your dreamwidth ID. Contact Icie with your signup ID (" . $_SESSION["signupID"] .") and the following details:<br/>";
		      echo "ERROR CODE: " . $error['code'] . "<br>";
		      echo "ERROR DESCRIPTION: " . $error['description'] . "</div>";
        }
	}
} else if($_POST['openid_action'] == "login" && $loadedForm["username"] == $_SESSION["DWuser"] && $_SESSION["loggedin"]){
        if(strtolower($rulesCheck) === CONFIG_ARRAY["event"]["rulescheck"]){
            $validated = validateSignupUsername();
//            echo "<br/>Signupdetails: ";
//            print_r($_SESSION["signupdetails"]);
//            echo "<br/>errordetails: ";
//            print_r($_SESSION["signuperrors"]);
            
        } else{ $rulescheckerror = "Please check you have read and understood the rules and entered the check phrase correctly!";
               $_SESSION["signuperrors"]["rulescheck"] = $rulescheckerror;
        }
}
else if($_GET['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
	$openid = new SimpleOpenID;
	$openid->SetIdentity($_GET['openid_identity']);
	$openid_validation_result = $openid->ValidateWithServer();
	if ($openid_validation_result == true){
        
        $userurl =  $openid->GetIdentity();
        
        //get just the dreamwidth ID
        $userurl = substr($userurl, strpos($userurl, "//") + 2, - (strlen($userurl) - strpos($userurl, ".dreamwidth.org") ));
        
        $_SESSION["DWuser"] = $userurl;
        $_SESSION["loggedin"] = TRUE;
        if(in_array($userurl, CONFIG_ARRAY["event"]["mods"])){
            $_SESSION["admin"] = TRUE;
        }
        echo "Dreamwidth user $userurl signed in.";
        if(strtolower($rulesCheck) === CONFIG_ARRAY["event"]["rulescheck"]){
            $validated = validateSignupUsername();
//            echo "<br/>Signupdetails: ";
//            print_r($_SESSION["signupdetails"]);
//            echo "<br/>errordetails: ";
//            print_r($_SESSION["signuperrors"]);
            
        } else{ 
            $rulescheckerror = "Please check you have read and understood the rules and entered the check phrase correctly!";
            $_SESSION["signuperrors"]["rulescheck"] = $rulescheckerror;
        }
        $email = $_SESSION["signupdetails"]["email"];
        $over18 = $_SESSION["signupdetails"]["over18"];
        $newteam = $_SESSION["signupdetails"]["newteam"];
        $teamType = $_SESSION["signupdetails"]["teamType"];
        $newteamname = $_SESSION["signupdetails"]["newteamname"];
        $characters = $_SESSION["signupdetails"]["characters"];
        $fandom = $_SESSION["signupdetails"]["fandom"];
        $captain = $_SESSION["signupdetails"]["captain"];
        $contentnotes = $_SESSION["signupdetails"]["contentnotes"];
        $rulesCheck = $_SESSION["signupdetails"]["rulesCheck"];
        $teamNick = $_SESSION["signupdetails"]["teamNick"];
        
        $username = $userurl;
        
	}else if($openid->IsError() == true){			// ON THE WAY, WE GOT SOME ERROR
		$error = $openid->GetError();
        echo "Something went wrong, please contact Icie with the following details:";
		echo "ERROR CODE: " . $error['code'] . "<br>";
		echo "ERROR DESCRIPTION: " . $error['description'] . "<br>";
	}else{											// Signature Verification Failed
		echo "Authorization failed. Try again, then contact Icie if it still doesn't work.";
	}
}else if ($_GET['openid_mode'] == 'cancel'){ // User Canceled your Request
	echo "Cancelled dreamwidth authorization - make sure you authorize dreamwidth to pass through your credentials.";
}

        if(count($_SESSION["signuperrors"]) > 0){
            echo "<div id='errors'>There are errors with your signup! Check below.</div>";
        }
?>

        <input type="hidden" name="openid_action" value="login">
        <div id="username-question">
            <span class="field">Dreamwidth Username:</span>
            <span class="descrip">You need a <a href="https://www.dreamwidth.org/create">DW account</a>. Make sure it's <a href="http://www.dreamwidth.org/register">verified</a>!</span>
            <input name="username" type="text" <?php
                   if ($username){                      
                       echo "value=\"$username\"";

                   }
                   ?>/>
        </div>

        <div id="email-question">
            <?php
                if($_SESSION["signuperrors"]["email"]){
                    echo "<div class='error'>" . $_SESSION["signuperrors"]["email"] . "</div>";
                }
            ?>
            <span class="field">E-mail Address:</span>
            <input name="email" type="email" <?php
                   if ($email){
                       echo "value=\"$email\"";

                   }
                   ?>/>
        </div>

        <div id="age-question">
                <span class="field">Are you 18 or older?</span><span class="descrip"><br/> <a href="<?php
                    echo CONFIG_ARRAY["event"]["minorpolicy"];
                        ?>">Minor policy</a>. You may update this during the event if you turn 18.</span><br/>
                <label><input name="over18" value="yes" type="radio" <?php
                   if ($over18 === "yes" ){
                       echo "checked";
                   }
                   ?>/>Yes</label> <label><input name="over18" type="radio" value="no" <?php
                   if ($over18 === "no" || !$over18){
                       echo "checked";
                   }
                   ?> />No</label>
        </div>

        <div id="team-questions">
            <p class="field">Team:</p>
                <input name="new-team" value="grandstand" type="radio" id="grandstand-radio" <?php
                   if (!$newteam || $newteam === "grandstand"){
                       echo "checked";
                   }
                   ?>/><label class="team-label" for="grandstand-radio">Join grandstand </label>
                <input name="new-team" value="no" type="radio" id="join-radio" <?php
                   if ($newteam === "no"){
                       echo "checked";
                   }
                   ?>/><label for="join-radio" class="team-label">Join team </label>
                <input name="new-team" value="yes" type="radio" id="new-radio" <?php
                   if ($newteam === "yes"){
                       echo "checked";
                   }
                   ?>/><label for="new-radio" class="team-label">Start new team </label>   
                <div id="join-team-options">
                    <?php
                        if($_SESSION["signuperrors"]["teamerror"]){
                            echo "<div class='error'>Error: ";
                            echo $_SESSION["signuperrors"]["teamerror"];
                            echo "</div>";
                        }
                    
                        if($_SESSION["signuperrors"]["teamfull"]){
                            echo "<div class='error'>Error: ";
                            echo $_SESSION["signuperrors"]["teamfull"];
                            echo "</div>";
                        }
                    ?>
                    <p class="field">Pick a team to join:</p>
                    <select name="team">
                        <?php
                            //create list of teams that aren't full as options
                            $teams = teams();
                            if(count($teams) == 0){
                                echo "<option disabled selected>No available teams</option>";
                            } else{
                                echo "<option value = ''></option>";
                                foreach ($teams as $team){
                                    $name = $team["name"];
                                    $displayname = $team["displayname"];
                                    $memberCount = $team["memberCount"];
                                    if (!$memberCount) $memberCount = "0";
                                    echo "<option value='$name'";
                                    if($memberCount >= 8) echo " disabled"; 
                                    
                                    if ($newteamname === $name){
                                        echo " selected";
                                    }
                   
                                    echo ">$displayname ($memberCount/8)</option>";
                                } 
                            }


                        ?>
                    </select>
                </div>
                <div id="new-team-options"><p class="field">
                    
                    <?php
                        if($_SESSION["signuperrors"]["teamerror"]){
                            echo "<div class='error'>Error: ";
                            echo $_SESSION["signuperrors"]["teamerror"];
                            echo "</div>";
                        }
                        if($_SESSION["signuperrors"]["addteamerror"]){
                            echo "<div class='error'>Error: ";
                            echo $_SESSION["signuperrors"]["addteamerror"];
                            echo "</div>";
                        }
                    ?>
                    New team type:</p>
                   <input name="teamType" id="ship-team" type="radio" value="ship"<?php
                   if (!$teamType || $teamType === "ship"){
                       echo "checked";
                   }
                   ?>/><label for="ship-team">Ship Team</label>
                    
                    <input name="teamType" id="group-team" value="group" type="radio" <?php
                   if ($teamType === "group"){
                       echo "checked";
                   }
                   ?>/><label for="group-team">Group Team</label>
                    <input name="teamType" id="sports-team" value="sports" type="radio" <?php
                   if ($teamType === "sports"){
                       echo "checked";
                   }
                   ?>/><label for="sports-team">Show Team</label>
                    
                    
                   <input name="teamType" id="solo-team" value="solo" type="radio" <?php
                   if ($teamType === "solo"){
                       echo "checked";
                   }
                   ?>/><label for="solo-team">Solo Team</label>
                        
                    <div class="character-question"><p><span class="field">Team character(s):</span><br/><span class="descrip">Please write names according to Archive of Our Own's <a href="https://archiveofourown.org/wrangling_guidelines/7#Non-English">guidelines</a>, separated by commas. </span></p><input name="characters" type="text" <?php
                   if ($characters){
                       echo "value=\"$characters\"";
                   }
                   ?> /></div>
                       <?php
                         if($_SESSION["signuperrors"]["charactererror"]){
                            echo "<div class='error'>";
                            echo $_SESSION["signuperrors"]["charactererror"];
                            echo "</div>";
                        }
                        ?>
                    <div>
                        
                        <p class="field">What is your team's series?</p>
                        <p class="descrip">If you have a cross-fandom ship, please list all series, and separate them with commas. If the canon has multiple names or you are unsure of how to spell it, there is a list <a href="<?php
                                echo CONFIG_ARRAY["event"]["fandomlist"];
                            ?>">here</a>.</p>
                        <?php
                         if($_SESSION["signuperrors"]["fandomerror"]){
                            echo "<div class='error'>";
                            echo $_SESSION["signuperrors"]["fandomerror"];
                            echo "</div>";
                        }
                        ?>
                        <p><input name="fandom" type="text" <?php
                   if ($fandom){
                       echo "value=\"$fandom\"";
                   }
                   ?>/></p>
                    </div>
                         <p><span class="field">Team nickname:</span><span class="descrip"> (optional)</span><input name="teamNick" type="text" <?php
                   if ($teamNick){
                       echo "value=\"$teamNick\"";
                   }
                   ?> /></p>
                </div>

            <div id="captain-question">
                    <p class="field">Would you like to volunteer to be the team's <a href="<?php
                            echo CONFIG_ARRAY["event"]["captain"];
                        ?>" target="_blank">Captain</a>?</p>
                    <p>
                        <label><input name="captain" value="yes" type="radio" <?php
                   if ($captain === "yes"){
                       echo "checked";
                   }
                   ?>/>Yes </label>&nbsp; 
                        <label><input name="captain" value="no" type="radio" <?php
                   if (!$captain || $captain === "no"){
                       echo "checked";
                   }
                   ?>/>No</label>
                    </p>
                </div>
        </div>
        <div class="tags-box">
            <p><span class="field">Are there any important content tags that you would like to include in the event's content tag list?</span><span class='descrip'> For reference, our content tag list can be found here: <a href="<?php
                    echo CONFIG_ARRAY["event"]["taglink"];
                ?>" target="_blank">content tags list</a>.</span></p> 
            <textarea name="contentnotes" rows="5"><?php
                       echo $contentnotes;
                   
                   ?></textarea>
        </div>
<?php
                        if($rulescheckerror){
                            echo "<div class='error'>";
                            echo $rulescheckerror;
                            echo "</div>";
                        }
            ?>
        <p>
        
            <span class="field"><a href="<?php
                    echo CONFIG_ARRAY["event"]["participantguidelines"];
                ?>" target="_blank">Participant Agreement</a>'s rules check phrase:</span><br />
            <input name="rules-check" type="text" <?php
                       echo "value=\"$rulesCheck\"";
                   
                   ?>/>
        </p>

        <input type="submit" value="Sign up!">
    </form>

