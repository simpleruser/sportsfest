<?php

    function clearTables($confirm){
        if($confirm === TRUE){
            
            require_once(DATABASE_FUNCTIONS);
            $conn = getNewConnection();
            $conn->query("delete from users where 1 = 1");
            $conn->query("delete from teams where id <> 1");
            $conn->query("delete from signups where 1 = 1");
            echo "All cleared!";
        }
    }

    function changeEventStatus($confirm, $newStatus){
        if($confirm === TRUE){
            
            
            $file = '../resources/config.php';
            $newStr = '        "signups" => "' . $newStatus . '",\n';
            $data = file($file); // reads an array of lines
            if($newStatus == "teamswitch"){
                $data = array_map(function($data) {
                    return stristr($data,'        "signups" =>') ? "        \"signups\" => \"teamswitch\",\n" : $data;
                }, $data);
            }
            file_put_contents($file, implode('', $data));
            
            echo "Updated event status to $newStatus";
//            
//            
//            rename($oldfile, $tmpfile);
//            if (!rename($newfile, $oldfile)) {
//                echo "failed to rename $newfile...\n";
//            }
        }
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //print_r($_POST);
        if($_POST["clear-all"] == "first-pass") echo "<form><input type='hidden' value='second-pass' name='clear-all'><button type='submit' name='clear-all-button' formmethod='post'>REALLY clear all event signups/users/teams??? There is no going back!</button></form>";
        if($_POST["clear-all"] == "second-pass"){
            clearTables(TRUE);
            
        }
        
        if($_POST["event-status"] == "first-pass") echo "<form><input type='hidden' value='second-pass' name='event-status'><input type='hidden' value='teamswitch' name='new-status'><button type='submit' name='clear-all-button' formmethod='post'>Confirm change the event status to 'Team switch'</button></form>";
        if($_POST["event-status"] == "second-pass"){
            changeEventStatus(TRUE, $_POST["new-status"]);
            
        }
        
        
        if($_POST["close-signups"] == "first-pass") echo "<form><input type='hidden' value='second-pass' name='close-signups'><input type='hidden' value='closed' name='new-status'><button type='submit' name='clear-all-button' formmethod='post'>Confirm change the event status to 'Closed signups'</button></form>";
        if($_POST["event-status"] == "second-pass"){
            changeEventStatus(TRUE, $_POST["new-status"]);
            
        }
    } else {
        echo "<form><input type='hidden' value='first-pass' name='clear-all'><button type='submit' name='clear-all-button' formmethod='post'>Clear all event signups/users/teams?</button></form>";
        echo "<form><input type='hidden' value='first-pass' name='event-status'><button type='submit' name='clear-all-button' formmethod='post'>Start team switch period (1)</button></form>";        echo "<form><input type='hidden' value='first-pass' name='close-signups'><button type='submit' name='clear-all-button' formmethod='post'>Close signups</button></form>";
    }

    
?>