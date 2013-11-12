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
// Fonctions non disponibles en PHP 5.1
// ============================================================================

/*
 * La fonction error_get_last() n'est disponible que depuis PHP 5.2 ; SACoche n'exigeant que PHP 5.1 minimum, la définir si besoin.
 * @see http://fr.php.net/manual/fr/function.error-get-last.php#103539
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
            \'line\'    => $errline,
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
 * La fonction array_fill_keys() n'est disponible que depuis PHP 5.2 ; SACoche n'exigeant que PHP 5.1 minimum, la définir si besoin.
 */
if(!function_exists('array_fill_keys'))
{
  function array_fill_keys($tab_clefs,$valeur)
  {
    return array_combine( $tab_clefs , array_fill(0,count($tab_clefs),$valeur) );
  }
}

/*
 * La fonction json_encode() n'est disponible que depuis PHP 5.2 ; SACoche n'exigeant que PHP 5.1 minimum, la définir si besoin.
 * @see http://fr.php.net/manual/fr/function.json-encode.php#107968
 * @see http://fr.php.net/manual/fr/function.json-encode.php#113219
 */
if (!function_exists('json_encode'))
{
  function json_encode($data)
  {
    switch ($type = gettype($data))
    {
      case 'NULL':
        return 'null';
      case 'boolean':
        return ($data ? 'true' : 'false');
      case 'integer':
      case 'double':
      case 'float':
        return $data;
      case 'string':
        return '"' . addslashes($data) . '"';
      case 'object':
        $data = get_object_vars($data);
      case 'array':
        $output_index_count = 0;
        $output_indexed = array();
        $output_associative = array();
        foreach ($data as $key => $value)
        {
          $output_indexed[] = json_encode($value);
          $output_associative[] = json_encode($key) . ':' . json_encode($value);
          if ($output_index_count !== NULL && $output_index_count++ !== $key)
          {
            $output_index_count = NULL;
          }
        }
        if ($output_index_count !== NULL)
        {
          return '[' . implode(',', $output_indexed) . ']';
        }
        else
        {
          return '{' . implode(',', $output_associative) . '}';
        }
      default:
        return ''; // Not supported
    }
  }
}

/*
 * Le paramètre PATHINFO_FILENAME de la fonction pathinfo() n'est disponible que depuis PHP 5.2 ; SACoche n'exigeant que PHP 5.1 minimum, traiter ce cas si besoin.
 */
function pathinfo_filename($file)
{
  if(defined('PATHINFO_FILENAME'))
  {
    return pathinfo($file,PATHINFO_FILENAME);
  }
  else
  {
    $position_filename  = strrpos($file,'/');
    $position_filename  = ($position_filename!==FALSE) ? $position_filename+1 : 0 ;
    $position_extension = strrpos($file,'.');
    $filename_length    = ($position_extension!==FALSE) ? $position_extension - $position_filename : strlen($file) ;
    return substr($file,$position_filename,$filename_length);
  }
}

/*
 * La fonction str_getcsv() n'est disponible que depuis PHP 5.3 ; SACoche n'exigeant que PHP 5.1 minimum, la définir si besoin.
 * @see http://us2.php.net/manual/fr/function.str-getcsv.php#111577
 */
if (!function_exists('str_getcsv'))
{
  function str_getcsv($input, $delimiter = ',', $enclosure = '"')
  {

    if( ! preg_match("/[$enclosure]/", $input) )
    {
      return (array)preg_replace(array("/^\\s*/", "/\\s*$/"), '', explode($delimiter, $input));
    }
    $token = "##"; $token2 = "::"; //alternate tokens "\034\034", "\035\035", "%%";
    $t1 = preg_replace(
      array("/\\\[$enclosure]/", "/$enclosure{2}/", "/[$enclosure]\\s*[$delimiter]\\s*[$enclosure]\\s*/", "/\\s*[$enclosure]\\s*/"),
      array($token2, $token2, $token, $token),
      trim(trim(trim($input),$enclosure))
    );
    $a = explode($token, $t1);
    foreach($a as $k=>$v)
    {
      $v = str_replace( $delimiter.$delimiter , $delimiter.' '.$delimiter , $v ); // Patch perso sinon les colonnes vides disparaissent
      if ( preg_match("/^{$delimiter}/", $v) || preg_match("/{$delimiter}$/", $v) )
      {
        $a[$k] = trim($v, $delimiter); $a[$k] = preg_replace("/$delimiter/", "$token", $a[$k]);
      }
    }
    $a = explode($token, implode($token, $a));
    return (array)preg_replace(array("/^\\s/", "/\\s$/", "/$token2/"), array('', '', $enclosure), $a);
  }
}

