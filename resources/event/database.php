<?php
    require_once(realpath(dirname(__FILE__) . "/../config.php"));
    function teams(){
        $conn = getNewConnection();
        if ($conn->connect_error) {
            die('Could not connect: ' . mysqli_error($con));
        }
        $sqlq = "select teamslist.id, teamslist.name, teamslist.displayname, count(userTeams.teamID) memberCount from (select COALESCE(teamNick, name) as name, COALESCE(concat(teamNick, ' &mdash; ', name), name) as displayname, id from teams where valid_to is null and name <> 'Grandstand') teamslist left join (select teamID from users where to_date is null) as userTeams on teamslist.id = userTeams.teamID group by teamslist.id, teamslist.name, teamslist.displayname  order by teamslist.name";
        $res = $conn->query($sqlq);
        $teams = array();
        if($res->num_rows > 0){
            while($row = $res->fetch_assoc()) {
                array_push($teams, array(name => $row["name"],memberCount => $row["memberCount"], displayname=> $row["displayname"]));
                
            }
            
        }
        return $teams;
        
        
    }
function checkSignedUp($conn, $username){
    $res = $conn->query("select name from users where to_date is null and name = '$username'");
    if($res->num_rows > 0) return true;
    else return false;
}

    function parse_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlentities($data, ENT_QUOTES);
        return $data;
    }
//
//        record_signup($loadedForm["username"]
//                      , $loadedForm["email"]
//                      , $loadedForm["over18"]
//                      , $loadedForm["newTeam"]
//                      , $loadedForm["teamType"]
//                      , $loadedForm["newteamname"]
//                      , $loadedForm["characters"]
//                      , $loadedForm["fandom"]
//                      , $loadedForm["captain"]
//                      , $loadedForm["contentNotes"]
//                      , $loadedForm["rulesCheck"]
//                      , $loadedForm["teamNick"]
//                      , session_id()
//                     );
//
    function record_signup($username, $email, $over18, $newteam, $teamType, $newteamname, $characters, $fandom, $captain, $contentnotes, $rulesCheck, $teamNick, $sessionID){
        
        $sqlQ = "INSERT INTO `signups` (username, email, over18, newteam, teamType, newteamname, characters, fandom, captain, contentnotes, rulesCheck, sessionID, teamNick) VALUES ('$username', '$email', '$over18', '$newteam', '$teamType', '$newteamname', '$characters', '$fandom', '$captain', '$contentnotes', '$rulesCheck', '$sessionID', '$teamNick')";
        $conn = getNewConnection();
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        if ($conn->query("SELECT id from signups where username = '$username'")){
            $conn->query("UPDATE signups set to_date = sysdate() where username = '$username'");
        }
        if ($conn->query($sqlQ) === TRUE) {
            $_SESSION["signupID"] = $conn->insert_id;
            echo "Signup submitted, your signup ID is: " . $_SESSION["signupID"] . ". Please reference this number when contacting the mods or Icie if you run into issues.";
        } else {
            echo "Something has gone wrong recording your signup. Please contact Icie with the details.";
        }

        
        $conn->close();
        
    }
//
function retrieveSignup($conn,$signupID,$dwuser){
    $sqlQ = "SELECT * FROM `signups` where `id` = $signupID and `to_date` is NULL and `username` = '$dwuser' order by from_date desc";
    
    $res = $conn->query($sqlQ);
    if($res->num_rows === 0){
        return FALSE;
    } else {
        return $res->fetch_assoc();
    }
}

function updateSignupToDWValid($conn,$signup){
    $status = "verifiedusername";
    if ($status["status"] == "valid" || $signup["status"] == "validdetails"){
        $status = "valid";
    }
    $signup["status"] = $status;
    
    $sqlQ = "UPDATE `signups` SET `to_date` = SYSDATE() where `id` = " . $signup["signupID"] . " and `to_date` is NULL and `username` = '" . $signup["DWuser"] . "'";
    $conn->query($sqlQ);
    
    $sqlQ = "INSERT INTO `signups` (username, email, over18, newteam, teamType, newteamname, characters, fandom, captain, contentnotes, rulesCheck, sessionID, status, teamNick) VALUES ('". $signup["username"] ."', '" . $signup["email"] . "', '" . $signup["over18"] . "', '" . $signup["newteam"] . "', '" . $signup["teamType"] . "', '" . $signup["newteamname"] . "', '" . $signup["characters"] . "', '" . $signup["fandom"] . "', '" . $signup["captain"] . "', '" . $signup["contentnotes"] . "', '" . $signup["rulesCheck"] . "', '" . $signup["sessionID"] . "', '" . $status . "', '" . $signup["teamNick"] . "')";
    
    $res = $conn->query($sqlQ);
    
    $_SESSION["signupdetails"] = $signup;
    return $res;
}


