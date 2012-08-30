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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Vérifier la version de PHP
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$version_php_mini = '5.1';
if(version_compare(PHP_VERSION,$version_php_mini,'<'))
{
	affich_message_exit($titre='PHP trop ancien',$contenu='Version de PHP utilisée sur ce serveur : '.PHP_VERSION.'<br />Version de PHP requise au minimum : '.$version_php_mini);
}
$version_mysql_mini = '5.0'; // Pour l'installation

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Vérifier la présence des modules nécessaires
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$extensions_chargees = get_loaded_extensions();
$extensions_requises = array('curl','dom','gd','mbstring','mysql','PDO','pdo_mysql','session','zip','zlib');
$extensions_manquantes = array_diff($extensions_requises,$extensions_chargees);
if(count($extensions_manquantes))
{
	affich_message_exit($titre='PHP incomplet',$contenu='Les modules PHP suivants sont manquants : '.implode($extensions_manquantes,' '));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// La fonction error_get_last() n'est disponible que depuis PHP 5.2 ; SACoche exigeant PHP 5.1, la définir si besoin.
// http://fr.php.net/manual/fr/function.error-get-last.php#103539
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// La fonction array_fill_keys() n'est disponible que depuis PHP 5.2 ; SACoche exigeant PHP 5.1, la définir si besoin.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if(!function_exists('array_fill_keys'))
{
	function array_fill_keys($tab_clefs,$valeur)
	{
		return array_combine( $tab_clefs , array_fill(0,count($tab_clefs),$valeur) );
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Fixer le niveau de rapport d'erreurs PHP
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Définir le décalage horaire par défaut de toutes les fonctions date/heure 
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

@date_default_timezone_set('Europe/Paris');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ne pas échapper les apostrophes pour Get/Post/Cookie
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('magic_quotes_gpc',0);
ini_set('magic_quotes_sybase',0);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ne pas enregistrer les variables Environment/GET/POST/Cookie/Server comme des variables globales.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// register_globals ne peut pas être définit durant le traitement avec "ini_set"...
// ini_set(register_globals,0);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Durée de vie des données (session...) sur le serveur, en nombre de secondes.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('session.gc_maxlifetime',3000);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Protection contre les attaques qui utilisent des identifiants de sessions dans les URL.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('session.use_trans_sid', 0); 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('session.use_only_cookies',1);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ne pas autoriser les balises courtes d'ouverture de PHP (et possibilité d'utiliser XML sans passer par echo).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('short_open_tag',0);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Désactiver le mode de compatibilité avec le Zend Engine 1 (PHP 4).
// Sinon l'utilisation de "simplexml_load_string()" ou "DOMDocument" (par exemples) provoquent des erreurs fatales, + incompatibilité avec classe PDO.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

ini_set('zend.ze1_compatibility_mode',0);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

mb_internal_encoding(CHARSET);

/**
 * Auto-chargement des classes (aucune inclusion de classe n'est nécessaire, elles sont chargées par cette fonction suivant les besoins).
 * 
 * @param string   $class_name   nom de la classe
 * @return void
 */
function load_class($class_name,$chemin)
{
	if(is_file($chemin))
	{
		require_once($chemin);
	}
	else
	{
		affich_message_exit($titre='Classe introuvable',$contenu='Le chemin de la classe '.$class_name.' est incorrect : '.$chemin);
	}
}
function __autoload($class_name)
{
	$tab_classes = array(
		'DB'                          => '_lib'.DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.'DB.class.php' ,
		'FirePHP'                     => '_lib'.DIRECTORY_SEPARATOR.'FirePHPCore'.DIRECTORY_SEPARATOR.'FirePHP.class.php' ,
		'FPDF'                        => '_lib'.DIRECTORY_SEPARATOR.'FPDF'.DIRECTORY_SEPARATOR.'fpdf.php' ,
		'PDF_Label'                   => '_lib'.DIRECTORY_SEPARATOR.'FPDF'.DIRECTORY_SEPARATOR.'PDF_Label.php' ,
		'FPDI'                        => '_lib'.DIRECTORY_SEPARATOR.'FPDI'.DIRECTORY_SEPARATOR.'fpdi.php' ,
		'PDFMerger'                   => '_lib'.DIRECTORY_SEPARATOR.'FPDI'.DIRECTORY_SEPARATOR.'PDFMerger.php' ,
		'phpCAS'                      => '_lib'.DIRECTORY_SEPARATOR.'phpCAS'.DIRECTORY_SEPARATOR.'CAS.php' ,

		'cssmin'                      => '_inc'.DIRECTORY_SEPARATOR.'class.CssMinified.php' ,
		'MyDOMDocument'               => '_inc'.DIRECTORY_SEPARATOR.'class.domdocument.php' ,
		'JSMin'                       => '_inc'.DIRECTORY_SEPARATOR.'class.JavaScriptMinified.php' ,
		'JavaScriptPacker'            => '_inc'.DIRECTORY_SEPARATOR.'class.JavaScriptPacker.php' ,
		'PDF'                         => '_inc'.DIRECTORY_SEPARATOR.'class.PDF.php' ,

		'Formulaire'                  => '_inc'.DIRECTORY_SEPARATOR.'class.formulaire.php' ,

		'DB_STRUCTURE_ADMINISTRATEUR' => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_administrateur.php' ,
		'DB_STRUCTURE_DIRECTEUR'      => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_directeur.php' ,
		'DB_STRUCTURE_ELEVE'          => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_eleve.php' ,
		'DB_STRUCTURE_PROFESSEUR'     => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_professeur.php' ,
		'DB_STRUCTURE_PUBLIC'         => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_public.php' ,
		'DB_STRUCTURE_WEBMESTRE'      => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_webmestre.php' ,

		'DB_STRUCTURE_BILAN'          => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_bilan.php' ,
		'DB_STRUCTURE_OFFICIEL'       => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_officiel.php' ,
		'DB_STRUCTURE_COMMUN'         => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_commun.php' ,
		'DB_STRUCTURE_MAJ_BASE'       => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_maj_base.php' ,
		'DB_STRUCTURE_REFERENTIEL'    => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_referentiel.php' ,
		'DB_STRUCTURE_SOCLE'          => '_sql'.DIRECTORY_SEPARATOR.'requetes_structure_socle.php' ,

		'DB_WEBMESTRE_PUBLIC'         => '_sql'.DIRECTORY_SEPARATOR.'requetes_webmestre_public.php' ,
		'DB_WEBMESTRE_SELECT'         => '_sql'.DIRECTORY_SEPARATOR.'requetes_webmestre_select.php' ,
		'DB_WEBMESTRE_WEBMESTRE'      => '_sql'.DIRECTORY_SEPARATOR.'requetes_webmestre_webmestre.php'
	);
	if(isset($tab_classes[$class_name]))
	{
		load_class($class_name,CHEMIN_SACOCHE.$tab_classes[$class_name]);
	}
	// Remplacement de l'autoload de phpCAS qui n'est pas chargé à cause de celui de SACoche
	// Voir le fichier ./_lib/phpCAS/CAS/autoload.php
	elseif(substr($class_name,0,4)=='CAS_')
	{
		load_class($class_name,CHEMIN_SACOCHE.'_lib'.DIRECTORY_SEPARATOR.'phpCAS'.DIRECTORY_SEPARATOR.str_replace('_',DIRECTORY_SEPARATOR,$class_name).'.php');
	}
	// Remplacement de l'autoload de SimpleSAMLphp qui n'est pas chargé à cause de celui de SACoche
	// Voir le fichier ./_lib/SimpleSAMLphp/lib/_autoload.php
	else if(in_array($class_name, array('XMLSecurityKey', 'XMLSecurityDSig', 'XMLSecEnc'), TRUE))
	{
		load_class($class_name,CHEMIN_SACOCHE.'_lib'.DIRECTORY_SEPARATOR.'SimpleSAMLphp'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xmlseclibs.php');
	}
	else if(substr($class_name,0,7)=='sspmod_')
	{
		$modNameEnd  = strpos($class_name, '_', 7);
		$module      = substr($class_name, 7, $modNameEnd - 7);
		$moduleClass = substr($class_name, $modNameEnd + 1);
		if(SimpleSAML_Module::isModuleEnabled($module))
		{
			load_class($class_name,SimpleSAML_Module::getModuleDir($module).'/lib/'.str_replace('_', DIRECTORY_SEPARATOR, $moduleClass).'.php');
		}
	}
	elseif( (substr($class_name,0,5)=='SAML2') || (substr($class_name,0,10)=='SimpleSAML') )
	{
		load_class($class_name,CHEMIN_SACOCHE.'_lib'.DIRECTORY_SEPARATOR.'SimpleSAMLphp'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.str_replace('_','/',$class_name).'.php');
	}
	// La classe invoquée ne correspond pas à ce qui vient d'être passé en revue
	else
	{
		affich_message_exit($titre='Classe introuvable',$contenu='La classe '.$class_name.' est inconnue.');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Pour FirePHP
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Augmenter le memory_limit (si autorisé) pour les pages les plus gourmandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function augmenter_memory_limit()
{
	if( (int)ini_get('memory_limit') < 256 )
	{
		@ini_set('memory_limit','256M');
		@ini_alter('memory_limit','256M');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Pour intercepter les erreurs de dépassement de mémoire (une erreur fatale échappe à un try{...}catch(){...}).
// Source : http://pmol.fr/programmation/web/la-gestion-des-erreurs-en-php/
// Mais ça ne fonctionne pas en CGI : PHP a déjà envoyé l'erreur 500 et cette fonction est appelée trop tard, PHP n'a plus la main.
// Pour avoir des informations accessibles en cas d'erreur type « PHP Fatal error : Allowed memory size of ... bytes exhausted » on peut aussi mettre dans les pages sensibles :
// ajouter_log_PHP( 'Demande de bilan' /*log_objet*/ , serialize($_POST) /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , TRUE /*only_sesamath*/ );
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function rapporter_erreur_fatale()
{
	$tab_last_error = error_get_last(); // tableau à 4 indices : type ; message ; file ; line
	if( ($tab_last_error!==NULL) && ($tab_last_error['type']===E_ERROR) && (substr($tab_last_error['message'],0,19)=='Allowed memory size') )
	{
		affich_message_exit($titre='Mémoire insuffisante',$contenu='Mémoire de '.ini_get('memory_limit').' insuffisante ; sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".');
	}
}

?>