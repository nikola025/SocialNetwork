<?php
    require_once 'header.php';
    if(!$loggedin){
        // Strinici pristupa nelogovan korisnik, i vrsi se restrikcija
        // header('Location: login.php'); PRVI NACIN
        die("<h3>You must <a href='login.php'>login</a> first to see the content of this page.</h3></div></body></html>"); //prekida izvrsenje skripta
        // jedino ako nisam logovan ovo die() ce prekidati izvrsenje skripte u suprotnom nece
    }
    $fname = $lname = $email = $gender = $lang = $bio = "";  // promenljive smo stavili iznad ifa jer nam trebaju i ako nije POST metod tj ako je GET
    $fnameError = $lnameError = $emailError = $genderError = $langError = $imageError = "";

    $result = queryMysql("SELECT * FROM profiles WHERE user_id = $id"); // ako ima profila da polja budu popunjena
    if($result->num_rows > 0  ) // u sustini tabela je dizajnirana tako da postoji jedan ili nula redova
    {
        $row = $result->fetch_assoc();
        $fname = $row['first_name'];        // sada vadim podatke iz baze, tako da citamo nazive iz baze, a i nema potrebe za sanitazeString() jer su vec u bazi
        $lname = $row['last_name'];
        $email = $row['email'];
        $gender = $row['gender'];
        $lang = $row['language'];
        $bio = $row['bio'];
    }
    if($_SERVER['REQUEST_METHOD'] == "POST"){
      //  var_dump($_POST);
        
        if(!empty($_POST['fname'])){
           $fname = sanitazeString($_POST['fname']);
       } else {
           $fnameError = "First name cannot be lef blank.";
       }
       if(!empty($_POST['lname'])){
            $lname = sanitazeString($_POST['lname']);
       } else {
           $lnameError = "Last name cannot be left blank.";
       }
       if(empty($_POST['email'])){   //Da li je vrednost elementa prazna? Znamo da postoji samo pitamo da li je prazna.
        // kod maila cemo da obrnemo logiku pitamo da JE prazno             
           
           $emailError = "Email field cannot be left blank";
       } else {
           $email = sanitazeString($_POST['email']);
           // proverimo da li je dobra adresa
           if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
               $emailError = "Invalid email format.";
               $email = ""; // ako se desila greska da vratimo email na prazan string
           }
       }
       if(isset($_POST['gender'])){                           //Da li postoji element sa zadatim kljucem? Ne znamo da li postoji.
            $gender = sanitazeString($_POST['gender']);  // i ne mora provera  sa sanitaze jer se radio o radio button-u
       } else {
           $genderError = "Gender cannot be left blank.";
       }
       if(!empty($_POST['lang'])){
           $lang = sanitazeString($_POST['lang']);
       } else {
           $langError = "You must choose one language.";
       }
      // if(!empty($_POST['bio'])){ //  moze da bude prazna
           $bio = sanitazeString($_POST['bio']);
           $io = preg_replace('/\s\s++/', ' ',$bio);            //trazimo razmake s je da moze jedan razmak da se desi, a s++ vise razmaka, a drugi parametar cime ga zamenjujemo 
       //}
       //Upis u bazu tek ako nije doslo do greske
       if($fnameError == "" && $lnameError == "" &&  $emailError == "" && $genderError == "" && $langError == ""){

            if($result->num_rows > 0){
                // Profil vec postoji, vrsi update(ako ocemo da promenimo vrednosti u formi na profilu)
                queryMysql("UPDATE profiles 
                    SET first_name = '$fname',
                        last_name = '$lname',
                        email = '$email',
                        gender = '$gender',
                        language ='$lang',
                        bio = '$bio'
                        WHERE user_id = $id");
            } else {
            queryMysql("INSERT INTO profiles(user_id, first_name, last_name, email, gender, language, bio) 
                VALUE ($id, '$fname', '$lname', '$email', '$gender', '$lang', '$bio');
            ");
            }
       }
    }

    // Da li je korisnik poslao slicicu
    if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])){ // ne moramo drugi and da pitamo, dovoljno je samo prvi, ovaj drugi se sadrzii u ovom prvom
        // Da li postoji folder za profilne slicice, ako ne postoji napravi ga
        if(!file_exists('profile_images/')){
            mkdir('profile_images/', 0777, true);
        }                 // pita da li fajl ili direktorijum postoji
        // Svaki korisnik ima svoju sliku u formatu id.jpg
        $saveto = "profile_images/$id.jpg";        // id imamo u headeru sacuvan za truenutno korisnika
                                    // dupli navodnici, znaci da ce se vrednost promenljive ispisati npr 1.jpg
        // Prebacuje se slicica iz privremene lokacije u lokaciju u folderu projekta
        move_uploaded_file($_FILES['image']['tmp_name'], $saveto);
        // Redimenzioniranje slicice
        // 1) Provera ekstenzije datoteke i kreiranje nove slike na osnovu poslate iz forme
        $typeok = true;
        switch($_FILES['image']['type']){
            case "image/gif";
                $src = imagecreatefromgif($saveto);
                break;
            case "image/jpeg":
            case "image/jpg":
                $src = imagecreatefromjpeg($saveto);
                 break;
            case "image/png":
                $src = imagecreatefrompng($saveto);
                break;
            default:
                $typeok = false;
        }
        if(!$typeok){
            $imageError = "Allowed types for profile photo are: gif/jpeg/jpg/png!";
        } else {
            // 2) Menjamo dimenzije nove slike
            list($w, $h)  = getimagesize($saveto);  //ova funkcija vraca dva parametra sirinu i visini pa moramo da ih smestimo u dve promenljive preko lista
            $max = 100;
            $tw = $w;
            $th = $h;
            if($w > $h && $w > $max){
                $th = $max * $h / $w;
                $tw = $max;
            } else if($h > $w && $h > $max){
                $tw = $max * $w / $h;
                $th = $max;
            }
            else{
                $th = $tw = $max;
            }
            // 3) Kreiranje nove slicice sa zadatim dimenzijama ($tw x $th)
            $tmp = imagecreatetruecolor($tw, $th);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tw, $th, $w, $h); // od koje pozicije slike src kopiramo u tmp
            imageconvolution($tmp, array(array(-1,-1,-1), array(-1,16,-1), array(-1,-1,-1)),8,0);
            imagejpeg($tmp,$saveto);
            imagedestroy($src);
            imagedestroy($tmp);
        }

    }
    showProfile($id);