// ============================================================================
// Constante SACoche - Atteste l'appel de ce fichier avant inclusion d'une autre page & permet de connaître le nom du script initial.
// ============================================================================

define( 'SACoche', pathinfo_filename($_SERVER['SCRIPT_NAME']) );
if(SACoche=='_loader') {exit('Ce fichier ne peut être appelé directement !');}

// ============================================================================
// Config PHP - Versions PHP & MySQL - Modules PHP
// ============================================================================

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé
// Dès maintenant car utilisé par exit_error().
// Tous les fichiers étant en UTF-8, et le code prévu pour manipuler des données en UTF-8, changer le CHARSET serait assez hasardeux pour ne pas dire risqué...
define('CHARSET','utf-8');
define('NL',PHP_EOL);

// Version PHP & MySQL requises et conseillées
// Attention : ne pas mettre de ".0" (par exemple "5.0") car version_compare() considère que 5 < 5.0 (@see http://fr.php.net/version_compare)
define('PHP_VERSION_MINI_REQUISE'     ,'5.1');
define('PHP_VERSION_MINI_CONSEILLEE'  ,'5.3.4'); // PHP 5.2 n'est plus supporté depuis le 16 décembre 2010.
define('MYSQL_VERSION_MINI_REQUISE'   ,'5');
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
  exit_error( 'PHP incomplet' /*titre*/ , 'Module(s) PHP manquant(s) : '.implode($extensions_manquantes,' ; ').'.<br />Ce serveur n\'a pas la configuration minimale requise.' /*contenu*/ );
}

// Définir le décalage horaire par défaut de toutes les fonctions date/heure
// La fonction date_default_timezone_set() est disponible depuis PHP 5.1 ; on a été stoppé avant si ce n'est pas le cas.
date_default_timezone_set('Europe/Paris'); 

// Modifier l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
// Requiert le module "mbstring" ; on a été stoppé avant si ce dernier est manquant.
mb_internal_encoding(CHARSET);

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
define('CHEMIN_DOSSIER_PARTENARIAT'   , CHEMIN_DOSSIER_TMP.'partenariat'.DS);
define('CHEMIN_DOSSIER_RSS'           , CHEMIN_DOSSIER_TMP.'rss'.DS);
//      CHEMIN_FICHIER_CONFIG_MYSQL     est défini dans index.php ou ajax.php, en fonction du type d'installation et d'utilisateur connecté
define('FPDF_FONTPATH'                , CHEMIN_DOSSIER_FPDF_FONT); // Pour FPDF (répertoire où se situent les polices)

