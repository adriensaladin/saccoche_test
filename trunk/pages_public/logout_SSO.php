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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$BASE = (isset($_GET['f_base'])) ? intval($_GET['f_base']) : 0;
$mode = (isset($_GET['f_mode'])) ? $_GET['f_mode']         : '';

if( (!$BASE) || ($mode!='cas') )
{
	exit('Paramètre manquant.');
}

if($mode=='cas')
{
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Préparation de la connexion au serveur CAS
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	// La classe phpCAS nécessite la bibliothèque de fonction cURL http://fr2.php.net/manual/fr/book.curl.php
	if(!in_array( 'curl' , get_loaded_extensions() ))
	{
		affich_message_exit($titre='PHP incomplet',$contenu='Le module PHP "curl" est manquant (bibliothèque requise pour CAS).');
	}
	// De connecter pour charger les paramètres de connexion au serveur CAS
	if($BASE)
	{
		charger_parametres_mysql_supplementaires($BASE);
	}
	$DB_TAB = DB_STRUCTURE_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root"');
	foreach($DB_TAB as $DB_ROW)
	{
		${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
	}
	if( (isset($connexion_mode,$cas_serveur_host,$cas_serveur_port,$cas_serveur_root)==false) || ($connexion_mode!='cas') )
	{
		affich_message_exit($titre='Données incompatibles',$contenu='Base de l\'établissement non configurée pour une connexion CAS.');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Connexion au serveur CAS pour se déconnecter
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	// Inclure la classe phpCAS
	require_once('./_inc/class.CAS.1.0.1.php');
	// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
	// phpCAS::setDebug('debugcas.txt');
	// Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; passer false comme dernier paramètre fait boucler phpCAS 1.1.1 (RAS avec phpCAS 1.0.1)
	phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, true);
	phpCAS::setLang(PHPCAS_LANG_FRENCH);
	// On indique qu'il n'y a pas de validation du certificat SSL à faire
	phpCAS::setNoCasServerValidation();
	// Gestion du single sign-out
	phpCAS::handleLogoutRequests(false);
	// Demander à CAS de se déconnecter
	phpCAS::logout();
}

exit();

?>
