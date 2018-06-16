<form>Icie is still building stuff, but for now here's the one option really needed here.<br/><br/>

<?php

    function clearTables($confirm){
        if($confirm === TRUE){
            
            require_once(DATABASE_FUNCTIONS);
            $conn = getNewConnection();
            $user = $_SESSION["DWuser"];
            $conn->query("update users set to_date = SYSDATE() where name = '$user'");
            echo "Okay, that's it! You've been dropped from the event. Thanks for stopping by!";
               session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
        session_regenerate_id(true);
            echo " You'll be redirected to the homepage shortly. Unless you have javascript turned off.";
           
echo"<script>
//Using setTimeout to execute a function after 5 seconds.
setTimeout(function () {
   //Redirect with JavaScript
   window.location.href= 'http://sportsfest.iciely.com';
}, 5000);
</script>";
        }
        }
    

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //print_r($_POST);
        if($_POST["clear-signup"] == "first-pass") echo "<input type='hidden' value='second-pass' name='clear-all'><button type='submit' name='clear-all-button' formmethod='post'>REALLY drop your signup?? If you lose your spot there is no going back!</button> If you just want to edit your details go to the <a href='/signup'>signup form</a> and resubmit it with the correct details.</form>";
        if($_POST["clear-all"] == "second-pass"){
            clearTables(TRUE);
            
        }
    } else {
        echo "<input type='hidden' value='first-pass' name='clear-signup'><button type='submit' name='clear-signup-button' formmethod='post'>Drop your signup?</button> If you just want to edit your details go to the <a href='/signup'>signup form</a> and resubmit it with the correct details.</form>";
    }
?>