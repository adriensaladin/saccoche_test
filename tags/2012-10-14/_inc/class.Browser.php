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

// Versions des navigateurs.

define(  'CHROME_VERSION_MINI_REQUISE'   , 1); define(  'CHROME_TEXTE_MINI_REQUIS'     , 'Version 1 minimum (sortie en 2008).');
define(  'CHROME_VERSION_MINI_CONSEILLEE', 5);
define(  'CHROME_VERSION_LAST'           ,21); define(  'CHROME_URL_DOWNLOAD'          , 'http://www.google.fr/chrome');

define( 'FIREFOX_VERSION_MINI_REQUISE'   , 3); define( 'FIREFOX_TEXTE_MINI_REQUIS'     , 'Version 3 minimum (sortie en 2008).');
define( 'FIREFOX_VERSION_MINI_CONSEILLEE', 4);
define( 'FIREFOX_VERSION_LAST'           ,15); define( 'FIREFOX_URL_DOWNLOAD'          , 'http://www.mozilla-europe.org/fr/');

define(   'OPERA_VERSION_MINI_REQUISE'   , 9); define(   'OPERA_TEXTE_MINI_REQUIS'     , 'Version 9 minimum (sortie en 2006).');
define(   'OPERA_VERSION_MINI_CONSEILLEE',11);
define(   'OPERA_VERSION_LAST'           ,12); define(   'OPERA_URL_DOWNLOAD'          , 'http://www.opera-fr.com/telechargements/');

define(  'SAFARI_VERSION_MINI_REQUISE'   , 3); define(  'SAFARI_TEXTE_MINI_REQUIS'     , 'Version 3 minimum (sortie en 2007).');
define(  'SAFARI_VERSION_MINI_CONSEILLEE', 5);
define(  'SAFARI_VERSION_LAST'           , 5); define(  'SAFARI_URL_DOWNLOAD'          , 'http://www.apple.com/fr/safari/'); // plus téléchargeable ?

define('EXPLORER_VERSION_MINI_REQUISE'   , 8); define('EXPLORER_TEXTE_MINI_REQUIS'     , 'Version 8 minimum (sortie en 2009) <span class="danger">mais usage déconseillé</span> (surtout avant la version 9).');
define('EXPLORER_VERSION_MINI_CONSEILLEE', 9);
define('EXPLORER_VERSION_LAST'           , 9); define('EXPLORER_URL_DOWNLOAD'          , 'http://windows.microsoft.com/fr-FR/internet-explorer/products/ie/home');



class Browser
{

  public static $tab_navigo = array( 'chrome' , 'firefox' , 'opera' , 'safari' , 'explorer' );

  // //////////////////////////////////////////////////
  // Méthode privée (interne)
  // //////////////////////////////////////////////////