// Vers des fichiers.
define('CHEMIN_FICHIER_CONFIG_INSTALL' , CHEMIN_DOSSIER_CONFIG.'constantes.php');
define('CHEMIN_FICHIER_DEBUG_CONFIG'   , CHEMIN_DOSSIER_TMP.'debug.txt');
define('CHEMIN_FICHIER_WS_LCS'         , CHEMIN_DOSSIER_WEBSERVICES.'import_lcs.php');
define('CHEMIN_FICHIER_WS_ARGOS'       , CHEMIN_DOSSIER_WEBSERVICES.'argos_import.php');
define('CHEMIN_FICHIER_WS_SESAMATH_ENT', CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_hebergements_conventions.php');

// ============================================================================
// Constantes de DEBUG
// ============================================================================

/**
 *   1 => afficher toutes les erreurs PHP
 *   2 => enregistrer les dialogues avec le serveur CAS (dans le fichier de log phpCAS)
 *   4 => résultats des requêtes SQL (dans la console Firefox avec FirePHP)
 *   8 => valeurs de $_SESSION       (dans la console Firefox avec FirePHP)
 *  16 => valeurs de $_POST          (dans la console Firefox avec FirePHP)
 *  32 => valeurs de $_GET           (dans la console Firefox avec FirePHP)
 *  64 => valeurs de $_FILES         (dans la console Firefox avec FirePHP)
 * 128 => valeurs de $_COOKIE        (dans la console Firefox avec FirePHP)
 * 256 => valeurs de $_SERVER        (dans la console Firefox avec FirePHP)
 * 512 => valeurs des constantes PHP (dans la console Firefox avec FirePHP)
 *
 * On fonctionne comme pour les droits unix wrx, en testant chaque bit en partant de la droite.
 * Pour choisir les infos voulues, il faut mettre en DEBUG la somme des valeurs correspondantes.
 * Par exemple 7 = 4 + 2 + 1 = requêtes SQL + logs CAS + erreurs PHP
 * La valeur minimale est 0 (pas de debug) et la valeur maximale est 1023.
 */

// Dans un fichier texte du dossier TMP pour ne pas être dans la distribution ni écrasé par les maj et être accessible en écriture, en particulier par l'espace webmestre.
define('DEBUG', !is_file(CHEMIN_FICHIER_DEBUG_CONFIG) ? 0 : (int)file_get_contents(CHEMIN_FICHIER_DEBUG_CONFIG) );

// Les lignes suivantes ne doivent pas être modifiées : pour changer le niveau de debug modifier seulement le fichier alimentant la constante DEBUG
define('DEBUG_PHP',     DEBUG &   1 ? TRUE : FALSE );
define('DEBUG_PHPCAS',  DEBUG &   2 ? TRUE : FALSE );
define('DEBUG_SQL',     DEBUG &   4 ? TRUE : FALSE );
define('DEBUG_SESSION', DEBUG &   8 ? TRUE : FALSE );
define('DEBUG_POST',    DEBUG &  16 ? TRUE : FALSE );
define('DEBUG_GET',     DEBUG &  32 ? TRUE : FALSE );
define('DEBUG_FILES',   DEBUG &  64 ? TRUE : FALSE );
define('DEBUG_COOKIE',  DEBUG & 128 ? TRUE : FALSE );
define('DEBUG_SERVER',  DEBUG & 256 ? TRUE : FALSE );
define('DEBUG_CONST',   DEBUG & 512 ? TRUE : FALSE );

// ============================================================================
// DEBUG - Fixer le niveau de rapport d'erreurs PHP
// ============================================================================

if(DEBUG_PHP)
{
  // Rapporter toutes les erreurs PHP (http://fr.php.net/manual/fr/errorfunc.constants.php)
  ini_set('error_reporting',E_ALL | E_STRICT);
}
else
{
  // Rapporter les erreurs à part les E_NOTICE (c'est la configuration par défaut de php.ini) et E_STRICT qui est englobé dans E_ALL à compter de PHP 5.4.
  ini_set('error_reporting',E_ALL & ~E_STRICT & ~E_NOTICE);
}

// ============================================================================
// Si appel depuis /_img/php/etiquette.php alors on n'a pas besoin d'aller plus loin dans ce fichier inclus.
// ============================================================================

if(SACoche=='etiquette') return;

// ============================================================================
// DEBUG - Sorties FirePHP
// ============================================================================

if(DEBUG>3)
{
  ini_set('output_buffering','On');
  $firephp = FirePHP::getInstance(TRUE);
  function afficher_infos_debug_FirePHP()
  {
    global $firephp;
    if(DEBUG_SESSION) { $firephp->dump('SESSION', $_SESSION); }
    if(DEBUG_POST)    { $firephp->dump('POST'   , $_POST); }
    if(DEBUG_GET)     { $firephp->dump('GET'    , $_GET); }
    if(DEBUG_FILES)   { $firephp->dump('FILES'  , $_FILES); }
    if(DEBUG_COOKIE)  { $firephp->dump('COOKIE' , $_COOKIE); }
    if(DEBUG_SERVER)  { $firephp->dump('SERVER' , $_SERVER); }
    if(DEBUG_CONST)   {
      $tab_constantes = get_defined_constants(TRUE);
      $firephp->dump('CONSTANTES', $tab_constantes['user']);
    }
  }
}

// ============================================================================
// URL de base du serveur
// ============================================================================

// Code issu de la fonction _getServerUrl() provenant de phpCAS/CAS/Client.php
// Les variables HTTP_X_FORWARDED_* sont définies quand un serveur web (ou un proxy) qui récupère la main la passe à un serveur php (qui peut ou pas être un autre serveur web, mais en général pas accessible directement).
// Concernant HTTP_X_FORWARDED_HOST, il peut contenir plusieurs HOST successifs séparés par des virgules : on explose le tableau et on utilise la première valeur.
// Daniel privilégie HTTP_HOST (qui provient de la requete HTTP) à SERVER_NAME (qui vient de la conf du serveur) quand les 2 existent, mais phpCAS fait le contraire ; en général c'est pareil, sauf s'il y a des alias sans redirection (ex d'un site qui donne les mêmes pages avec et sans les www), dans ce cas le 1er est l'alias demandé et le 2nd le nom principal configuré du serveur (qui peut être avec ou sans les www, suivant la conf).
// Il arrive (très rarement) que HTTP_HOST ne soit pas défini (HTTP 1.1 impose au client web de préciser un nom de site, ce qui n'était pas le cas en HTTP 1.0 ; HTTP 1.1 date de 1999, avec un brouillon en 1996 ; et puis il y a aussi les appels en mode CLI).

function getServerUrl()
{
  if (!empty($_SERVER['HTTP_X_FORWARDED_HOST']))   { return current(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])); }
  if (!empty($_SERVER['HTTP_X_FORWARDED_SERVER'])) { return $_SERVER['HTTP_X_FORWARDED_SERVER']; }
  if (!empty($_SERVER['HTTP_HOST']))               { return $_SERVER['HTTP_HOST']; }
  if (!empty($_SERVER['SERVER_NAME']))             { return $_SERVER['SERVER_NAME']; }
  exit_error( 'HOST indéfini' /*titre*/ , 'SACoche n\'arrive pas à déterminer le nom du serveur hôte !<br />HTTP_HOST, SERVER_NAME, HTTP_X_FORWARDED_HOST et HTTP_X_FORWARDED_SERVER sont tous indéfinis.' /*contenu*/ );
}

