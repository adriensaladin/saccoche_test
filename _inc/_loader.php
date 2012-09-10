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

// ============================================================================
// Config PHP - Versions PHP & MySQL - Modules PHP
// ============================================================================

// Définir le décalage horaire par défaut de toutes les fonctions date/heure
date_default_timezone_set('Europe/Paris'); 

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé
// Présence aussi d'un "AddDefaultCharset ..." dans le fichier .htaccess
// Cependant, tous les fichiers étant en UTF-8 et le code prévu pour manipuler des données en UTF-8, changer le CHARSET semble assez hasardeux pour ne pas dire risqué...
define('CHARSET','utf-8');
// Modifier l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
mb_internal_encoding(CHARSET);

define('PHP_VERSION_MINI_REQUISE'     ,'5.1');
define('PHP_VERSION_MINI_CONSEILLEE'  ,'5.3.4'); // PHP 5.2 n'est plus supporté depuis le 16 décembre 2010.
define('MYSQL_VERSION_MINI_REQUISE'   ,'5.0');
define('MYSQL_VERSION_MINI_CONSEILLEE','5.5'); // Version stable depuis octobre 2010

// Vérifier la version de PHP
if(version_compare(PHP_VERSION,PHP_VERSION_MINI_REQUISE,'<'))
{
	exit_error( 'PHP trop ancien' /*titre*/ , 'Version de PHP utilisée sur ce serveur : '.PHP_VERSION.'<br />Version de PHP requise au minimum : '.PHP_VERSION_MINI_REQUISE /*contenu*/ );
}

// Vérifier la présence des modules nécessaires
$extensions_chargees = get_loaded_extensions();
$extensions_requises = array('curl','dom','gd','mbstring','mysql','PDO','pdo_mysql','session','zip','zlib');
$extensions_manquantes = array_diff($extensions_requises,$extensions_chargees);
if(count($extensions_manquantes))
{
	exit_error( 'PHP incomplet' /*titre*/ , 'Module(s) PHP manquant(s) : '.implode($extensions_manquantes,' ') /*contenu*/ );
}

// Remédier à l'éventuelle configuration de magic_quotes_gpc à On (directive obsolète depuis PHP 5.3.0 et supprimée en PHP 6.0.0).
// array_map() génère une erreur si le tableau contient lui-même un tableau ; à la place on peut utiliser array_walk_recursive() ou la fonction ci-dessous présente dans le code de MySQL_Dumper et PunBB) :
// function stripslashes_array($val){$val = is_array($val) ? array_map('stripslashes_array',$val) : stripslashes($val);return $val;}
if(get_magic_quotes_gpc())
{
	function tab_stripslashes(&$val,$key)
	{
		$val = stripslashes($val);
	}
	array_walk_recursive($_COOKIE ,'tab_stripslashes');
	array_walk_recursive($_GET    ,'tab_stripslashes');
	array_walk_recursive($_POST   ,'tab_stripslashes');
	array_walk_recursive($_REQUEST,'tab_stripslashes');
}

// ============================================================================
// Type de serveur (LOCAL|DEV|PROD)
// ============================================================================

// On ne peut pas savoir avec certitude si un serveur est "local" car aucune méthode ne fonctionne à tous les coups :
// - $_SERVER['HTTP_HOST'] peut ne pas renvoyer localhost sur un serveur local (si configuration de domaines locaux via fichiers hosts / httpd.conf par exemple).
// - gethostbyname($_SERVER['HTTP_HOST']) peut renvoyer "127.0.0.1" sur un serveur non local car un serveur a en général 2 ip (une publique - ou privée s'il est sur un lan - et une locale).
// - $_SERVER['SERVER_ADDR'] peut renvoyer "127.0.0.1" avec nginx + apache sur 127.0.0.1 ...
$test_local = ( ($_SERVER['HTTP_HOST']=='localhost') || ($_SERVER['HTTP_HOST']=='127.0.0.1') || (mb_substr($_SERVER['HTTP_HOST'],-6)=='.local') ) ? TRUE : FALSE ;
$serveur = ($test_local) ? 'LOCAL' : ( (mb_strpos($_SERVER['HTTP_HOST'],'.devsesamath.net')) ? 'DEV' : 'PROD' ) ;
define('SERVEUR_TYPE',$serveur); // PROD | DEV | LOCAL

