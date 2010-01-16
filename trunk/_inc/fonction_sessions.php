<?php
/**
 * @version $Id: fonction_sessions.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Fonctions pour gérer les sessions.
 * La session est transmise via le cookie "$_COOKIE['SESSION_NOM']".
 * 
 */

// Paramétrage de la session
define('SESSION_NOM','livret-session');
session_name(SESSION_NOM);
session_cache_limiter('nocache');
session_cache_expire(180);

// Répertoire d'enregistrement des sessions
$session_rep = session_save_path();
$test_open = @opendir($session_rep);
if(!$test_open)
{
	@mkdir($session_rep);
}

// Ouvrir une session existante
function open_old_session()
{
	$ID = $_COOKIE[SESSION_NOM];
	session_id($ID);
	session_start();
}

// Créer une nouvelle session
function open_new_session()
{
	$ID = md5(uniqid(rand(),true));
	session_id($ID);
	session_start();
}

// Initialiser une session existante
function init_session()
{
	$_SESSION = array();
	$_SESSION['PROFIL']           = 'public';	// public / webmestre / administrateur  / professeur  / eleve
	$_SESSION['STRUCTURE_ID']     = 0;
	$_SESSION['USER_ID']          = 0;
	$_SESSION['SSO']              = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
}

// Fermer une session existante
function close_session()
{
	$alerte_sso = ( isset($_SESSION['SSO']) && ($_SESSION['SSO']!='normal') &&($_SESSION['PROFIL']!='administrateur') ) ? '&amp;structure_id='.$_SESSION['STRUCTURE_ID'].'&amp;sso='.$_SESSION['SSO'] : false ;
	$_SESSION = array();
	setcookie(session_name(),'',time()-42000,'/');
	session_destroy();
	return $alerte_sso;
}

// Effacer les traces d'anciennes sessions (prend vite de la place si non effacé automatiquement par l'hébergeur)
// ***** A appeler lors une fois par jour (non encore incorporé). *****
function clean_old_session()
{
	global $session_rep;
	$j_moins_7 = time() - 7*24*60*60;
	$tab_files = scandir($session_rep);
	unset($tab_files[0],$tab_files[1]);
	foreach($tab_files as $file)
	{
		if( filemtime($session_rep.'/'.$file) < $j_moins_7 )
		{
			unlink($session_rep.'/'.$file);
		}
	}
}

?>