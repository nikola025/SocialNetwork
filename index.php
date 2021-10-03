
        <?php
            //ukljuci header.php
            // include "header.php"      ako ne postoji samo ce da ga ignosrise
            // require "header.php"      ako ne postoji napravice gresku
            // include_once "header.php" ukljucuje fajl i ako ga nema ne pravi gresku, najvise jedan put dozvoljava
            // require_once "header.php" ukljucuje fajl i ako ga nema pravi gresku, najvise jedan put dozvoljava    
            require_once "header.php";
            require_once "Role.php";
            require_once "PrivilegedUser.php"
        ?>
        <div class="content">
            <p>
            <?php
                echo "Welcome, $user!";
				
                if(isset($id)){
                    showProfile($id);
                    /*
                    var_dump($_SESSION['username']);
                    $result = PrivilegedUser::getByUsername($_SESSION['username']);
                    var_dump($result); 
                    */
                }
                /*
                $result = Role::getRolePerms(1);  // daj mi permisije za rolu 1
                var_dump($result);
                var_dump($result->hasPermission('Run SQL'));
                */
            ?>
            </p>
        </div>
    </div>
</body>
</html>