// ============================================================================
// Fixer le niveau de rapport d'erreurs PHP
// ============================================================================

if(SERVEUR_TYPE == 'PROD')
{
	// Rapporter toutes les erreurs à part les E_NOTICE (c'est la configuration par défaut de php.ini) et E_STRICT qui est englobé dans E_ALL à compter de PHP 5.4.
	ini_set('error_reporting',E_ALL & ~E_STRICT & ~E_NOTICE);
}
else
{
	// Rapporter toutes les erreurs PHP sur le serveur local (http://fr.php.net/manual/fr/errorfunc.constants.php)
	ini_set('error_reporting',E_ALL | E_STRICT);
}

// ============================================================================
// Chemins dans le système de fichiers du serveur (pour des manipulations de fichiers locaux)
// ============================================================================

// Vers le dossier d'installation de l'application SACoche, avec séparateur final.
define('DS',DIRECTORY_SEPARATOR);
define('CHEMIN_DOSSIER_SACOCHE'  , realpath(dirname(dirname(__FILE__))).DS);
define('LONGUEUR_CHEMIN_SACOCHE' , strlen(CHEMIN_DOSSIER_SACOCHE)-1);

// Vers des sous-dossiers, avec séparateur final.
define('CHEMIN_DOSSIER_PRIVATE'       , CHEMIN_DOSSIER_SACOCHE.'__private'.DS);
define('CHEMIN_DOSSIER_TMP'           , CHEMIN_DOSSIER_SACOCHE.'__tmp'.DS);
define('CHEMIN_DOSSIER_IMG'           , CHEMIN_DOSSIER_SACOCHE.'_img'.DS);
define('CHEMIN_DOSSIER_INCLUDE'       , CHEMIN_DOSSIER_SACOCHE.'_inc'.DS);
define('CHEMIN_DOSSIER_FPDF_FONT'     , CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'FPDF'.DS.'font'.DS);
define('CHEMIN_DOSSIER_SQL'           , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS);
define('CHEMIN_DOSSIER_SQL_STRUCTURE' , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS.'structure'.DS);
define('CHEMIN_DOSSIER_SQL_WEBMESTRE' , CHEMIN_DOSSIER_SACOCHE.'_sql'.DS.'webmestre'.DS);
define('CHEMIN_DOSSIER_PAGES'         , CHEMIN_DOSSIER_SACOCHE.'pages'.DS);
define('CHEMIN_DOSSIER_WEBSERVICES'   , CHEMIN_DOSSIER_SACOCHE.'webservices'.DS);
define('CHEMIN_DOSSIER_CONFIG'        , CHEMIN_DOSSIER_PRIVATE.'config'.DS);
define('CHEMIN_DOSSIER_LOG'           , CHEMIN_DOSSIER_PRIVATE.'log'.DS);
define('CHEMIN_DOSSIER_MYSQL'         , CHEMIN_DOSSIER_PRIVATE.'mysql'.DS);
define('CHEMIN_DOSSIER_BADGE'         , CHEMIN_DOSSIER_TMP.'badge'.DS);
define('CHEMIN_DOSSIER_COOKIE'        , CHEMIN_DOSSIER_TMP.'cookie'.DS);
define('CHEMIN_DOSSIER_DEVOIR'        , CHEMIN_DOSSIER_TMP.'devoir'.DS);
define('CHEMIN_DOSSIER_DUMP'          , CHEMIN_DOSSIER_TMP.'dump-base'.DS);
define('CHEMIN_DOSSIER_EXPORT'        , CHEMIN_DOSSIER_TMP.'export'.DS);
define('CHEMIN_DOSSIER_IMPORT'        , CHEMIN_DOSSIER_TMP.'import'.DS);
define('CHEMIN_DOSSIER_LOGINPASS'     , CHEMIN_DOSSIER_TMP.'login-mdp'.DS);
define('CHEMIN_DOSSIER_LOGO'          , CHEMIN_DOSSIER_TMP.'logo'.DS);
define('CHEMIN_DOSSIER_OFFICIEL'      , CHEMIN_DOSSIER_TMP.'officiel'.DS);
define('CHEMIN_DOSSIER_RSS'           , CHEMIN_DOSSIER_TMP.'rss'.DS);
define('CHEMIN_FICHIER_CONFIG_INSTALL', CHEMIN_DOSSIER_CONFIG.'constantes.php');
//      CHEMIN_FICHIER_CONFIG_MYSQL     est défini dans index.php ou ajax.php, en fonction du type d'installation et d'utilisateur connecté
define('FPDF_FONTPATH'                , CHEMIN_DOSSIER_FPDF_FONT); // Pour FPDF (répertoire où se situent les polices)

