<?php

//##########################################################################
// Définition des pools de connexion
//##########################################################################

$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['ABSTRACTION']    = 'PDO';
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['TYPE']           = 'mysql';
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['PORT']           = '3306';
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['FORCE_ENCODING'] = 'utf8';
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['CRITICAL']       = true;
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['LOG']            = 'errfile';

$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['HOST'] = SACOCHE_STRUCTURE_BD_HOST;
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['USER'] = SACOCHE_STRUCTURE_BD_USER;
$_CONST['POOL'][SACOCHE_STRUCTURE_BD_NAME]['PASS'] = SACOCHE_STRUCTURE_BD_PASS;

//##########################################################################
// Associations des noms de connexion aux pools et à la base de données
//##########################################################################

$_CONST['CONNECTION'][SACOCHE_STRUCTURE_BD_NAME]['POOL']    = SACOCHE_STRUCTURE_BD_NAME;
$_CONST['CONNECTION'][SACOCHE_STRUCTURE_BD_NAME]['DB_NAME'] = SACOCHE_STRUCTURE_BD_NAME;

?>