function validateSignupUsername(){
//    echo "validate signup start";
    $_SESSION["signuperrors"] = array();
    $conn = getNewConnection();
    $signup = retrieveSignup($conn, $_SESSION["signupID"],  $_SESSION["DWuser"]);
    if($signup === FALSE){
        echo "Signup not found for id: " . $_SESSION["signupID"] . ". Please submit a new signup. If you continue to get this error, please contact Icie.";
        $conn->close();
        return FALSE;
    }
    
    updateSignupToDWValid($conn,$signup);
    $errors = array();
    
    if($signup["newteam"] == "yes"){
        
        if(CONFIG_ARRAY["event"]["signups"] == "open"){
            $newteamname = addNewTeam($conn, $signup);
        
            $signup["newteamname"] = $newteamname;
        } else{
            $_SESSION["signuperrors"]["teamerror"] = "Sorry, new team creation is currently closed!";
        }
        
    }
    
    $adduserres = addUser($conn, $signup);
    if($adduserres) {
        echo " You have been signed up!";
        if($_SESSION["signuperrors"].count > 0){
            echo " But there are still some errors, so you have been added to Grandstand. See below.";
        }
    } else {
        echo "There were issues recording your signup, check below.";
    }
    $conn->close();
    
//    echo "validate signup end";

    return TRUE;
        
     
}
//
function getTeam($conn, $teamname){
    $sqlQ = "SELECT * FROM `memberCount` where (name = '$teamname' OR nickname = '$teamname')";
    $res = $conn->query($sqlQ);
//    echo "<br/>sqlq get team $sqlQ<br/>";
    if($res->num_rows > 0){
        $team = $res->fetch_assoc();
        
        return $team;
    } else {
        $_SESSION["signuperrors"]["teamerror"] = "Error with adding you to team " . $teamname . " the team doesn't appear to exist.";
        return;
    }

}
//
function getTeamMembersByTeamID($conn, $teamID){
    $sqlQ = "SELECT name from users where to_date is null teamID = $teamID";
    $res = $conn->query($sqlQ);
    $members = array();
    if($res->num_rows >0){
        $i = 0;
        while($row = $res->fetch_assoc()){
            $members[$i] = $row["name"];
            $i = $i + 1;
        }
    }
    return $members;
}
//

function updateCaptain($conn, $teamID){
    if(!$teamID || $teamID == 1){return;}
    $sqlQ = "SELECT hasCaptain, capVolunteers, membercount from memberCount where id = $teamID";
    $res = $conn->query($sqlQ);
//    echo "captain set: $teamID";
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
//        print_r($row);
        $hasCap = $row["hasCaptain"];
        $numWilling = $row["capVolunteers"];
        $numMembers = $row["membercount"];
//        echo "hasCap $hasCap num willing $numWilling";
        if(!$hasCap == 1 && $numWilling > 0){
//            echo "here";
            $sqlQ = "select name from users where teamID = $teamID and to_date is null and captain_volunteer = 1 and from_date = (select min(from_date) from users where teamID = $teamID and to_date is null and captain_volunteer = 1)";
            $res = $conn->query($sqlQ);
//            echo "Oh god captain update: $sqlQ '";
//            echo $res->num_rows;
            if( $res->num_rows > 0){
//                echo "here";
                $newCaptain = $res->fetch_assoc()["name"];
                $sqlQ = "update users set captain = 1 where teamID = $teamID and name = '$newCaptain' and to_date is null";
                $res = $conn->query($sqlQ);
//                print_r($res);
            }
            
//            echo "'end";
        }
        
        if($numMembers === 0 || !$numMembers){
        
    echo "Previous team was $teamID and has $numMembers members";    
            $sqlQ = "update teams set valid_to = SYSDATE() where id = $teamID";
            $res = $conn->query($sqlQ);
//            print_r($res);
        }
    }
//    echo "vend";
}

function addUser($conn, $signup){
//    echo "<div>here</div>";
    if (!filter_var($signup["email"], FILTER_VALIDATE_EMAIL)) {
         $_SESSION["signuperrors"]["email"] = "Please enter a valid email! If you believe this message is in error, contact Icie.";
        return false;
    }
    $previousTeam = FALSE;
    $sqlQ = "SELECT teamID FROM `users` WHERE `users`.`name` = '" . $signup["username"] . "' and `users`.`to_date` is null";
    $res = $conn->query($sqlQ);
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        $previousTeam = $row["teamID"];
    }
