<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération des paramètres transmis
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$MODE = (isset($_GET['sso']))  ? clean_login($_GET['sso'])   : '';
$BASE = (isset($_GET['base'])) ? clean_entier($_GET['base']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Il faut savoir quel mode de SSO utiliser
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if(!in_array( $MODE , array('cas','saml') )) // ldap ajouté un jour ?
{
	affich_message_exit($titre='Donnée manquante',$contenu='Paramètre indiquant le mode de connexion SSO non transmis.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if(HEBERGEUR_INSTALLATION=='multi-structures')
{
	if(!$BASE)
	{
		affich_message_exit($titre='Donnée manquante',$contenu='Paramètre indiquant la base concernée non transmis.');
	}
	charger_parametres_mysql_supplementaires($BASE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Connexion à la base pour charger les paramètres du SSO demandé
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// Mettre à jour la base si nécessaire
maj_base_si_besoin($BASE);

$DB_TAB = DB_STRUCTURE_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root"'); // A compléter
foreach($DB_TAB as $DB_ROW)
{
	${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if( ($MODE=='cas') && ( ($connexion_mode!='cas') || (isset($connexion_mode,$cas_serveur_host,$cas_serveur_port,$cas_serveur_root)==false) ) )
{
	affich_message_exit($titre='Données incompatibles',$contenu='Etablissement non configuré par l\'administrateur pour une connexion CAS.');
}
// même test à faire pour les autres modes

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déconnexion avec le protocole CAS
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($MODE=='cas')
{
	// Inclure la classe phpCAS
	require_once('./_lib/phpCAS/CAS.php');
	// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
	// phpCAS::setDebug('debugcas.txt');
	// Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; le dernier argument indique qu'on utilise la session existante
	phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, false);
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
