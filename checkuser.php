<?php
    require_once "functions.php";

    if(isset($_POST['username'])){      // da li je dosao POST metodom, ako nije samo ignorisem
        $username = sanitazeString($_POST['username']);
        $result = queryMysql("SELECT * FROM users WHERE username='$username'");
        if($result->num_rows){
            echo "<span id='taken'>That username is taken - please choose another one!
            </span>";
        }else{
            echo "<span id='available'>This username is available</span>";
        }
    }