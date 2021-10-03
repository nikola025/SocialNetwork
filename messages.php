<?php

    require_once 'header.php';
    if(!$loggedin){
        die("</div></bodt></html>");
    }
    if (isset($_GET['id'])){                    //ako sam preneo parametar korisnika kome hocu da posaljem poruku 
        $viewId = sanitazeString($_GET['id']);
    } else {
        $viewId = $id;                          // ako nema parametra
    }

    if(isset($_GET['erase'])){
        $messageId = sanitazeString($_GET['erase']);  // ne treba provera da li red postoji u bazi, jer ako ne postoji sql ne generise gresku
        $result = queryMysql("DELETE FROM messages WHERE id = $messageId");
        if($result){
            echo "<h4>Message deleted!</h4>";
        }
    }

     
    if($viewId == $id){
        $text = "Your messages:";
    } else {
        $temp = queryMysql("SELECT * FROM users WHERE id = $viewId");
        if($temp->num_rows){
            $row = $temp->fetch_assoc();            //imacu tacno jednog korisnika
            $username = $row['username'];
            $text = "${username}'s Messages:";
        } else {
            $text = "";
        }
    }

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $message = sanitazeString($_POST['message']);
        if($message != ""){
            $pm = substr(sanitazeString($_POST['pm']), 0, 1);             // treba nam podstring duzine 1
            $time = time();
            queryMysql("INSERT INTO messages(auth_id, recip_id, pm, time, message)
                VALUE
                ($id, $viewId, '$pm', $time, '$message')");
            echo "<h4>Message sent!</h4>";
        } else {
        echo "<h4>You cannot send an empty message!</h4>";
        }
    }

    $result = queryMysql("SELECT id FROM users WHERE id=$viewId");
    if($result->num_rows){
        ?>
    <!-- Prekidam php kod jer cu pisati html deo -->
        <form action="messages.php?id=<?php echo $viewId?>" method="POST">
            <h4>Type here to leave a message</h4>
            <textarea name="message" id="message" cols="60" rows="3"></textarea>
            <br>
            Private <input type="radio" name="pm" id="pm" value = "1" checked>
            &nbsp; &nbsp; &nbsp;
            Public <input type="radio" name="pm" id="pm" value= "0">
            <br>
            <input type="submit" value="Send message">
        </form>

    <?php

    }

    $result = queryMysql("SELECT * FROM messages WHERE recip_id = $viewId ORDER BY time DESC");
    if($result->num_rows){  //ako postoji korisnik, onda moze da se posalje poruka
    
        echo "<h3>$text</h3>";
        while($row = $result->fetch_assoc()){
            if($row['pm'] == 0 || $row['auth_id'] == $id || $row['recip_id'] == $id){ //poruke mogu da vidim jedino ako su javne ili je logovani korisnik poslao ili primio poruku
                echo "<div class='mess'>";
                //echo date("M jS \'y g:ia:",$row['time']);
                echo date("j.n.Y. H:i:s: ", $row['time']);
                $authId = $row['auth_id'];
                $result1 = queryMysql("SELECT username FROM users WHERE id=$authId");
                $row1 = $result1->fetch_assoc();
                echo $row1['username'];
                if ($row['pm'] == '0'){         //moglo je int, ali sam vec stavio char
                    echo " wrote: <br>&quot;" . $row['message'] . "&quot;";  
                } else {
                    echo " whispered: <br>&quot;" . $row['message'] . "&quot;";
                }
                if($row['auth_id'] == $id){              // na liniji 13
                    $messageId = $row['id'];
                    echo "<br><a href='messages.php?id=${viewId}&erase=$messageId'>Delete message</a>"; 
                     //da bi obrisali poruku koju smo poslali nekom korisniku moramo i id poruke da prosledimo
                }
    
                echo "</div>";
            }
           
        }
    } else {
        echo "<h3>No messages yet.</h3>";

    }