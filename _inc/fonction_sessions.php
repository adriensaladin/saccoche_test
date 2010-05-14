<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// Fonctions pour gérer les sessions.
// La session est transmise via le cookie "$_COOKIE['SESSION_NOM']".

// Paramétrage de la session
define('SESSION_NOM','SACoche-session');
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
	$ID = uniqid(mt_rand(),true);
	session_id($ID);
	session_start();
}

// Initialiser une session existante
function init_session()
{
	$_SESSION = array();

	// Numéro de la base
	$_SESSION['BASE']             = 0;
	// Données associées à l'utilisateur.
	$_SESSION['USER_PROFIL']      = 'public';	// public / webmestre / administrateur  / professeur  / eleve
	$_SESSION['USER_ID']          = 0;
	// Données associées à l'établissement.
	$_SESSION['SESAMATH_ID']      = 0;
	$_SESSION['SSO']              = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
}

// Fermer une session existante
function close_session()
{
	$alerte_sso = ( isset($_SESSION['SSO']) && ($_SESSION['SSO']!='normal') &&($_SESSION['USER_PROFIL']!='administrateur') ) ? '&amp;f_base='.$_SESSION['BASE'].'&amp;f_sso='.$_SESSION['SSO'] : false ;
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