// Si appel depuis /_img/php/etiquette.php alors on n'a pas besoin d'aller plus loin dans ce fichier inclus.
if(SACoche=='etiquette') return;

// ============================================================================
// URLs de l'application (les chemins restent relatifs pour les images ou les css/js...)
// ============================================================================

// Il arrive (très rarement) que HTTP_HOST ne soit pas défini (http 1.1 impose au client web de préciser un nom de site, ce qui n'était pas le cas en http 1.0 ; http 1.1 date de 1999, avec un brouillon en 1996).
$_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ;
$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://';
$url = $protocole.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$fin = mb_strpos($url,SACoche);
if($fin)
{
	$url = mb_substr($url,0,$fin-1);
}
// Il manque "/sacoche" à l'URL si appelé depuis le projet
if(defined('APPEL_SITE_PROJET'))
{
	$url .= '/sacoche';
}
define('URL_INSTALL_SACOCHE',$url); // la seule constante sans slash final
define('URL_DIR_SACOCHE',$url.'/'); // avec slash final
$tab_bad = array( CHEMIN_DOSSIER_SACOCHE , DS );
$tab_bon = array( URL_DIR_SACOCHE        , '/');
define('URL_DIR_TMP'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_TMP       ) );
define('URL_DIR_IMG'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_IMG       ) );
define('URL_DIR_DEVOIR'   , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_DEVOIR    ) );
define('URL_DIR_DUMP'     , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_DUMP      ) );
define('URL_DIR_EXPORT'   , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_EXPORT    ) );
define('URL_DIR_LOGINPASS', str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_LOGINPASS ) );
define('URL_DIR_LOGO'     , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_LOGO      ) );
define('URL_DIR_RSS'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_RSS       ) );

// ============================================================================
// URL externes appelées par l'application
// ============================================================================

define('SERVEUR_PROJET'        ,'https://sacoche.sesamath.net');         // URL du projet SACoche (en https depuis le 08/02/2012)
define('SERVEUR_SSL'           ,'https://sacoche.sesamath.net');         // URL du serveur Sésamath sécurisé (idem serveur projet SACoche depuis le 08/02/2012)
define('SERVEUR_COMMUNAUTAIRE' ,SERVEUR_PROJET.'/appel_externe.php');    // URL du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
define('SERVEUR_DOCUMENTAIRE'  ,SERVEUR_PROJET.'/appel_doc.php');        // URL du fichier chargé d'afficher les documentations
define('SERVEUR_LPC_SIGNATURE' ,SERVEUR_SSL   .'/appel_externe.php');    // URL du fichier chargé de signer un XML à importer dans LPC
define('SERVEUR_TELECHARGEMENT',SERVEUR_PROJET.'/telechargement.php');   // URL du fichier renvoyant le ZIP de la dernière archive de SACoche disponible
define('SERVEUR_VERSION'       ,SERVEUR_PROJET.'/sacoche/VERSION.txt');  // URL du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_CNIL'          ,SERVEUR_PROJET.'/?fichier=cnil');        // URL de la page "CNIL (données personnelles)"
define('SERVEUR_CONTACT'       ,SERVEUR_PROJET.'/?fichier=contact');     // URL de la page "Où échanger autour de SACoche ?"
define('SERVEUR_GUIDE_ADMIN'   ,SERVEUR_PROJET.'/?fichier=guide_admin'); // URL de la page "Guide d'un administrateur de SACoche"
define('SERVEUR_NEWS'          ,SERVEUR_PROJET.'/?fichier=news');        // URL de la page "Historique des nouveautés"
define('SERVEUR_RSS'           ,SERVEUR_PROJET.'/_rss/rss.xml');         // URL du fichier comportant le flux RSS