//   echo "previous team:'$previousTeam'";
    $sqlQ = "UPDATE `users` SET `to_date` = SYSDATE() WHERE `users`.`name` = '" . $signup["username"] . "'";
    $conn->query($sqlQ);
    if(CONFIG_ARRAY["event"]["signups"] === "open" || CONFIG_ARRAY["event"]["signups"] === "teamswitch"){
        $signup["teamid"] = 1;
        if($signup["newteam"] == "grandstand"){
            $signup["newteamname"] = "Grandstand";
        }
        $captainwilling = "0";
        $setcaptain = "0";
        if($signup["newteamname"] != "Grandstand"){
            $teamDetails = getTeam($conn, $signup["newteamname"]);
            if($teamDetails && $teamDetails["memberCount"] >= 8){
                $teamMembers = getTeamMembersByTeamID($conn, $teamDetails["id"]);
                if(!in_array($signup["username"], $members)){
                    $_SESSION["signuperrors"]["teamfull"] = "The team you are trying to join is full!";
                    $signup["newteamname"] = "Grandstand";
                }
            }
            if($signup["newteamname"] != "Grandstand") {
                $signup["teamid"] = $teamDetails["id"];
                if($signup["captain"] == "yes"){ 
                    $captainwilling = "1";

                    if($teamDetails["hasCaptain"] != 1) $setcaptain = "1";
                                               }

            }

        }
    } else {
        $signup["teamid"] = $previousTeam ? $previousTeam : 1;
        if(!$previousTeam){
            $_SESSION["signuperrors"]["teamerror"] = "Sorry, team switching is closed! If you wish to leave the event, you can do so on <a href='/userprofile'>your profile</a>";
        }
    }
    
    $signup["over18"] = $signup["over18"] == "yes"? "1" : "0";
//    print_r($signup);
    
    $sqlQ = "INSERT INTO `users` (`name`, `dwvalidated`, `teamID`, `over18`, `email`, `captain`, `captain_volunteer`) VALUES ( '" . strtolower($signup["username"]) . "', '1', '" . $signup["teamid"] . "', '" . $signup["over18"] . "', '" . $signup["email"] . "',$setcaptain,$captainwilling)";
//    echo "<br/>SQL: $sqlQ";
    $res = $conn->query($sqlQ);
    if($previousTeam){
        updateCaptain($conn,$previousTeam);
    }
    return $res;
}
//
function getSignupByID(){
    if($_SESSION["signupID"] != null){
        $conn = getNewConnection();
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        $sqlQ = "SELECT * FROM `signups` where `id` = " . $_SESSION["signupID"] . " and `to_date` is NULL";
        $res = $conn->query($sqlQ);
//        print_r($res);
        if($res->num_rows > 0){
            $row = $res->fetch_assoc();
            return $row;
        } else {
            echo "Signup not found for id: " . $_SESSION["signupID"] . ".";
        }
        
        $conn->close();
    }
}
function getSignupByUser(){

    if($_SESSION["DWUser"] != null){
        $conn = getNewConnection();
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        $sqlQ = "SELECT * FROM `signups` where `id` = " . $_SESSION["DWuser"] . " and `to_date` is NULL";
        $res = mysqli_query($conn,$sqlQ);
//        print_r($res);
        if($res->num_rows > 0){
            $row = $res->fetch_assoc();
            return $row;
        } else {
            echo "Signup not found for user: " . $_SESSION["DWuser"] . ".";
        }
        
        $conn->close();
    }
}
//
function getSignupBySession(){
    $conn = getNewConnection();
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sqlQ = "SELECT * FROM `signups` where `sessionID` = " . session_id() . " and `to_date` is NULL";
    $res = $conn->query($sqlQ);
//    print_r($res);
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        return $row;
    } else {
        echo "Signup not found for session: " . session_id() . ".";
    }

    $conn->close();
    
    
}

function getNewConnection(){
        $sqlUsername = CONFIG_ARRAY["db"]["db1"]["username"];
        $sqlPass = CONFIG_ARRAY["db"]["db1"]["password"];
        $sqlHost = CONFIG_ARRAY["db"]["db1"]["host"];
        $sqlDBName = CONFIG_ARRAY["db"]["db1"]["dbname"];
        return new mysqli($sqlHost, $sqlUsername,$sqlPass,$sqlDBName);
}