$HOST = getServerUrl();

function getServerProtocole()
{
  // $_SERVER['HTTPS'] peut valoir 'on' ou 'off' ou ''
  return ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://' ;
}

function getServerPort()
{
  global $HOST;
  // Rien à indiquer si port 80 (protocole standard HTTP) ou 443 (protocole standard HTTPS) ou port déjà indiqué dans le HOST (les navigateurs indiquent le port dans le header Host de la requete http quand il est non standard comme la norme http1/1 le préconise http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.23 mais le serveur web ne le file généralement pas à PHP dans HTTP_HOST)
  return ( !isset($_SERVER['SERVER_PORT']) || in_array($_SERVER['SERVER_PORT'],array(80,443)) || strpos($HOST,':') ) ? '' : ':'.$_SERVER['SERVER_PORT'] ;
}

define('URL_BASE',getServerProtocole().$HOST.getServerPort());

// ============================================================================
// Type de serveur (LOCAL|DEV|PROD)
// ============================================================================

// On ne peut pas savoir avec certitude si un serveur est "local" car aucune méthode ne fonctionne à tous les coups :
// - $_SERVER['HTTP_HOST'] peut ne pas renvoyer localhost sur un serveur local (si configuration de domaines locaux via fichiers hosts / httpd.conf par exemple).
// - gethostbyname($_SERVER['HTTP_HOST']) peut renvoyer "127.0.0.1" sur un serveur non local car un serveur a en général 2 ip (une publique - ou privée s'il est sur un lan - et une locale).
// - $_SERVER['SERVER_ADDR'] peut renvoyer "127.0.0.1" avec nginx + apache sur 127.0.0.1 ...
$test_local = ( ($HOST=='localhost') || ($HOST=='127.0.0.1') || (mb_substr($HOST,-6)=='.local') ) ? TRUE : FALSE ;
$serveur_type = ($test_local) ? 'LOCAL' : ( ( (substr($HOST,-18)=='.sesamath.net:8080') || (substr($HOST,-18)=='.sesamath.net:8443') ) ? 'DEV' : 'PROD' ) ;
define('SERVEUR_TYPE',$serveur_type); // PROD | DEV | LOCAL

// ============================================================================
// URLs de l'application (les chemins restent relatifs pour les images ou les css/js...)
// ============================================================================

