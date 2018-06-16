<?php
//print_r($_POST);
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
            if($newStatus == "closed"){
                $data = array_map(function($data) {
                    return stristr($data,'        "signups" =>') ? "        \"signups\" => \"closed\",\n" : $data;
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
            if($newStatus == "closed"){
                require_once(DATABASE_FUNCTIONS);
                $conn = getNewConnection();
                $res = $conn->query("select distinct name from users where teamID in (select id from memberCount where membercount < 4 or membercount is null) and to_date is null");
                
                $res_users = $res->fetch_all();
                echo "<div>Moving " . $res_users->num_rows . "participants. Usernames:<ul> ";
                foreach($res_users as $r){
                    echo "<li>" . $r[0] .  "</li>";
                }
                echo "</ul></div>";
                $conn->query("update users set teamID = 1 where teamID in (select id from memberCount where membercount < 4 or membercount is null) and to_date is null ");
               printf("Moved %d participants to grandstand<br/>" , $conn->affected_rows);
                
                $res = $conn->query("SELECT distinct contentnotes from signups where to_date is null and contentnotes <> ''");
                
                $contenttags = "";
                
                
                $restags = $res->fetch_all();
                foreach($restags as $r){
                    $contenttags = $contenttags . $r[0] . "\n";
                }
                $resrows = $res->fetch_all();
                
                echo "Final content tag list:<div><textarea style='width:100%;height:10em;'>$contenttags</textarea></div>";
                $cf = fopen("content_tags.txt", "w");
                fwrite($cf, $contenttags);
                fclose($cf);
                
            }
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
        
        if($_POST["close-signups"] == "second-pass"){
            changeEventStatus(TRUE, $_POST["new-status"]);
            
        }
    } else {
        echo "<form><input type='hidden' value='first-pass' name='clear-all'><button type='submit' name='clear-all-button' formmethod='post'>Clear all event signups/users/teams?</button></form>";
        echo "<form><input type='hidden' value='first-pass' name='event-status'><button type='submit' name='clear-all-button' formmethod='post'>Start team switch period (1)</button></form>";        echo "<form><input type='hidden' value='first-pass' name='close-signups'><button type='submit' name='clear-all-button' formmethod='post'>Close signups</button></form>";
    }

    
?>