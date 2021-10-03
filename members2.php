<?php
    require_once 'header.php';
    if(!$loggedin){
        // Strinici pristupa nelogovan korisnik, i vrsi se restrikcija
        // header('Location: login.php'); PRVI NACIN
        die("<h3>You must <a href='login.php'>login</a> first to see the content of this page.</h3></div></body></html>"); //prekida izvrsenje skripta
    }

    if(isset($_GET['id'])){  // Mi na ovu stranicu dolazimo get metodom, klikom na link nekog membera hocemo da uhvatimo ?id
        //  Prikazi profil korisnika ciji je id=$_GET['id']
        $userId = sanitazeString($_GET['id']);  // userId je samo promenljiva 
        $result1 = queryMysql("SELECT first_name, last_name FROM profiles WHERE user_id = $userId");
        if($result1->num_rows){
            $row = $result1->fetch_assoc();
            $view = $row['first_name'] . " " . $row['last_name'];
        } else {
            $result2 = queryMysql("SELECT username FROM users WHERE id=$userId");
            $row = $result2->fetch_assoc();
            $view = $row['username'];
        }

        if($userId == $id){
            $name = "Your";
        } else {
            $name = "${view}'s";
        }

        echo "<h3>$name Profile</h3>";
        showProfile($userId);
        die("<br><br><a href='members.php'>Go back to the previous page.</a></div></body></html>");            // da mi ne prikazuje ostale kad gledam nekog
    }

    ?>

    <div class="content">
        <h3>Members on the platform</h3>

        <?php
            // Dohvatamo sve korisnike koji nisu logovani korisnik
            $result = queryMysql("SELECT users.id AS uid, users.username,
            profiles.first_name, profiles.last_name FROM users
            LEFT JOIN profiles ON users.id = profiles.user_id 
            WHERE users.id != $id
            ORDER BY profiles.first_name, profiles.last_name");             //id vadimo iz sesije za logovanog korisnika
            echo "<ul id='member_list'>";
            while($row = $result->fetch_assoc()){
               
                $userId = $row['uid']; 
                echo "<li id='$userId'>";                        
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
                echo "</li>";
            }
            echo "</ul>";
        ?>

    </div>
    <script src="myscript.js"></script>
    <script>
      /*
      // var addLinks = document.getElementsByClassName('add');
            var addLinks = document.querySelectorAll('.add');   //dobicu sve linkove koji imaju ovu klasu
            for(var i=0;i<addLinks.length;i++){
                //console.log(addLinks[i]);
                addLinks[i].addEventListener("click", function(event){
                    //alert("Hello");
                    event.preventDefault(); // da se ne prati link u hrefu  
                    var myId = this.getAttribute('mid');
                    var friendId = this.getAttribute('fid');
                    var params = "action=add&my_id=" + myId + "&friend_id=" + friendId ;        
                   // alert(myId + " " + friendId);                                // u js + za nadovezivanje stringova
                    //Pravimo novi ajax zahtev
                    var request = ajaxRequest();                            
                        if(request !== false){
                            request.open("POST", "manage_friend.php", true);
                            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            request.setRequestHeader("Content-length", params.length);
                            request.setRequestHeader("Connection","close");

                            request.onreadystatechange = function(){                    
                                if(this.readyState == 4 && this.status == 200){         //indikator da je sve u redu
                                    document.getElementById(friendId).innerHTML = this.responseText;
                                }
                            }
                            request.send(params);           // tek na kraju saljemo zahtev stranici checkuser 
                        }
                });
            }

            var removeLinks = document.querySelectorAll('.remove');   //dobicu sve linkove koji imaju ovu klasu
            for(var i=0;i<removeLinks.length;i++){
                //console.log(removeLinks[i]);
                removeLinks[i].addEventListener("click", function(event){
                    //alert("Hello");
                    event.preventDefault(); // da se ne prati link u hrefu  
                    var myId = this.getAttribute('mid');
                    var friendId = this.getAttribute('fid');
                    var params = "action=remove&my_id=" + myId + "&friend_id=" + friendId ;        
                   // alert(myId + " " + friendId);                                // u js + za nadovezivanje stringova
                    //Pravimo novi ajax zahtev
                    var request = ajaxRequest();                            
                        if(request !== false){
                            request.open("POST", "manage_friend.php", true);
                            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            request.setRequestHeader("Content-length", params.length);
                            request.setRequestHeader("Connection","close");

                            request.onreadystatechange = function(){                    
                                if(this.readyState == 4 && this.status == 200){         //indikator da je sve u redu
                                    document.getElementById(friendId).innerHTML = this.responseText;
                                }
                            }
                            request.send(params);           // tek na kraju saljemo zahtev stranici checkuser 
                        }
                });
            }
        */

        var ulList = document.querySelector("#member_list");
       // console.log(ulList);                          //Cisto provera da vidim koju listu ce da doda, ako stavim samo ul,dodaje listu sa klasom menu, a meni ta ne treba
                                                        // pa zato ovoj novoj listi dodajem id member_list 
        ulList.addEventListener("click",function(event){
            //console.log(event);
            if(event.target.tagName == "A"){            // da li smo kliknuli na link
                if(event.target.className == "add"){
                   // alert("dodaj");
                    event.preventDefault();             //preventDefault() samo ako sam kliknuo na add i remove klasu 
                    var myId = event.target.getAttribute('mid');        // umesto this sada treba event.tartget
                    var friendId = event.target.getAttribute('fid');
                    var params = "action=add&my_id=" + myId + "&friend_id=" + friendId ;        
                   // alert(myId + " " + friendId);                                // u js + za nadovezivanje stringova
                    //Pravimo novi ajax zahtev
                    var request = ajaxRequest();                            
                        if(request !== false){
                            request.open("POST", "manage_friend.php", true);
                            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            request.setRequestHeader("Content-length", params.length);
                            request.setRequestHeader("Connection","close");

                            request.onreadystatechange = function(){                    
                                if(this.readyState == 4 && this.status == 200){         //indikator da je sve u redu
                                    document.getElementById(friendId).innerHTML = this.responseText;
                                }
                            }
                            request.send(params);           // tek na kraju saljemo zahtev stranici checkuser 
                        }

                                        
                } else if(event.target.className == "remove"){
                    // alert("izbrisi");
                    event.preventDefault();
                    var myId = event.target.getAttribute('mid');
                    var friendId = event.target.getAttribute('fid');
                    var params = "action=remove&my_id=" + myId + "&friend_id=" + friendId ;        
                   // alert(myId + " " + friendId);                                // u js + za nadovezivanje stringova
                    //Pravimo novi ajax zahtev
                    var request = ajaxRequest();                            
                        if(request !== false){
                            request.open("POST", "manage_friend.php", true);
                            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            request.setRequestHeader("Content-length", params.length);
                            request.setRequestHeader("Connection","close");

                            request.onreadystatechange = function(){                    
                                if(this.readyState == 4 && this.status == 200){         //indikator da je sve u redu
                                    document.getElementById(friendId).innerHTML = this.responseText;
                                }
                            }
                            request.send(params);           // tek na kraju saljemo zahtev stranici checkuser 
                        }
                }
            }
        });
    </script>
    </div>
    </body>
    </html>