$url = URL_BASE.$_SERVER['SCRIPT_NAME'];
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
define('URL_DIR_TMP'         , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_TMP         ) );
define('URL_DIR_IMG'         , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_IMG         ) );
define('URL_DIR_DEVOIR'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_DEVOIR      ) );
define('URL_DIR_DUMP'        , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_DUMP        ) );
define('URL_DIR_EXPORT'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_EXPORT      ) );
define('URL_DIR_IMPORT'      , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_IMPORT      ) );
define('URL_DIR_LOGINPASS'   , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_LOGINPASS   ) );
define('URL_DIR_LOGO'        , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_LOGO        ) );
define('URL_DIR_PARTENARIAT' , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_PARTENARIAT ) );
define('URL_DIR_RSS'         , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_RSS         ) );
define('URL_DIR_WEBSERVICES' , str_replace( $tab_bad , $tab_bon , CHEMIN_DOSSIER_WEBSERVICES ) );

// ============================================================================
// URL externes appelées par l'application
// ============================================================================

define('SERVEUR_PROJET'         ,'https://sacoche.sesamath.net');            // URL du projet SACoche (en https depuis le 08/02/2012)
define('SERVEUR_SSL'            ,'https://sacoche.sesamath.net');            // URL du serveur Sésamath sécurisé (idem serveur projet SACoche depuis le 08/02/2012)
define('SERVEUR_ASSO'           ,'http://www.sesamath.net');                 // URL du serveur de l'association Sésamath
define('SERVEUR_COMMUNAUTAIRE'  ,SERVEUR_PROJET.'/appel_externe.php');       // URL du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
define('SERVEUR_DOCUMENTAIRE'   ,SERVEUR_PROJET.'/appel_doc.php');           // URL du fichier chargé d'afficher les documentations
define('SERVEUR_LPC_SIGNATURE'  ,SERVEUR_SSL   .'/appel_externe.php');       // URL du fichier chargé de signer un XML à importer dans LPC
define('SERVEUR_TELECHARGEMENT' ,SERVEUR_PROJET.'/telechargement.php');      // URL du fichier renvoyant le ZIP de la dernière archive de SACoche disponible
define('SERVEUR_VERSION'        ,SERVEUR_PROJET.'/sacoche/VERSION.txt');     // URL du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_CNIL'           ,SERVEUR_PROJET.'/?fichier=cnil');           // URL de la page "CNIL (données personnelles)"
define('SERVEUR_CONTACT'        ,SERVEUR_PROJET.'/?fichier=contact');        // URL de la page "Où échanger autour de SACoche ?"
define('SERVEUR_CARTE_ENT'      ,SERVEUR_PROJET.'/?fichier=ent');            // URL de la page "Avec quels ENT SACoche est-il interconnecté ?"
define('SERVEUR_GUIDE_ADMIN'    ,SERVEUR_PROJET.'/?fichier=guide_admin');    // URL de la page "Guide de démarrage (administrateur de SACoche)"
define('SERVEUR_GUIDE_RENTREE'  ,SERVEUR_PROJET.'/?fichier=guide_rentree');  // URL de la page "Guide de changement d'année (administrateur de SACoche)"
define('SERVEUR_NEWS'           ,SERVEUR_PROJET.'/?fichier=news');           // URL de la page "Historique des nouveautés"
define('SERVEUR_RSS'            ,SERVEUR_PROJET.'/_rss/rss.xml');            // URL du fichier comportant le flux RSS
define('SERVEUR_BLOG_CONVENTION',SERVEUR_ASSO.'/blog/index.php/aM4');        // URL de la page expliquant les Conventions ENT

// ============================================================================
// Autres constantes diverses... et parfois importantes !
// ============================================================================

// test si c'est l'hébergement Sésamath qui est utilisé
$is_hebergement_sesamath = (mb_strpos(URL_BASE,SERVEUR_PROJET)!==FALSE) ? TRUE : FALSE ;
define('IS_HEBERGEMENT_SESAMATH',$is_hebergement_sesamath);
// define('IS_HEBERGEMENT_SESAMATH',TRUE);

// indiquer si une convention Établissement-ENT est requise et à compter de quand
define('CONVENTION_ENT_REQUISE'         ,TRUE);
define('CONVENTION_ENT_START_DATE_FR'   ,'01/09/2013');
define('CONVENTION_ENT_START_DATE_MYSQL','2013-09-01');

