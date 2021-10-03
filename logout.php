<?php
    require_once 'header.php';
    // Proverimo da li je postavljena sesija, jer neko moze ukucati direktno logout i onda bi se zatvorila sesija koja nije ni bila pokrenuta 
    if (isset($_SESSION['username'])){ // isset(username nije uvek setovan) zato jer mora da proverimo da li u ovom asocijativnom nizu postoji vrednost sa ovim kljucem 
        $_SESSION = array(); // za svaki slucaj ispraznimo sesiju 
        session_destroy();
        echo "<div class='content'>You have been logged out. Go to the 
        <a href='index.php'>main page</a>.</div>";        
        //header('Location: index.php');
    } else {
        echo "<div class='content'>You cannot logout because you are not logged in!</div>";
    }
 
    
?> 

 </div>
 </body>
 </html>