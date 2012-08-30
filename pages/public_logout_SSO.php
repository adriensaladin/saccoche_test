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
// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$BASE = (isset($_GET['base'])) ? clean_entier($_GET['base']) : 0;

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

$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root","gepi_url","gepi_rne","gepi_certificat_empreinte"'); // A compléter
foreach($DB_TAB as $DB_ROW)
{
	${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if($connexion_mode=='normal')
{
	affich_message_exit($titre='Configuration manquante',$contenu='Etablissement non configuré par l\'administrateur pour utiliser un service d\'authentification externe.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déconnexion avec le protocole CAS
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($connexion_mode=='cas')
{
	// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
	// phpCAS::setDebug('debugcas.txt');
	// Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; le dernier argument indique qu'on utilise la session existante
	phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, false);
	phpCAS::setLang(PHPCAS_LANG_FRENCH);
	// On indique qu'il n'y a pas de validation du certificat SSL à faire
	phpCAS::setNoCasServerValidation();
	// Gestion du single sign-out
	phpCAS::handleLogoutRequests(false);
	// Déconnexion de CAS
	phpCAS::logout();
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déconnexion de GEPI avec le protocole SAML
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($connexion_mode=='gepi')
{
	// Mise en session d'informations dont SimpleSAMLphp a besoin ; utiliser des constantes ne va pas car Gepi fait un appel à SimpleSAMLphp en court-circuitant SACoche pour vérifier la légitimité de l'appel.
	$_SESSION['SACoche-SimpleSAMLphp'] = array(
		'GEPI_URL'                  => $gepi_url,
		'GEPI_RNE'                  => $gepi_rne,
		'GEPI_CERTIFICAT_EMPREINTE' => $gepi_certificat_empreinte,
		'SIMPLESAMLPHP_BASEURLPATH' => substr($_SERVER['SCRIPT_NAME'],1,-9).'_lib/SimpleSAMLphp/www/',
		'WEBMESTRE_NOM'             => WEBMESTRE_NOM,
		'WEBMESTRE_PRENOM'          => WEBMESTRE_PRENOM,
		'WEBMESTRE_COURRIEL'        => WEBMESTRE_COURRIEL
	);
	// Initialiser la classe
	$auth = new SimpleSAML_Auth_Simple('distant-gepi-saml');
	// Déconnexion de GEPI
	if ($auth->isAuthenticated())
	{
		$auth->logout();
		exit();
	}
	elseif(isset($_SESSION['SimpleSAMLphp_SESSION']))
	{
		// On revient très probablement de la déconnexion de GEPI (en effet, au contraire de CAS, la page de déconnexion distante renvoie vers l'application au lieu de marquer un arrêt).
		unset($_SESSION['SimpleSAMLphp_SESSION']);
		affich_message_exit($titre='Deconnexion de Gepi',$contenu='Déconnexion du service d\'authentification Gepi effectuée.<br />Fermez votre navigateur par sécurité.');
	}
	else
	{
		// Bizarre... a priori on n'était pas connecté à GEPI... appel direct ?
		affich_message_exit($titre='Deconnexion de Gepi',$contenu='Votre authentification sur Gepi n\'a pas été retrouvée.<br />Fermez votre navigateur par sécurité pour être certain d\'en être déconnecté.');
	}
}

?>