// ============================================================================
// Autres constantes diverses... et parfois importantes !
// ============================================================================

// Identifiants particuliers (à ne pas modifier)
define('ID_DEMO'                   ,9999); // id de l'établissement de démonstration (pour $_SESSION['SESAMATH_ID']) ; 0 pose des pbs, et il fallait prendre un id disponible dans la base d'établissements de Sésamath
define('ID_MATIERE_PARTAGEE_MAX'   ,9999); // id de la matière transversale dans la table "sacoche_matiere" ; c'est l'id maximal des matières partagées (les id des matières spécifiques sont supérieurs)
define('ID_NIVEAU_MAX'             ,1000); // Un id de niveau supérieur correspond à un id de famille qui a été incrémenté de cette constante
define('ID_FAMILLE_MATIERE_USUELLE',  99);

// cookies
define('COOKIE_STRUCTURE','SACoche-etablissement');  // nom du cookie servant à retenir l'établissement sélectionné, afin de ne pas à avoir à le sélectionner de nouveau, et à pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_AUTHMODE' ,'SACoche-mode-connexion'); // nom du cookie servant à retenir le dernier mode de connexion utilisé par un user connecté, afin de pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_DEBUG'    ,'SACoche-debug');          // nom du cookie servant à retenir si le mode debug est activé (pas en PROD).

// session
define('SESSION_NOM','SACoche-session'); // Est aussi défini dans /_lib/SimpleSAMLphp/config/config.php

// dates
define('TODAY_FR'    ,date("d/m/Y"));
define('TODAY_MYSQL' ,date("Y-m-d"));
define('SORTIE_DEFAUT_FR'    ,'31/12/9999'); // inutilisé
define('SORTIE_DEFAUT_MYSQL' ,'9999-12-31');

// Version des fichiers installés.
// À comparer avec la dernière version disponible sur le serveur communautaire.
// Pour une conversion en entier : list($annee,$mois,$jour) = explode('-',substr(VERSION_PROG,0,10); $indice_version = (date('Y')-2011)*365 + date('z',mktime(0,0,0,$mois,$jour,$annee));
define('VERSION_PROG', file_get_contents(CHEMIN_DOSSIER_SACOCHE.'VERSION.txt') ); // Dans un fichier texte pour permettre un appel au serveur communautaire sans lui faire utiliser PHP.

// Version de la base associée.
// À comparer avec la version de la base actuellement en place.
define('VERSION_BASE', file_get_contents(CHEMIN_DOSSIER_SQL.'version_bdd.txt') ); // Dans un fichier texte pour faciliter la maintenance par les développeurs.

// ============================================================================
// Auto-chargement des classes
// ============================================================================

/**
 * Inclusion d'un fichier ou exit(message d'erreur)
 * 
 * @param string   $class_name   nom de la classe
 * @param string   $chemin       chemin vers le fichier de la classe
 * @return void
 */
function load_class($class_name,$chemin)
{
	if(is_file($chemin))
	{
		require($chemin);
	}
	else
	{
		exit_error( 'Classe introuvable' /*titre*/ , 'Le chemin de la classe '.$class_name.' est incorrect : '.$chemin /*contenu*/ );
	}
}

/**
 * Auto-chargement des classes (aucune inclusion de classe n'est nécessaire, elles sont chargées par cette fonction suivant les besoins).
 * 
 * @param string   $class_name   nom de la classe
 * @return void
 */
