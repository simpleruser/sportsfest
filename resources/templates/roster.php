<?php
    require_once(DATABASE_FUNCTIONS);
    $team_count = $participant_count = $roster_div = "";

    $conn = getNewConnection();
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    $res = $conn->query("SELECT * from memberCount where name <> 'Grandstand' and exists (select 1 from users where memberCount.id = users.teamID and to_date is null) order by id");
    $teams = "";
    if($res->num_rows > 0){
        $teams = $res->fetch_all();
    }
 
    
    $res = $conn->query("select `name`, `teamID`, case when `over18` = 0 then '*' else '' END `over18`, `captain`, `vice_captain` from `users` where `to_date` is null and `teamID` <> 1 order by `teamID`, `captain`, `name`");
    if($res->num_rows > 0){
        $participants = $res->fetch_all();
    }
    $i = 0;  
//print_r($teams);
    foreach($teams as &$team){
        $team["members"] = array();
        while($i < count($participants) && $participants[$i][1] == $team[0]){
//            echo "participant $i captain '" . $participants[$i][3] . "' ";
            if($participants[$i][3] == 1){
                $team["captain"] = $participants[$i][0] . $participants[$i][2]; 
            }
//            echo "<br/>";
            array_push( $team["members"], $participants[$i]);
            $i = $i + 1;
        }
    }
    unset($team);

    $res = $conn->query("select count(distinct `name`) as count from `users` where `to_date` is null");
    if($res->num_rows > 0){
        $participant_count = $res->fetch_assoc()["count"];
    }

    $res = $conn->query("select count(distinct `name`) as count from `users` where `to_date` is null and teamID = 1");
    if($res->num_rows > 0){
        $grandstand_count = $res->fetch_assoc()["count"];
    }
    if(!($grandstand_count > 0)) $grandstand_count = "0";
    

	?><h1>
	Sports Fest 2018 Team Roster
	</h1>
<div id="roster">
<p class="tally">
	There are currently <strong><?php 
        echo count($teams);
    
    ?> teams</strong> and <strong><?php
        echo $participant_count;
    ?> participants</strong>.
</p>
<?php
    if(CONFIG_ARRAY["event"]["signups"] == "open") echo '<p class="signup">Join in by <a href="/signup">signing up</a>!</p>';
?>

<p class="tally"> An asterisk (*) indicates that a participant is a minor.</p><div class="inner">
<div class="team"><div class="title"><span>Grandstand</span><span><span class="fandom"></span><span class="member-count">(<?php
    echo $grandstand_count;
    ?>/&infin;)</span></span></div><ul class="members"><li class="captain">Mod team</li><li><a href="/grandstand">Grandstand members</a></li></ul></div>
<?php

    function nummemberscomp($a,$b){
        $amc = $a[5];
        $bmc = $b[5];
        if($amc == $bmc) return 0;
        return $amc < $bmc? -1:1;

    }
    
    usort($teams, "nummemberscomp");
    
    foreach($teams as &$team){
        $captain = "";
        if ($team[2]) $team[1] = $team[2] . " &mdash; " . $team[1];
        $membersCount = count($team["members"]);
        $teamStatus = "";
        if ($membersCount >= 8) $teamStatus = " team-full";
        if ($membersCount < 8 && $membersCount >= 4) $teamStatus = " team-valid";
//        echo "fandoms: team 1 " . $team[1] . " team 5: " . $team[6];
        if($team[1] == $team[6]) $team[6] = "";
        $team["html"] = "<div class='team$teamStatus'><div class='title'><span>" . $team[1] . "</span><span><span class='fandom'>" . $team[6] . "</span><span class='member-count'>(" . $team[5] . "/8)</span></span></div><ul class='members'>";
        $captain = $team["captain"];
        if (!$team["captain"]) $captain = "<span class='no-captain'>No captain, you could volunteer!</span>";
        $team["html"] = $team["html"] . "<li class='captain'>" . $captain . "</li>";
        
        foreach($team["members"] as $member){
            
            if($member[0] . $member[2] != $captain) $team["html"] = $team["html"] . "<li>" . $member[0] . $member[2] . "</li>";
        }
        echo $team["html"] . "</ul></div>";
        
    }
    unset($team);
    echo "</div>";
?>
</div>
