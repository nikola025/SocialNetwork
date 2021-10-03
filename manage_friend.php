<?php

require_once "functions.php";   //ne zelimo header;

if($_SERVER['REQUEST_METHOD'] == "POST"){

    if(isset($_POST['action'])){
        
            if($_POST['action'] == "add"){
            
                $userId = sanitazeString($_POST['friend_id']);
                $id = sanitazeString($_POST['my_id']);
        
                $result =  queryMysql("SELECT * FROM friends WHERE sender_id = $id 
                                    AND receiver_id = $userId");
                if($result->num_rows == 0){
                    queryMysql("INSERT INTO friends(sender_id,receiver_id)
                        VALUES ($id,$userId)");
                }  
             } elseif($_POST['action'] == "remove"){
                                $userId = sanitazeString($_POST['friend_id']);
                                $id = sanitazeString($_POST['my_id']);
                                queryMysql("DELETE FROM friends WHERE sender_id = $id 
                                                            AND receiver_id = $userId");
            
            }
                                                                    //Vracamo sadrzaj li taga   
                                $result = queryMysql("SELECT users.id AS uid, users.username,
                                profiles.first_name, profiles.last_name FROM users
                                LEFT JOIN profiles ON users.id = profiles.user_id 
                                WHERE users.id = $userId");      
                                $row = $result->fetch_assoc();
                                $userId = $row['uid'];                    
                                echo "<a href='members2.php?id=$userId'>";
                                echo $row['first_name'];
                                echo " ";
                                echo $row['last_name'];
                                echo " (";
                                echo $row['username'];
                                echo ")";
                                echo "</a>";
                                echo "&nbsp; &nbsp;";
                                // Proveravamo u kojoj smo relaciji sa korisnikom
                                // 1) Samo ja drugog korisnika pratim
                                // 2) Samo drugi korisnik mene prati
                                // 3) Uzajamno pracenje sa drugim korisnikom
                                
                                // Provera da li ja pratim datog korisnika
                                $result1 = queryMysql("SELECT * FROM friends WHERE
                                            sender_id = $id AND receiver_id = $userId");
                                $t1 = $result1->num_rows;  // 0 ili 1
                
                                // Da li dati korisnik mene prati
                                $result2 = queryMysql("SELECT * FROM friends WHERE
                                            sender_id = $userId AND receiver_id = $id");
                                $t2 = $result2->num_rows;  // 0 ili 1 
                                $additionalText="";
                                if($t1 + $t2 > 1){
                                    echo " is a mutual friend ";
                
                                } elseif($t1){
                                    echo " you are following ";
                                } elseif($t2){
                                    echo " is following you ";
                                    $additionalText = " back";
                                } 
                                if(!$t1){
                                    echo "[<a mid='$id' fid='$userId' href='#' class='add' >Follow$additionalText</a>]";
                                    echo "&nbsp";
                                } else{
                                    echo "[<a mid='$id' fid='$userId' href='#' class='remove'>Unfollow</a>]";
                                    echo "&nbsp";
                                }
                                echo "[<a href='messages.php?id=$userId'>Send message</a>]";

    }
}