// Identifiants particuliers (à ne pas modifier)
define('ID_DEMO'                   , 9999); // id de l'établissement de démonstration (pour $_SESSION['SESAMATH_ID']) ; 0 pose des pbs, et il fallait prendre un id disponible dans la base d'établissements de Sésamath
define('ID_MATIERE_PARTAGEE_MAX'   , 9999); // id de la matière transversale dans la table "sacoche_matiere" ; c'est l'id maximal des matières partagées (les id des matières spécifiques sont supérieurs)
define('ID_NIVEAU_MAX'             , 1000); // Un id de niveau supérieur correspond à un id de famille qui a été incrémenté de cette constante
define('ID_FAMILLE_MATIERE_USUELLE',   99);
define('CODE_BREVET_EPREUVE_TOTAL' ,  255);

// cookies
define('COOKIE_STRUCTURE' ,'SACoche-etablissement' ); // nom du cookie servant à retenir l'établissement sélectionné, afin de ne pas à avoir à le sélectionner de nouveau, et à pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_AUTHMODE'  ,'SACoche-mode-connexion'); // nom du cookie servant à retenir le dernier mode de connexion utilisé par un user connecté, afin de pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_PARTENAIRE','SACoche-partenaire'    ); // nom du cookie servant à retenir le partenaire sélectionné, afin de ne pas à avoir à le sélectionner de nouveau (convention ENT sur serveur Sésamath uniquement).
define('COOKIE_MEMOGET'   ,'SACoche-memoget'       ); // nom du cookie servant à retenir des paramètres multiples transmis en GET dans le cas où le service d'authentification externe en perd...

// session
define('SESSION_NOM','SACoche-session'); // Est aussi défini dans /_lib/SimpleSAMLphp/config/config.php

// Version des fichiers installés.
// À comparer avec la dernière version disponible sur le serveur communautaire.
// Pour une conversion en entier : list($annee,$mois,$jour) = explode('-',substr(VERSION_PROG,0,10)); $indice_version = (date('Y')-2011)*365 + date('z',mktime(0,0,0,$mois,$jour,$annee));
// Dans un fichier texte pour permettre un appel au serveur communautaire sans lui faire utiliser PHP.
define('VERSION_PROG', file_get_contents(CHEMIN_DOSSIER_SACOCHE.'VERSION.txt') );

// Version de la base associée d'un établissement, et du webmestre si multi-structures.
// À comparer avec la version de la base actuellement en place.
// Dans des fichiers texte pour faciliter la maintenance par les développeurs.
define('VERSION_BASE_STRUCTURE', file_get_contents(CHEMIN_DOSSIER_SQL.'VERSION_BASE_STRUCTURE.txt') ); 
define('VERSION_BASE_WEBMESTRE', file_get_contents(CHEMIN_DOSSIER_SQL.'VERSION_BASE_WEBMESTRE.txt') );

// dates
define('TODAY_FR'    ,date("d/m/Y"));
define('TODAY_MYSQL' ,date("Y-m-d"));
define('SORTIE_DEFAUT_FR'    ,'31/12/9999'); // inutilisé
define('SORTIE_DEFAUT_MYSQL' ,'9999-12-31');

