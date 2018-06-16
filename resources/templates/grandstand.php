<?php


    require_once(DATABASE_FUNCTIONS);
    
    $conn = getNewConnection();
    $sqlQ = "SELECT distinct name from users where teamID = 1 and to_date is null order by name";
    echo "<h1>Grandstand Roster</h1>";
    $res = $conn->query($sqlQ);
    if($res->num_rows > 0){
        echo "<div class='tally'>There are " . $res->num_rows . " participants signed up for grandstand!</div>";
        echo "<div id='roster'><div class='inner'>";
        $users = array("0&mdash;9" => array());
        while($row = $res->fetch_assoc()){
            $name = $row["name"];
            $firstLetter = $name[0];
            if($firstLetter < 'a'){
                array_push($users["0&mdash;9"],$name);
            } else{
                if(!$users[$firstLetter]){
                    $users[$firstLetter] = array($name);
                } else{
                    array_push($users[$firstLetter], $name);
                }
            }
        }
        if(count($users["0&mdash;9"]) == 0) unset($users["0&mdash;9"]);
        foreach($users as $key => $userarray){
            echo "<div class='user-group team'><div class='title'>$key</div><ul class='user-list members'>";
            foreach($userarray as $user){
                echo "<li>$user</li>";
            }
            echo "</ul></div>";
        }
        echo "</div></div>";
    }
    

?>