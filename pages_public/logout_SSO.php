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

$base = (isset($_GET['f_base'])) ? intval($_GET['f_base'])     : 0;
$sso  = (isset($_GET['f_sso']))  ? $_GET['f_sso']              : '';

require_once('../_inc/tableau_sso.php');	// !!! Remonter d'un dossier !!!	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
unset($tab_sso['normal']);

if( (!$base) || (!isset($tab_sso[$sso])) )
{
	exit('Paramètre manquant.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Préparation de la connexion à l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// La classe phpCAS nécessite la bibliothèque de fonction cURL http://fr2.php.net/manual/fr/book.curl.php
if(!in_array( 'curl' , get_loaded_extensions() ))
{
	exit('Le module PHP "curl" est manquant (bibliothèque requise pour CAS).');
}
// Inclure la classe phpCAS
include_once('../_inc/class.CAS.php');	// !!! Remonter d'un dossier !!!
// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
// phpCAS::setDebug('debugcas.txt');
// Initialiser la connexion avec CAS 
$cas_host = $sso; // l'hôte du serveur CAS
$cas_port = 443; // Le port
$cas_root = ''; //inutile avec ARGOS
// Le premier argument est la version du protocole CAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_root, false);
phpCAS::setLang('french');
// On indique qu'il n'y a pas de validation du certificat SSL à faire
phpCAS::setNoCasServerValidation();
// Gestion du single sign-out
phpCAS::handleLogoutRequests(false);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Appel pour se déconnecter
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

phpCAS::logout();

?>