function fandomParsing($fandom){
    $fanlower = strtolower($fandom);
    if(strpos($fanlower, "stella women") === 0 || strpos(strtolower($fandom), "stella women") > 0 ){
//        echo "fandom in '$fandom'";
        $fandom = preg_replace("/(Stella Women.*,.*),?/i", "Stella",$fandom);
//                echo "fandom out '$fandom'";

    }
    return $fandom;
}

function addNewTeam($conn, $signup){
//    print_r($signup);
    $username = $signup["username"];
    $teamType = $signup["teamType"];
    $characters = $signup["characters"];
    $fandom = $signup["fandom"];
    $fandom = fandomParsing($fandom);
    $captain = $signup["captain"];
    $teamNick = $signup["teamNick"];
    $addTeamResult = array();
    $charArray = explode(",",$characters);
    foreach($charArray as &$char){
        $char = ucwords(trim($char));
    }
    unset($char);
    sort($charArray);
    

    $charString = implode(", ",$charArray);
//echo "$fandom";
    $fandomArray = explode(",", $fandom);
    foreach($fandomArray as &$fandom){
        $fandom = trim($fandom);
//        echo "fandom: $fandom";
    }
    sort($fandomArray);
    $emptycount = 0;
    foreach($fandomArray as &$fd){
        if(strlen($fd) > 0){
            if(!in_array(strtolower($fd),CONFIG_ARRAY["event"]["fandomlower"])) {

                $_SESSION["signuperrors"]["fandomerror"] = "The fandom '$fd' is not a fandom that has been nominated for the event, you will be added to Grandstand.";
                return "Grandstand";
            }
            if (strtolower($fd) == "stella"){
                $fd = "Stella Women&apos;s Academy, High School Division Class C³";
                $fd = parse_input($fd);
            }
        } else {
            $emptycount++;
        }
        
    }
    unset($fd);
//    echo "fandom array count: " . count($fandomArray);
//    print_r($fandomArray);
    $fandomString = implode(", ",$fandomArray);
    if(count($fandomArray) == 0 || count($fandomArray) - $emptycount == 0 ){
        $_SESSION["signuperrors"]["fandomerror"] = "You must list at least one valid fandom. You'll be added to Grandstand.";
            return "Grandstand";
    }
    
    if(count($charArray) != 1 && $teamType == "solo"){
        $_SESSION["signuperrors"]["charactererror"] = "You must list exactly one character for a solo team! You've been added to Grandstand.";
            return "Grandstand";
    }
    
    
    if((count($charArray) < 2 || count($charArray) > 4) && $teamType == "ship"){
        $_SESSION["signuperrors"]["charactererror"] = "You need between 2 and 4 characters for a ship team! You've been added to Grandstand.";
            return "Grandstand";
    }
    
    
    if((count($charArray) < 5) && $teamType == "group"){
        $_SESSION["signuperrors"]["charactererror"] = "You need 5 or more characters for a group team! You've been added to Grandstand.";
            return "Grandstand";
    }
    
    $_SESSION["signupdetails"]["fandom"] = $fandomString;    
    $_SESSION["signupdetails"]["characters"] = $charString;

//    echo "<br/><br/>Fandoms: $fandomString<br/>Characters: $charString";
    $sqlQ = "select `name` from teams where (characters = '$charString' and fandom = '$fandomString') or (teamNick = '$teamNick') and valid_to is null";
//    echo "<div>SQL: $sqlQ</div>";
    $teamExists = $conn->query($sqlQ);
    if($teamExists->num_rows > 0){
        $_SESSION["signuperrors"]["teamerror"] = "This team already exists! You have been added to team " . $rw["name"];
        $rw = $teamExists->fetch_assoc();
//        echo "here!! ";
//        echo $rw["name"];
        return $rw["name"];
    } else {
        
        $captain = $captain == "yes" ? "1" : "0"; 
            $teamName = "";
            if ($teamType == "ship"){
                $teamName = implode(", ",$charArray);
            }
            if ($teamType == "solo"){
                $teamName = $characters;
            }
            if ($teamType == "group"){
                $teamName = implode(", ",$charArray);
            }
            if ($teamType == "sports"){
                $teamName = $fandom;
            }
        
        if(strlen($teamNick) > 0) $teamNick = "'$teamNick'";
        else $teamNick = "null";
        
        $res = $conn->query("insert into teams (name, teamNick, type,characters, fandom,hasCaptain, hasViceCaptain) values('$teamName', $teamNick,'$teamType', '$charString', '$fandomString', $captain, 0)");
        if ($res){
            return $teamName;
        } else {
            $_SESSION["signuperrors"]["addteamerror"] = "There was an error adding the new team. Try again and then contact Icie if it keeps happening.";
            return "Grandstand";
        }
//    
    }
}

?>