function __autoload($class_name)
{
	$tab_classes = array(
		'DB'                          => '_lib'.DS.'DB'.DS.'DB.class.php' ,
		'FirePHP'                     => '_lib'.DS.'FirePHPCore'.DS.'FirePHP.class.php' ,
		'FPDF'                        => '_lib'.DS.'FPDF'.DS.'fpdf.php' ,
		'PDF_Label'                   => '_lib'.DS.'FPDF'.DS.'PDF_Label.php' ,
		'FPDI'                        => '_lib'.DS.'FPDI'.DS.'fpdi.php' ,
		'PDFMerger'                   => '_lib'.DS.'FPDI'.DS.'PDFMerger.php' ,
		'phpCAS'                      => '_lib'.DS.'phpCAS'.DS.'CAS.php' ,

		'Browser'                     => '_inc'.DS.'class.Browser.php' ,
		'Clean'                       => '_inc'.DS.'class.Clean.php' ,
		'cssmin'                      => '_inc'.DS.'class.CssMinified.php' ,
		'FileSystem'                  => '_inc'.DS.'class.FileSystem.php' ,
		'Form'                        => '_inc'.DS.'class.Form.php' ,
		'Html'                        => '_inc'.DS.'class.Html.php' ,
		'InfoServeur'                 => '_inc'.DS.'class.InfoServeur.php' ,
		'LockAcces'                   => '_inc'.DS.'class.LockAcces.php' ,
		'MyDOMDocument'               => '_inc'.DS.'class.domdocument.php' ,
		'JSMin'                       => '_inc'.DS.'class.JavaScriptMinified.php' ,
		'JavaScriptPacker'            => '_inc'.DS.'class.JavaScriptPacker.php' ,
		'PDF'                         => '_inc'.DS.'class.PDF.php' ,
		'SACocheLog'                  => '_inc'.DS.'class.SACocheLog.php' ,
		'Sesamail'                    => '_inc'.DS.'class.Sesamail.php' ,
		'Session'                     => '_inc'.DS.'class.Session.php' ,
		'To'                          => '_inc'.DS.'class.To.php' ,

		'DB_STRUCTURE_ADMINISTRATEUR' => '_sql'.DS.'requetes_structure_administrateur.php' ,
		'DB_STRUCTURE_DIRECTEUR'      => '_sql'.DS.'requetes_structure_directeur.php' ,
		'DB_STRUCTURE_ELEVE'          => '_sql'.DS.'requetes_structure_eleve.php' ,
		'DB_STRUCTURE_PROFESSEUR'     => '_sql'.DS.'requetes_structure_professeur.php' ,
		'DB_STRUCTURE_PUBLIC'         => '_sql'.DS.'requetes_structure_public.php' ,
		'DB_STRUCTURE_WEBMESTRE'      => '_sql'.DS.'requetes_structure_webmestre.php' ,

		'DB_STRUCTURE_BILAN'          => '_sql'.DS.'requetes_structure_bilan.php' ,
		'DB_STRUCTURE_OFFICIEL'       => '_sql'.DS.'requetes_structure_officiel.php' ,
		'DB_STRUCTURE_COMMUN'         => '_sql'.DS.'requetes_structure_commun.php' ,
		'DB_STRUCTURE_MAJ_BASE'       => '_sql'.DS.'requetes_structure_maj_base.php' ,
		'DB_STRUCTURE_REFERENTIEL'    => '_sql'.DS.'requetes_structure_referentiel.php' ,
		'DB_STRUCTURE_SOCLE'          => '_sql'.DS.'requetes_structure_socle.php' ,

		'DB_WEBMESTRE_PUBLIC'         => '_sql'.DS.'requetes_webmestre_public.php' ,
		'DB_WEBMESTRE_SELECT'         => '_sql'.DS.'requetes_webmestre_select.php' ,
		'DB_WEBMESTRE_WEBMESTRE'      => '_sql'.DS.'requetes_webmestre_webmestre.php'
	);
	if(isset($tab_classes[$class_name]))
	{
		load_class($class_name,CHEMIN_DOSSIER_SACOCHE.$tab_classes[$class_name]);
	}
	// Remplacement de l'autoload de phpCAS qui n'est pas chargé à cause de celui de SACoche
	// Voir le fichier ./_lib/phpCAS/CAS/autoload.php
	elseif(mb_substr($class_name,0,4)=='CAS_')
	{
		load_class($class_name,CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'phpCAS'.DS.str_replace('_',DS,$class_name).'.php');
	}
	// Remplacement de l'autoload de SimpleSAMLphp qui n'est pas chargé à cause de celui de SACoche
	// Voir le fichier ./_lib/SimpleSAMLphp/lib/_autoload.php
	else if(in_array($class_name, array('XMLSecurityKey', 'XMLSecurityDSig', 'XMLSecEnc'), TRUE))
	{
		load_class($class_name,CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'SimpleSAMLphp'.DS.'lib'.DS.'xmlseclibs.php');
	}
	else if(mb_substr($class_name,0,7)=='sspmod_')
	{
		$modNameEnd  = mb_strpos($class_name, '_', 7);
		$module      = mb_substr($class_name, 7, $modNameEnd - 7);
		$moduleClass = mb_substr($class_name, $modNameEnd + 1);
		if(SimpleSAML_Module::isModuleEnabled($module))
		{
			load_class($class_name,SimpleSAML_Module::getModuleDir($module).'/lib/'.str_replace('_', DS, $moduleClass).'.php');
		}
	}
	elseif( (mb_substr($class_name,0,5)=='SAML2') || (mb_substr($class_name,0,10)=='SimpleSAML') )
	{
		load_class($class_name,CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'SimpleSAMLphp'.DS.'lib'.DS.str_replace('_','/',$class_name).'.php');
	}
	// La classe invoquée ne correspond pas à ce qui vient d'être passé en revue
	else
	{
		exit_error( 'Classe introuvable' /*titre*/ , 'La classe '.$class_name.' est inconnue.' /*contenu*/ );
	}
}

