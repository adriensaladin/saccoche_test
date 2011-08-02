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
$TITRE = "Connexion SSO";

/*
 * Cette page n'est pas (plus en fait) appelée directement.
 * Elle est appelée lors lien direct vers une page nécessitant une identification :
 * - si des paramètres dans l'URL indiquent explicitement un SSO (nouvelle connexion, appel depuis un service tiers...)
 * - ou si des informations en cookies indiquent un SSO (session perdue mais tentative de reconnexion automatique)
 * 
 * En cas d'installation de type multi-structures, SACoche doit connaître la structure concernée AVANT de lancer SAML ou CAS pour savoir si l'établissement l'a configuré ou pas, et avec quels paramètres !
 * Si on ne sait pas de quel établissement il s'agit, on ne peut pas savoir s'il y a un CAS, un SAML-GEPI, et si oui quelle URL appeler, etc.
 * (sur un même serveur il peut y avoir un SACoche avec authentification reliée à l'ENT de Nantes, un SACoche relié à un LCS, un SACoche relié à un SAML-GEPI, ...)
 * D'autre part on ne peut pas me fier à une éventuelle info transmise par SAML ou CAS ; non seulement car elle arrive trop tard comme je viens de l'expliquer, mais aussi car ce n'est pas le même schéma partout.
 * (CAS, par exemple, peut renvoyer le RNE en attribut APRES authentification à une appli donnée, dans une acad donnée, mais pas pour autant à une autre appli, ou dans une autre acad)
 * 
 * Normalement on passe en GET le numéro de la base, mais il se peut qu'une connection directe ne puisse être établie qu'avec l'UAI (connu de l'ENT) en non avec le numéro de la base SACoche (inconnu de l'ENT).
 * Dans ce cas, on récupère le numéro de la base et on le remplace dans les variable PHP, pour ne pas avoir à recommencer ce petit jeu à chaque échange avec le serveur SSO pendant l'authentification.
 * 
 * URL directe mono-structure            : http://adresse.com/?sso
 * URL directe multi-structure normale   : http://adresse.com/?sso&base=...
 * URL directe multi-structure spéciale  : http://adresse.com/?sso&uai=...
 * 
 * URL profonde mono-structure           : http://adresse.com/?page=...&sso
 * URL profonde multi-structure normale  : http://adresse.com/?page=...&sso&base=...
 * URL profonde multi-structure spéciale : http://adresse.com/?page=...&sso&uai=...
 */

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Si transmission d'un UAI
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$UAI = (isset($_GET['uai'])) ? clean_uai($_GET['uai']) : '' ;

if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($UAI!='') )
{
	$DB_ROW = DB_WEBMESTRE_recuperer_structure_by_UAI($UAI);
	if(!count($DB_ROW))
	{
		affich_message_exit($titre='Paramètre incorrect',$contenu='Le numéro UAI transmis n\'est pas référencé sur cette installation de SACoche.');
	}
	// Remplacer l'info par le numéro de base correspondant dans toutes les variables accessibles à PHP avant que la classe SSO ne s'en mèle.
	$bad = 'uai='.$_GET['uai'];
	$bon = 'base='.$DB_ROW['sacoche_base'];
	$_GET['base']     = $DB_ROW['sacoche_base'];
	$_REQUEST['base'] = $DB_ROW['sacoche_base'];
	if(isset($_SERVER['HTTP_REFERER'])) { $_SERVER['HTTP_REFERER'] = str_replace($bad,$bon,$_SERVER['HTTP_REFERER']); }
	if(isset($_SERVER['QUERY_STRING'])) { $_SERVER['QUERY_STRING'] = str_replace($bad,$bon,$_SERVER['QUERY_STRING']); }
	if(isset($_SERVER['REQUEST_URI'] )) { $_SERVER['REQUEST_URI']  = str_replace($bad,$bon,$_SERVER['REQUEST_URI'] ); }
	unset($_GET['uai']);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$BASE = (isset($_GET['base'])) ? clean_entier($_GET['base']) : ( (isset($_COOKIE[COOKIE_STRUCTURE])) ? clean_entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ) ;

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

$DB_TAB = DB_STRUCTURE_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root","gepi_url","gepi_rne","gepi_certificat_empreinte"'); // A compléter
foreach($DB_TAB as $DB_ROW)
{
	${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if($connexion_mode=='normal')
{
	affich_message_exit($titre='Configuration manquante',$contenu='Etablissement non configuré par l\'administrateur pour utiliser un service d\'authentification externe.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Identification avec le protocole CAS
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($connexion_mode=='cas')
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
	// Demander à CAS d'aller interroger le serveur
	// Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
	// (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
	phpCAS::forceAuthentication();
	// Rapatrier les informations si elles sont validées par CAS (qui envoie alors un ticket en GET)
	$auth = phpCAS::checkAuthentication();
	// Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
	$id_ENT = phpCAS::getUser();
	// Comparer avec les données de la base
	list($auth_resultat,$auth_DB_ROW) = tester_authentification_user($BASE,$id_ENT,$password=false,$connexion_mode);
	if($auth_resultat!='ok')
	{
		affich_message_exit($titre='Incident authentification',$contenu=$auth_resultat);
	}
	enregistrer_session_user($BASE,$auth_DB_ROW);
	// Redirection vers la page demandée en cas de succès.
	// En théorie il faudrait laisser la suite du code se poursuivre, ce qui n'est pas impossible, mais ça pose le souci de la transmission de &verif_cookie
	$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://' ;
	redirection_immediate($protocole.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&verif_cookie');
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Identification à partir de GEPI avec le protocole SAML
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
	// Inclure la classe SimpleSAMLphp
	require_once('./_lib/SimpleSAMLphp/lib/_autoload.php');
	// Initialiser la classe
	$auth = new SimpleSAML_Auth_Simple('distant-gepi-saml');
	// Tester si le user est authentifié, rediriger sinon
	$auth->requireAuth();
	// Récupérer l'identifiant Gepi de l'utilisateur authentifié pour le traiter dans l'application
	$attr = $auth->getAttributes();
	$login_GEPI = $attr['USER_ID_GEPI'][0];
	// Comparer avec les données de la base
	list($auth_resultat,$auth_DB_ROW) = tester_authentification_user($BASE,$login_GEPI,$password=false,$connexion_mode);
	if($auth_resultat!='ok')
	{
		affich_message_exit($titre='Incident authentification',$contenu=$auth_resultat);
	}
	enregistrer_session_user($BASE,$auth_DB_ROW);
	// Pas de redirection car passage possible d'infos en POST à conserver ; tant pis pour la vérification du cookie...
}

?>