  /**
   * PHP CSS Browser Selector v0.0.1
   * @author Bastian Allgeier (http://bastian-allgeier.de)
   * http://bastian-allgeier.de/css_browser_selector
   * License: http://creativecommons.org/licenses/by/2.5/
   * Credits: This is a php port from Rafael Lima's original Javascript CSS Browser Selector: http://rafael.adm.br/css_browser_selector
   * 
   * Fonction originale réécrite et modifiée pour SACoche par Thomas Crespin.
   * @param string   $UserAgent   facultatif
   * @return array                array( 'nav_modele' , 'nav_version' );
   */
  private static function css_selector($UserAgent=NULL)
  {
    $tab_retour = array( 'nav_modele'=>'' , 'nav_version'=>0 , 'environnement'=>'' );
    // Variable à analyser
    $UserAgent = ($UserAgent) ? strtolower($UserAgent) : ( isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '' ) ;
    // Détection du navigateur et si possible de sa version
    if( (!preg_match('#opera|webtv#', $UserAgent)) && (strstr($UserAgent,'msie')) )
    {
      $tab_retour['nav_modele']  = 'explorer';
      $tab_retour['nav_version'] = (preg_match('#msie\s([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'firefox'))
    {
      $tab_retour['nav_modele']  = 'firefox';
      $tab_retour['nav_version'] = (preg_match('#firefox/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'iceweasel'))
    {
      $tab_retour['nav_modele']  = 'firefox';
      $tab_retour['nav_version'] = (preg_match('#iceweasel/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'icecat'))
    {
      $tab_retour['nav_modele']  = 'firefox';
      $tab_retour['nav_version'] = (preg_match('#icecat/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'gecko/'))
    {
      $tab_retour['nav_modele']  = 'gecko';
    }
    elseif(strstr($UserAgent,'opera'))
    {
      $tab_retour['nav_modele']  = 'opera';
      $tab_retour['nav_version'] = (preg_match('#opera(\s|\/)([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[2] : 0 ;
      $tab_retour['nav_version'] = (preg_match('#version/([0-9]+\.?[0-9]*)#', $UserAgent, $array)) ? $array[1] : $tab_retour['nav_version'] ;
    }
    elseif(strstr($UserAgent,'konqueror'))
    {
      $tab_retour['nav_modele']  = 'konqueror';
    }
    elseif(strstr($UserAgent,'chrome'))
    {
      $tab_retour['nav_modele']  = 'chrome';
      $tab_retour['nav_version'] = (preg_match('#chrome/([0-9]+\.?[0-9]*)#',$UserAgent,$array)) ? (int)$array[1] : 0 ;

    }
    elseif(strstr($UserAgent,'iron'))
    {
      $tab_retour['nav_modele']  = 'chrome';
    }
    elseif(strstr($UserAgent,'applewebkit/'))
    {
      $tab_retour['nav_modele']  = 'safari';
      $tab_retour['nav_version'] = (preg_match('#version\/([0-9]+\.?[0-9]*)#', $UserAgent, $array)) ? (int)$array[1] : 0 ;
    }
    elseif(strstr($UserAgent,'mozilla'))
    {
      $tab_retour['nav_modele']  = 'gecko';
    }
    // Détection de l'environnement
    /*
    if(strstr($UserAgent,'j2me'))
    {
      $tab_retour['environnement'] = 'mobile';
    }
    elseif(strstr($UserAgent,'iphone'))
    {
      $tab_retour['environnement'] = 'iphone';
    }
    elseif(strstr($UserAgent,'ipod'))
    {
      $tab_retour['environnement'] = 'ipod';
    }
    elseif(strstr($UserAgent,'mac'))
    {
      $tab_retour['environnement'] = 'mac';
    }
    elseif(strstr($UserAgent,'darwin'))
    {
      $tab_retour['environnement'] = 'mac';
    }
    elseif(strstr($UserAgent,'webtv'))
    {
      $tab_retour['environnement'] = 'webtv';
    }
    elseif(strstr($UserAgent,'win'))
    {
      $tab_retour['environnement'] = 'win';
    }
    elseif(strstr($UserAgent,'freebsd'))
    {
      $tab_retour['environnement'] = 'freebsd';
    }
    elseif( (strstr($UserAgent, 'x11')) || (strstr($UserAgent, 'linux')) )
    {
      $tab_retour['environnement'] = 'linux';
    }
    */
    // Envoi du résultat
    return $tab_retour;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  //
  // Chrome
  //
  // $version_page = url_get_contents('http://googlechromereleases.blogspot.com/search/label/Beta%20updates');
  // $nb_match = preg_match( '#'.'Google Chrome '.'(.*?)'.' has been released'.'#' , $version_page , $tab_matches );
  // $version_numero = ($nb_match) ? (float)$tab_matches[1] : 6 ;
  //
  // Firefox
  //
  // $version_page = url_get_contents('http://www.mozilla-europe.org/fr/');
  // $nb_match = preg_match( '#'.'product=firefox-'.'(.*?)'.'&amp;os=win'.'#' , $version_page , $tab_matches );
  // $version_numero = ($nb_match) ? (float)$tab_matches[1] : 4 ;
  //
  // Opéra
  //
  // $version_page = url_get_contents('http://www.opera-fr.com/telechargements/');
  // $nb_match = preg_match( '#'.'<h2>Version finale actuelle : '.'(.*?)'.'</h2>'.'#' , $version_page , $tab_matches );
  // $version_numero = ($nb_match) ? (float)$tab_matches[1] : 10 ;
  //
  // Safari
  //
  // $version_page = url_get_contents('http://swdlp.apple.com/cgi-bin/WebObjects/SoftwareDownloadApp.woa/wa/getProductData?localang=fr_fr&grp_code=safari');
  // $nb_match = preg_match( '#'.'<LABEL CLASS=platform>Safari '.'(.*?)'.' pour '.'#' , $version_page , $tab_matches );
  //
  // $version_page = url_get_contents('http://www.commentcamarche.net/download/telecharger-34055514-safari');
  // $nb_match = preg_match( '#'.'<td>'.'(.*?)'.' \(derni&egrave;re version\)</td>'.'#' , $version_page , $tab_matches );
  // $version_numero = ($nb_match) ? (float)$tab_matches[1] : 5 ;
  //

  public static function afficher_navigateurs_modernes()
  {
    $tab_chaine = array();
    foreach(Browser::$tab_navigo as $navigo)
    {
      $tab_chaine[$navigo] = '<a class="lien_ext" href="'.constant(strtoupper($navigo).'_URL_DOWNLOAD').'"><img src="'.URL_DIR_IMG.'navigateur/'.$navigo.'18.gif" alt="'.ucfirst($navigo).'" /> '.ucfirst($navigo).' '.constant(strtoupper($navigo).'_VERSION_LAST').'</a>';
    }
    // Affichage
    return $tab_chaine;
  }

  public static function afficher_navigateurs_alertes()
  {
    $tab_infos = Browser::css_selector(); // array( 'nav_modele' , 'nav_version' );
    $alertes = '';
    $alerte_ancien = '<div class="astuce">Votre navigateur est dépassé ! Utilisez une version récente pour une navigation plus sure, rapide et efficace.</div>';
    if($tab_infos['nav_modele']=='explorer')
    {
      if($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_REQUISE'))        $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Internet Explorer est utilisable à partir de sa version 8.</div>';
      elseif($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_CONSEILLEE')) $alertes .= '<div class="danger">Votre navigateur dysfonctionne ! L\'usage d\'Internet Explorer est déconseillé avant sa version 9.</div>';
    }
    elseif($tab_infos['nav_modele']=='firefox')
    {
      if($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_REQUISE'))        $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Firefox est utilisable à partir de sa version 3.</div>';
      elseif($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_CONSEILLEE')) $alertes .= $alerte_ancien;
    }
    elseif($tab_infos['nav_modele']=='opera')
    {
      if($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_REQUISE'))        $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Opéra est utilisable à partir de sa version 9.</div>';
      elseif($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_CONSEILLEE')) $alertes .= $alerte_ancien;
    }
    elseif($tab_infos['nav_modele']=='safari')
    {
      if($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_REQUISE'))        $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Safari est utilisable à partir de sa version 3.</div>';
      elseif($tab_infos['nav_version']<constant(strtoupper($tab_infos['nav_modele']).'_VERSION_MINI_CONSEILLEE')) $alertes .= $alerte_ancien;
    }
    return ($alertes) ? '<hr />'.$alertes.'<div class="astuce">Installez '.implode(' ou ',Browser::afficher_navigateurs_modernes()).'.</div>' : '' ;
  }

}
?>