// Dimension maxi d'une photo redimensionnée (en pixels) ; utilisé aussi dans le style.css : .photo {min-height:180px} (144+36)
// Prendre un nombre divisible par 3 et par 4, donc un multiple de 12.
// Avec 180 (12x15), au format 2/3 ça donne 120/180 et au format 3/4 ça donne 135/180 ; c'est un peu grand à l'écran.
// Avec 144 (12x12), au format 2/3 ça donne  96/144 et au format 3/4 ça donne 108/144 ; c'est un choix intermédiaire.
// Avec 120 (12x10), au format 2/3 ça donne  80/120 et au format 3/4 ça donne  90/120 ; c'est un peu petit à l'écran.
define('PHOTO_DIMENSION_MAXI',144);
// Le format jpeg est le plus adapté aux photos ; un facteur 90 Permet un gain de poids significatif (>50% par rapport à 100), pour une perte de qualité minime.
// Avec une dimension maxi imposée de 180 pixels, on arrive à 8~9 Ko par photo par élève dans la base (en comptant le base64_encode).
// Avec une dimension maxi imposée de 144 pixels, on arrive à 6~7 Ko par photo par élève dans la base (en comptant le base64_encode).
// Avec une dimension maxi imposée de 120 pixels, on arrive à 4~5 Ko par photo par élève dans la base (en comptant le base64_encode).
define('JPEG_QUALITY',90);

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
    'cURL'                        => '_inc'.DS.'class.cURL.php' ,
    'Erreur500'                   => '_inc'.DS.'class.Erreur500.php' ,
    'FileSystem'                  => '_inc'.DS.'class.FileSystem.php' ,
    'Form'                        => '_inc'.DS.'class.Form.php' ,
    'Html'                        => '_inc'.DS.'class.Html.php' ,
    'InfoServeur'                 => '_inc'.DS.'class.InfoServeur.php' ,
    'JSMin'                       => '_inc'.DS.'class.JavaScriptMinified.php' ,
    'JavaScriptPacker'            => '_inc'.DS.'class.JavaScriptPacker.php' ,
    'LockAcces'                   => '_inc'.DS.'class.LockAcces.php' ,
    'Mobile_Detect'               => '_inc'.DS.'class.MobileDetect.php' ,
    'MyDOMDocument'               => '_inc'.DS.'class.domdocument.php' ,
    'PDF'                         => '_inc'.DS.'class.PDF.php' ,
    'RSS'                         => '_inc'.DS.'class.RSS.php' ,
    'SACocheLog'                  => '_inc'.DS.'class.SACocheLog.php' ,
    'ServeurCommunautaire'        => '_inc'.DS.'class.ServeurCommunautaire.php' ,
    'Sesamail'                    => '_inc'.DS.'class.Sesamail.php' ,
    'Session'                     => '_inc'.DS.'class.Session.php' ,
    'SessionUser'                 => '_inc'.DS.'class.SessionUser.php' ,
    'To'                          => '_inc'.DS.'class.To.php' ,
    'Webmestre'                   => '_inc'.DS.'class.Webmestre.php' ,

    'DB_STRUCTURE_ADMINISTRATEUR' => '_sql'.DS.'requetes_structure_administrateur.php' ,
    'DB_STRUCTURE_DIRECTEUR'      => '_sql'.DS.'requetes_structure_directeur.php' ,
    'DB_STRUCTURE_ELEVE'          => '_sql'.DS.'requetes_structure_eleve.php' ,
    'DB_STRUCTURE_PROFESSEUR'     => '_sql'.DS.'requetes_structure_professeur.php' ,
    'DB_STRUCTURE_PUBLIC'         => '_sql'.DS.'requetes_structure_public.php' ,
    'DB_STRUCTURE_WEBMESTRE'      => '_sql'.DS.'requetes_structure_webmestre.php' ,

    'DB_STRUCTURE_BILAN'          => '_sql'.DS.'requetes_structure_bilan.php' ,
    'DB_STRUCTURE_BREVET'         => '_sql'.DS.'requetes_structure_brevet.php' ,
    'DB_STRUCTURE_COMMUN'         => '_sql'.DS.'requetes_structure_commun.php' ,
    'DB_STRUCTURE_IMAGE'          => '_sql'.DS.'requetes_structure_image.php' ,
    'DB_STRUCTURE_MAJ_BASE'       => '_sql'.DS.'requetes_structure_maj_base.php' ,
    'DB_STRUCTURE_OFFICIEL'       => '_sql'.DS.'requetes_structure_officiel.php' ,
    'DB_STRUCTURE_REFERENTIEL'    => '_sql'.DS.'requetes_structure_referentiel.php' ,
    'DB_STRUCTURE_SOCLE'          => '_sql'.DS.'requetes_structure_socle.php' ,

    'DB_WEBMESTRE_ADMINISTRATEUR' => '_sql'.DS.'requetes_webmestre_administrateur.php' ,
    'DB_WEBMESTRE_MAJ_BASE'       => '_sql'.DS.'requetes_webmestre_maj_base.php' ,
    'DB_WEBMESTRE_PARTENAIRE'     => '_sql'.DS.'requetes_webmestre_partenaire.php' ,
    'DB_WEBMESTRE_PUBLIC'         => '_sql'.DS.'requetes_webmestre_public.php' ,
    'DB_WEBMESTRE_SELECT'         => '_sql'.DS.'requetes_webmestre_select.php' ,
    'DB_WEBMESTRE_WEBMESTRE'      => '_sql'.DS.'requetes_webmestre_webmestre.php' ,
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
// Fonctions d'affichage et de sortie
// ============================================================================