// ============================================================================
// Quelques fonctions utiles : perso_mb_detect_encoding_utf8() - augmenter_memory_limit() - rapporter_erreur_fatale() - exit_error()
// ============================================================================

/**
 * Fonction pour remplacer mb_detect_encoding() à cause d'un bug : http://fr2.php.net/manual/en/function.mb-detect-encoding.php#81936
 *
 * @param string
 * @return string
 */
function perso_mb_detect_encoding_utf8($text)
{
	return (mb_detect_encoding($text.' ','auto',TRUE)=='UTF-8');
}

/*
 * Augmenter le memory_limit (si autorisé) pour les pages les plus gourmandes
 * 
 * @param void
 * @return void
 */
function augmenter_memory_limit()
{
	if( (int)ini_get('memory_limit') < 256 )
	{
		@ini_set('memory_limit','256M');
		@ini_alter('memory_limit','256M');
	}
}

/*
 * Pour intercepter les erreurs de dépassement de mémoire (une erreur fatale échappe à un try{...}catch(){...}).
 *
 * Source : http://pmol.fr/programmation/web/la-gestion-des-erreurs-en-php/
 * Mais ça ne fonctionne pas en CGI : PHP a déjà envoyé l'erreur 500 et cette fonction est appelée trop tard, PHP n'a plus la main.
 * Pour avoir des informations accessibles en cas d'erreur type « PHP Fatal error : Allowed memory size of ... bytes exhausted » on peut aussi mettre dans les pages sensibles :
 * ajouter_log_PHP( 'Demande de bilan' , serialize($_POST) , __FILE__ , __LINE__ , TRUE );
 * 
 * @param void
 * @return void
 */
function rapporter_erreur_fatale()
{
	$tab_last_error = error_get_last(); // tableau à 4 indices : type ; message ; file ; line
	if( ($tab_last_error!==NULL) && ($tab_last_error['type']===E_ERROR) && (mb_substr($tab_last_error['message'],0,19)=='Allowed memory size') )
	{
		exit_error( 'Mémoire insuffisante' /*titre*/ , 'Mémoire de '.ini_get('memory_limit').' insuffisante ; sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' /*contenu*/ );
	}
}

