<?php
    session_start();
    // Kreiranje tabela unutar baze podataka
    require_once "functions.php";
    require_once "PrivilegedUser.php";

    $ok = false;
    if(isset($_SESSION['username'])){
        $user = PrivilegedUser::getByUsername($_SESSION['username']);
        if($user !== false && $user->hasPrivilege("Run SQL")){
            $ok = true;
        }
    }

if($ok){

createTable('users', 
        'id INT UNSIGNED AUTO_INCREMENT,
        username VARCHAR(30) NOT NULL,          
        password VARCHAR(255) NOT NULL,
        INDEX(username(6)),
        PRIMARY KEY(id)'
);
 

// polje user_id da bi znali za kog usera ce biti vezan ovaj profil
// UNIQUE jer treba da bude veza 1-1, za tacno jednog usera se vezuje jedan profil
// sliku necemo pamtiti kao link, slika ce imati naziv kao id korisnika npr 2.jpg
createTable('profiles',
    'id INT UNSIGNED AUTO_INCREMENT,
     user_id INT UNSIGNED UNIQUE,            
     first_name VARCHAR(255) NOT NULL,
     last_name VARCHAR(255) NOT NULL,
     email VARCHAR(255) NOT NULL,
     gender VARCHAR(6) NOT NULL,
     language VARCHAR(50) NOT NULL,
     bio TEXT,
     PRIMARY KEY(id),
     FOREIGN KEY(user_id) REFERENCES users(id) on UPDATE CASCADE ON DELETE NO ACTION'
);

createTable('friends', 
    'id INT UNSIGNED AUTO_INCREMENT,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(sender_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE NO ACTION, 
    FOREIGN KEY(receiver_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE NO ACTION
    '
);

createTable('messages',
    'id INT UNSIGNED AUTO_INCREMENT,
    auth_id INT UNSIGNED NOT NULL,
    recip_id INT UNSIGNED NOT NULL,
    pm CHAR(1) NOT NULL,
    time INT UNSIGNED NOT NULL,
    message VARCHAR(4096) NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(auth_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE NO ACTION,
    FOREIGN KEY(recip_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE NO ACTION
    '
);

createTable('roles','
    id INT UNSIGNED AUTO_INCREMENT,
    role_name VARCHAR(200) NOT NULL,
    PRIMARY KEY(id)
');

createTable('permissions','
    id INT UNSIGNED AUTO_INCREMENT,
    perm_desc VARCHAR(200) NOT NULL,
    PRIMARY KEY(id)
');

createTable('role_permissions','
    id INT UNSIGNED AUTO_INCREMENT,
    role_id INT UNSIGNED NOT NULL,
    perm_id INT UNSIGNED NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE NO ACTION,
    FOREIGN KEY(perm_id) REFERENCES permissions(id) ON UPDATE CASCADE ON DELETE NO ACTION
');


createTable('user_roles','
    id INT UNSIGNED AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE NO ACTION,
    FOREIGN KEY(role_id) REFERENCES reles(id) ON UPDATE CASCADE ON DELETE NO ACTION
');

    echo "<br> ...done!";
} else {
    echo "You are not authorized to run this page!";
}









