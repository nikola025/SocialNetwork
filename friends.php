 <?php

 require_once 'header.php';
if (!$loggedin){
    die("</div></body></html>");
}

echo "<div class='content'>";
    $followers = array();    // id-evi korisnika koji mene prate
    $following = array();    // id-evi korisnika koje ja pratim

    $result  = queryMysql("SELECT sender_id FROM friends WHERE receiver_id = $id");
    while($row = $result->fetch_assoc()){
        $followerId = $row['sender_id'];
        $followers[] = $followerId;             // dodajem na kraju niza
    }

    $result = queryMysql("SELECT receiver_id FROM friends WHERE sender_id = $id");
    while($row = $result->fetch_assoc()){
        $followingId = $row['receiver_id'];
        $following[] = $followingId;
    }

    $mutual = array_intersect($followers, $following); // presek ova dva skupa
    // sada iz ovih gore nizova treba da izbacim ove u sto su u mutual 
    $followers = array_diff($followers, $mutual);       //razlika dva skupa
    $following = array_diff($following, $mutual);

    $haveFriends = false;
    if (sizeof($mutual)){
    //Da li ima elemenata u ovom nizu
        $haveFriends = true;
        echo "<h3>Mutal friends</h3>";
        echo "<ul>";
            foreach($mutual as $friendId){
            $result = queryMysql("SELECT users.id AS uid, users.username,
                profiles.first_name, profiles.last_name FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id 
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $fName = $row['first_name'];
            $lName = $row['last_name'];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";

            }
        echo "</ul>";
    }

    if (sizeof($followers)){
        $haveFriends = true; 
        echo "<h3>Friends who are following me</h3>";
        echo "<ul>";
            foreach($followers as $friendId){
            $result = queryMysql("SELECT users.id AS uid, users.username,
                profiles.first_name, profiles.last_name FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id 
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $fName = $row['first_name'];
            $lName = $row['last_name'];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";

            }
        echo "</ul>";
    }

    if (sizeof($following)){       
        $haveFriends = true;
        echo "<h3>Friends which I follow</h3>";
        echo "<ul>";
            foreach($following as $friendId){
            $result = queryMysql("SELECT users.id AS uid, users.username,
                profiles.first_name, profiles.last_name FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id 
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $fName = $row['first_name'];
            $lName = $row['last_name'];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";

            }
        echo "</ul>";
    }
    if($haveFriends == FALSE){
        echo "<div>You don't have any friends yet :( :( :(</div>";
        echo "<div><a href='members.php'>Go and make some friends!</div>";        
    }

echo "</div>";



