<?php

//##########################################################################
// 									Définition des pools de connexion
//##########################################################################

$_CONST["POOL"][SESAMATH2_BD_NAME]["ABSTRACTION"] = 'PDO';
$_CONST["POOL"][SESAMATH2_BD_NAME]["TYPE"] = 'mysql';
$_CONST["POOL"][SESAMATH2_BD_NAME]["PORT"] = '3306';
$_CONST["POOL"][SESAMATH2_BD_NAME]["FORCE_ENCODING"] = 'utf8';
$_CONST["POOL"][SESAMATH2_BD_NAME]["CRITICAL"] = true;
$_CONST["POOL"][SESAMATH2_BD_NAME]["LOG"] = 'errfile';

$_CONST["POOL"][SESAMATH2_BD_NAME]["HOST"] = SESAMATH2_BD_HOST;
$_CONST["POOL"][SESAMATH2_BD_NAME]["USER"] = SESAMATH2_BD_USER;
$_CONST["POOL"][SESAMATH2_BD_NAME]["PASS"] = SESAMATH2_BD_PASS;

//##########################################################################
// 		Associations des nom de connexion aux pool et à la base de données
//##########################################################################

$_CONST["CONNECTION"][SESAMATH2_BD_NAME]["POOL"] = SESAMATH2_BD_NAME;
$_CONST["CONNECTION"][SESAMATH2_BD_NAME]["DB_NAME"] = SESAMATH2_BD_NAME;

?>
