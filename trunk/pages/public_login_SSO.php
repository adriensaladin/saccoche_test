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
 * URL directe mono-structure            : http://adresse.com?sso=...
 * URL directe multi-structure normale   : http://adresse.com?sso=...&base=...
 * URL directe multi-structure spéciale  : http://adresse.com?sso=...&uai=...
 * 
 * URL profonde mono-structure           : http://adresse.com?page=...&sso=...
 * URL profonde multi-structure normale  : http://adresse.com?page=...&sso=...&base=...
 * URL profonde multi-structure spéciale : http://adresse.com?page=...&sso=...&uai=...
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
// Récupération des paramètres transmis ou en cookie (à effectuer après le test de l'UAI)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$MODE = (isset($_GET['sso'])) ? clean_login($_GET['sso']) : ( (isset($_COOKIE[COOKIE_AUTHMODE])) ? clean_login($_COOKIE[COOKIE_AUTHMODE]) : 'normal' ) ;
$BASE = (isset($_GET['base'])) ? clean_entier($_GET['base']) : ( (isset($_COOKIE[COOKIE_STRUCTURE])) ? clean_entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ) ;

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
// Identification avec le protocole CAS
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
	// Demander à CAS d'aller interroger le serveur
	// Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
	// (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
	phpCAS::forceAuthentication();
	// Rapatrier les informations si elles sont validées par CAS (qui envoie alors un ticket en GET)
	$auth = phpCAS::checkAuthentication();
	// Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
	$login = phpCAS::getUser();
	// Comparer avec les données de la base
	$connexion = connecter_user($BASE,$login,$password=false,$mode_connection='cas');
	if($connexion!='ok')
	{
		affich_message_exit($titre='Compte inaccessible',$contenu=$connexion);
	}
	// Redirection vers la page demandée en cas de succès.
	// En théorie il faudrait laisser la suite du code se poursuivre, ce qui n'est pas impossible, mais ça pose le souci de la transmission de &verif_cookie
	$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://' ;
	redirection_immediate($protocole.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&verif_cookie');
}

?>