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

// Vérifier la version de PHP
$version_php_mini = '5.1';
if(version_compare(PHP_VERSION,$version_php_mini,'<'))
{
	affich_message_exit($titre='PHP trop ancien',$contenu='Version de PHP utilisée sur ce serveur : '.PHP_VERSION.'<br />Version de PHP requise au minimum : '.$version_php_mini);
}
$version_mysql_mini = '5.0'; // Pour l'installation

// Vérifier la présence des modules nécessaires
$extensions_chargees = get_loaded_extensions();
$extensions_requises = array('curl','dom','gd','mbstring','mysql','PDO','pdo_mysql','session','zip','zlib');
$extensions_manquantes = array_diff($extensions_requises,$extensions_chargees);
if(count($extensions_manquantes))
{
	affich_message_exit($titre='PHP incomplet',$contenu='Les modules PHP suivants sont manquants : '.implode($extensions_manquantes,' '));
}

// La fonction array_fill_keys() n'est disponible que depuis PHP 5.2 ; SACoche exigeant PHP 5.1, la définir si besoin.
if(!function_exists('array_fill_keys'))
{
	function array_fill_keys($tab_clefs,$valeur)
	{
		return array_combine( $tab_clefs , array_fill(0,count($tab_clefs),$valeur) );
	}
}

// Fixer le niveau de rapport d'erreurs PHP
if(SERVEUR_TYPE == 'PROD')
{
	// Rapporter toutes les erreurs à part les E_NOTICE ; c'est la configuration par défaut de php.ini.
	ini_set('error_reporting',E_ALL ^ E_NOTICE);
}
else
{
	// Rapporter toutes les erreurs PHP sur le serveur local
	ini_set('error_reporting',E_ALL);
}

// Définir le décalage horaire par défaut de toutes les fonctions date/heure 
@date_default_timezone_set('Europe/Paris');

// Ne pas échapper les apostrophes pour Get/Post/Cookie
ini_set('magic_quotes_gpc',0);
ini_set('magic_quotes_sybase',0);

// Ne pas enregistrer les variables Environment/GET/POST/Cookie/Server comme des variables globales.
// register_globals ne peut pas être définit durant le traitement avec "ini_set"...
// ini_set(register_globals,0);

// Durée de vie des données (session...) sur le serveur, en nombre de secondes.
ini_set('session.gc_maxlifetime',3000);
// Le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.
// Protection contre les attaques qui utilisent des identifiants de sessions dans les URL.
ini_set('session.use_trans_sid', 0); 
ini_set('session.use_only_cookies',1);

// Ne pas autoriser les balises courtes d'ouverture de PHP (et possibilité d'utiliser XML sans passer par echo).
ini_set('short_open_tag',0);

// Désactiver le mode de compatibilité avec le Zend Engine 1 (PHP 4).
// Sinon l'utilisation de "simplexml_load_string()" ou "DOMDocument" (par exemples) provoquent des erreurs fatales, + incompatibilité avec classe PDO.
ini_set('zend.ze1_compatibility_mode',0);

// Modifier l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
mb_internal_encoding(CHARSET);

/**
 * Auto-chargement des classes (aucune inclusion de classe n'est nécessaire, elles sont chargées par cette fonction suivant les besoins).
 * 
 * @param string   $class_name   nom de la classe
 * @return void
 */
function __autoload($class_name)
{
	$tab_classes = array(
		'DB'                          => '_lib'.DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.'DB.class.php' ,
		'FirePHP'                     => '_lib'.DIRECTORY_SEPARATOR.'FirePHPCore'.DIRECTORY_SEPARATOR.'FirePHP.class.php' ,
		'FPDF'                        => '_lib'.DIRECTORY_SEPARATOR.'FPDF'.DIRECTORY_SEPARATOR.'fpdf.php' ,
		'PDF_Label'                   => '_lib'.DIRECTORY_SEPARATOR.'FPDF'.DIRECTORY_SEPARATOR.'PDF_Label.php' ,
		'phpCAS'                      => '_lib'.DIRECTORY_SEPARATOR.'phpCAS'.DIRECTORY_SEPARATOR.'CAS.php' ,
		'SimpleSAML_Auth_Simple'      => '_lib'.DIRECTORY_SEPARATOR.'SimpleSAMLphp'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'_autoload.php' ,

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
		$class_file = CHEMIN_SACOCHE.$tab_classes[$class_name];
		if(is_file($class_file))
		{
			require_once($class_file);
		}
		else
		{
			affich_message_exit($titre='Classe introuvable',$contenu='Le chemin de la classe '.$class_name.' est incorrect : '.$class_file);
		}
	}
}

// Pour FirePHP
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

?>