?>
        <div class="content">
            <!-- Sadrzaj stranice profile.php -->
            <!-- Ako action u form ostavimo prazno to ce znaciti da vodi na istu stranicu, da se na istu stranicu salju podaci -->
            <form action="" method="POST" enctype="multipart/form-data"> <!-- bez ovog atributa nece ni jedan fajl da se salje --> 
                 <h2>Add or edit your profile</h2>
                 <span class="error">* required fields</span><br><br>
                <label for="fname">First name:</label>
                <input type="text" name="fname" id="fname" value="<?php echo $fname ?>"> <!-- ako je fname prazan string onda ce biti prazno polje -->
                <span class="error">*<?php echo $fnameError ?></span>
                <br> <!-- dodatno stilizovati u css-u -->
                <label for="lname">Last name:</label>
                <input type="text" name="lname" id="lname" value="<?php echo $lname ?>">
                <span class="error">*<?php echo $lnameError?></span>
                <br>
                <label for="email">Email</label>
                <input type="text" name="email" id="email" value="<?php echo $email ?>">
                <span class="error">*<?php echo $emailError ?></span>
                <br>
                <label for="gender">Gender:</label>
                <input type="radio" name="gender" id="female" value="female" 
                <?php echo ($gender == 'female') ? "checked" : "" ?>>Female  <!-- checked -->
                <input type="radio" name="gender" id="male" value="male"
                <?php echo ($gender == 'male') ? "checked" : "" ?>>Male
                <span class="error">*<?php echo $genderError ?></span>
                <br>
                <label for="lang">Favourite programming language</label>
                <select name="lang" id="lang">
                    <option value="" <?php echo ($lang == "")? "selected" : "" ?>>--Choose--</option>
                    <option value="php" <?php echo ($lang == "php")? "selected" : "" ?>>PHP</option>
                    <option value="c" <?php echo ($lang == "c")? "selected" : "" ?>>C</option>
                    <option value="c++" <?php echo ($lang == "c++")? "selected" : "" ?>>C++</option>
                    <option value="java" <?php echo ($lang == "java")? "selected" : "" ?>>Java</option>
                </select>
                <span class="error">*<?php echo $langError ?></span>
                <br>
                <label for="bio">Biography:</label>
                <textarea name="bio" id="bio" cols="30" rows="4"><?php echo $bio?></textarea>
                <br>
                <label for="image">Profile photo</label>
                <input type="file" name="image" id="image">
                <span class="error"><?php echo $imageError ?></span>
                <br>
                <input type="submit" value="Save Profile">
            </form>
        </div>

    </div>
    </body>
    </html>