/*
 * Afficher une page HTML minimaliste avec un message explicatif et un lien pour retourner en page d'accueil (si AJAX, renvoyer juste un message).
 * 
 * @param string $titre     titre de la page
 * @param string $contenu   contenu HTML affiché (ou AJAX retourné)
 * @param bool   $setup     facultatif ; TRUE pour un lien vers la procédure d'installation au lieu d'un lien vers l'accueil.
 * @return void
 */
function exit_error($titre,$contenu,$setup=FALSE)
{
	if(SACoche=='index')
	{
		$tab_lien = ($setup) ? array('href'=>'index.php?page=public_installation','txt'=>'Procédure d\'installation') : array('href'=>'index.php','txt'=>'Retour en page d\'accueil') ;
		header('Content-Type: text/html; charset='.CHARSET);
		echo'<!DOCTYPE html>';
		echo'<html>';
		echo'<head><meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" /><title>SACoche » '.$titre.'</title></head>';
		echo'<body style="background:#EAEAFF;font:15px sans-serif;color:#D00">';
		echo'<p>'.$contenu.'</p>';
		echo'<p><a href="./'.$tab_lien['href'].'">'.$tab_lien['txt'].' de SACoche.</a></p>';
		echo'</body>';
		echo'</html>';
	}
	else
	{
		echo $contenu;
	}
	exit();
}

// ============================================================================
// Débogage - FirePHP
// ============================================================================

if(SERVEUR_TYPE=='PROD')
{
	// pas de DEBUG en PROD
	define('DEBUG',FALSE);
}
elseif(isset($_GET['debug']))
{
	if($_GET['debug'])
	{
		// demande explicite d'activer le mode DEBUG
		define('DEBUG',TRUE);
		setcookie(COOKIE_DEBUG,1,0,'');
	}
	else
	{
		// demande explicite de désactiver le mode DEBUG
		define('DEBUG',FALSE);
		setcookie(COOKIE_DEBUG,'',time()-42000,'');
	}
}
elseif(isset($_COOKIE[COOKIE_DEBUG]))
{
	// mode DEBUG à conserver
	define('DEBUG',TRUE);
}
else
{
	// pas de mode DEBUG à conserver
	define('DEBUG',FALSE);
}

/*
 * Pour FirePHP
 */
if(DEBUG)
{
	ini_set('output_buffering','On');
	$firephp = FirePHP::getInstance(TRUE);
}

function afficher_infos_debug()
{
	global $firephp;
	$firephp->dump('COOKIE', $_COOKIE);
	$firephp->dump('FILES', $_FILES);
	$firephp->dump('GET', $_GET);
	$firephp->dump('POST', $_POST);
	$firephp->dump('SESSION', $_SESSION);
	$tab_constantes = get_defined_constants(TRUE);
	$firephp->dump('CONSTANTES', $tab_constantes['user']);
}

// ============================================================================
// Fonctions non disponibles en PHP 5.1
// ============================================================================

/*
 * La fonction error_get_last() n'est disponible que depuis PHP 5.2 ; SACoche exigeant PHP 5.1, la définir si besoin.
 * http://fr.php.net/manual/fr/function.error-get-last.php#103539
 */
if(!function_exists('error_get_last'))
{
	set_error_handler(
			create_function(
				'$errno,$errstr,$errfile,$errline,$errcontext',
				'
					global $__error_get_last_retval__;
					$__error_get_last_retval__ = array(
						\'type\'    => $errno,
						\'message\' => $errstr,
						\'file\'    => $errfile,
						\'line\'    => $errline
					);
					return NULL;
				'
			)
	);
	function error_get_last()
	{
		global $__error_get_last_retval__;
		if( !isset($__error_get_last_retval__) )
		{
			return NULL;
		}
		return $__error_get_last_retval__;
	}
}

/*
 * La fonction array_fill_keys() n'est disponible que depuis PHP 5.2 ; SACoche exigeant PHP 5.1, la définir si besoin.
 */
if(!function_exists('array_fill_keys'))
{
	function array_fill_keys($tab_clefs,$valeur)
	{
		return array_combine( $tab_clefs , array_fill(0,count($tab_clefs),$valeur) );
	}
}

?>