<?php
    $dbhost = "localhost";
    $dbname = "dmreza";
    $dbuser = "dmadminn";
    $dbpassword = "nix025";

    // Objekat konekcije ka bazi
    $connection = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);
    if($connection->connect_error != null){
        die($connection->connect_error);
    } 

    // Funkcija za izvrsavanje proizvoljnih upita u bazu podataka
    function queryMysql($query){
        global $connection;           // sad je ista kao ona gore, da nije stavljeno global, bila bi lokalna 
        $result = $connection->query($query); 
        if(!$result){
            die($connection->error); // ovo polje se puni jedino kada se desila greska u upitu
        }
        return $result;
    }

    // Funkcija za izvrsavanje CREATE TABLE upita
    function createTable($name, $query){ // prvi parametar, ime tabele, a drugi, kako izgleda tabela
        queryMysql("CREATE TABLE IF NOT EXISTS $name($query)"); //kada je string unutar duplih navodnika onda ce se i vrednost ove 2 promenljive ispisati
        echo "Table '$name' created or alredy exists.<br>";
    }

    // Funkcija za tretiranje inputa iz forme
    function sanitazeString($text){
        $text = strip_tags($text);    
        $text = htmlentities($text);
        $text = stripslashes($text);
        global $connection;
        $text = $connection->real_escape_string($text);
        return $text;
    }

    function showProfile($id){
        $result = queryMysql("SELECT * FROM profiles WHERE user_id = $id");
        if($result->num_rows){
            $row = $result->fetch_assoc();
            echo "<div class='p'>";
            if(file_exists("profile_images/$id.jpg")){
                echo "<img src='profile_images/$id.jpg' class='pf'>";
            }
            echo "<div class='pi'>";
            echo $row['first_name'] . " " . $row['last_name'];
            echo "<br>";
            echo $row['email'];
            echo "<br>";
            echo $row['bio'];
            echo "</div>";
            echo "</div>";
        } 
    }
















