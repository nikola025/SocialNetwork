<?php
    require_once "header.php";
    // Kojom metodom(nacinom) smo dosli na ovu stranicu signup.php?
    // GET ili POST
    // lokalne promenljive - unutar funkcije ($x)
    // globalne promenljive - van funkcije ($x)
    // superglobalne promenljive su ugradjene php promenljive koje postoje u svim php fajlovima 
    // ($_COOKIE, $_FILES, $_GET, $_SERVER)  ako podatke posaljem preko get oni ce se puniti u $_GET promenljivu
    // $_GET i $_POST su asocijativni nizovi
    
    $error = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $username = $connection->real_escape_string($_POST['username']);
        $password = $connection->real_escape_string($_POST['password']); 
        if($username == "" || $password == ""){
            $error = "All fields are required!";
        } else {
            // ovde moze da ide provera username i passworda, ali mi cemo dozvoliti da budu proizvoljni
            $result = queryMysql("SELECT * FROM users WHERE username = '$username'");
            // $result - rezultat izvrsenja upita
            if($result->num_rows > 0){
                // Korisnik sa ovim username-om vec postoji
                $error = "That username is taken - please choose another one!";
            } else {
                // Upis novog korisnika
                $codedPassword = PASSWORD_HASH($password, PASSWORD_DEFAULT);
                queryMysql("INSERT INTO users(username,password)
                    VALUES('$username', '$codedPassword')");
                    header("Location: login.php"); // redirektujemo korisnika na stranicu login.php
            }

        }
    } 
    /*      Nista se ne desava kad je GET methoda
    else {
        if(!empty($_GET['username']) && !empty($_GET['password'])){
            $username = $connection->real_escape_string($_GET['username']);
            $password = $connection->real_escape_string($_GET['password']); 
            echo $username;
            echo "<br>";
            echo $password;
        }
    }
    */
?>

    <div class="content">
        <h2>Create a new account</h2>
        <div class="error">
            <?php echo $error; ?>  <!-- Ako se nije desila greska div ce ostati prazan -->
        </div>
        <form action="signup.php" method="post"> 
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Your username..."
            onBlur = "checkUser(this)">
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Your password...">
            <br>
            <input type="submit" value="Sign up!">
        </form>
    </div>
</div>
<script src="myscript.js"></script>
<script>
    function checkUser(inp){
        var username = inp.value;
        if(username == ""){
            document.getElementsByClassName('error')[0].innerHTML = "";         //ispraznim div sa klasom error 
            return;
        } 
        // AJAX request            // saljemo zahtev php stranici
        var params = "username=" + username;
        var request = ajaxRequest();

        if(request !== false){
            request.open("POST", "checkuser.php", true);
            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            request.setRequestHeader("Content-length", params.length);
            request.setRequestHeader("Connection","close");

            request.onreadystatechange = function(){                    
                if(this.readyState == 4 && this.status == 200){         //indikator da je sve u redu
                    document.getElementsByClassName('error')[0].innerHTML = this.responseText;
                }
            }
            request.send(params);           // tek na kraju saljemo zahtev stranici checkuser 
        }
    }

    
</script>
</body>
</html>