/**
 * Fonction pour remplacer mb_detect_encoding() à cause d'un bug : http://fr2.php.net/manual/en/function.mb-detect-encoding.php#81936
 *
 * @param string
 * @return bool
 */
function perso_mb_detect_encoding_utf8($text)
{
  return (mb_detect_encoding($text.' ','auto',TRUE)=='UTF-8');
}

/*
 * Convertir les caractères spéciaux (&"'<>) en entité HTML afin d'éviter des problèmes d'affichage (INPUT, SELECT, TEXTAREA, XML, ou simple texte HTML valide...).
 * Pour que les retours à la ligne soient convertis en <br /> il faut coupler cette fontion à la fonction nl2br()
 * 
 * @param string
 * @return string
 */
function html($text)
{
  // Ne pas modifier ce code à la légère : les résultats sont différents suivant que ce soit un affichage direct ou ajax, suivant la version de PHP (5.1 ou 5.3)...
  return (perso_mb_detect_encoding_utf8($text)) ? htmlspecialchars($text,ENT_COMPAT,'UTF-8') : utf8_encode(htmlspecialchars($text,ENT_COMPAT)) ;
}

/*
 * Afficher une page HTML minimale (mais aux couleurs de SACoche) avec un message explicatif et un lien adapté éventuel.
 * 
 * @param string $titre     titre de la page
 * @param string $contenu   contenu HTML affiché (ou AJAX retourné) ; il doit déjà avoir été filtré si besoin avec html()
 * @param string $lien      "accueil" pour un lien vers l'accueil (par défaut) OU "install" vers la procédure d'installation OU "" pour aucun
 * @return void
 */
function exit_error( $titre , $contenu , $lien='accueil' )
{
  if(SACoche!='ajax')
  {
    header('Content-Type: text/html; charset='.CHARSET);
    echo'<!DOCTYPE html>'.NL;
    echo'<html>'.NL;
    echo  '<head>'.NL;
    echo    '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'.NL;
    echo    '<link rel="stylesheet" type="text/css" href="./_css/style.css" />'.NL;
    echo    '<style type="text/css">#cadre_milieu{color:#D00}</style>'.NL;
    echo    '<title>SACoche » '.$titre.'</title>'.NL;
    echo  '</head>'.NL;
    echo  '<body>'.NL;
    echo    '<div id="cadre_milieu">'.NL;
    echo      '<div class="hc"><img src="./_img/logo_grand.gif" alt="SACoche" width="208" height="71" /></div>'.NL;
    echo      '<h1>'.$titre.'</h1>'.NL;
    echo      '<p>'.str_replace('<br />','</p><p>',$contenu).'</p>'.NL;
        if($lien=='accueil') { echo'<p><a href="./index.php">Retour en page d\'accueil de SACoche.</a></p>'.NL; } 
    elseif($lien=='install') { echo'<p><a href="./index.php?page=public_installation">Procédure d\'installation de SACoche.</a></p>'.NL; } 
    echo    '</div>'.NL;
    echo  '</body>'.NL;
    echo'</html>'.NL;
  }
  else
  {
    echo str_replace('<br />',' ',$contenu);
  }
  exit();
}

/*
 * Rediriger le navigateur.
 * 
 * @param string $adresse   URL de la page vers laquelle rediriger
 * @return void
 */
function exit_redirection($adresse)
{
  // Qqs header ajoutés par précaution car même si la redirection est indiquée comme étant temporaire, il semblent que certains navigateurs buguent en la mettant en cache.
  header('Pragma: no-cache');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); // IE n'aime pas "no-store" ni "no-cache".
  header('Expires: 0');
  // Cette fois-ci on y va
  header('Status: 307 Temporary Redirect', TRUE, 307);
  header('Location: '.$adresse);
  exit();
}

/*
 * Pour retenir les données qui seront à afficher dans la section <head></head> de la page.
 */
$GLOBALS['HEAD'] = array();
?>