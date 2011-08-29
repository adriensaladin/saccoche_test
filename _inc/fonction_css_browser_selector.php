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

/**
 * PHP CSS Browser Selector v0.0.1
 * @author Bastian Allgeier (http://bastian-allgeier.de)
 * http://bastian-allgeier.de/css_browser_selector
 * License: http://creativecommons.org/licenses/by/2.5/
 * Credits: This is a php port from Rafael Lima's original Javascript CSS Browser Selector: http://rafael.adm.br/css_browser_selector
 * 
 * Fonction originale réécrite et modifiée pour SACoche par Thomas Crespin.
 */

function css_browser_selector($UserAgent=null)
{
	$tab_retour = array( 'nav_modele'=>'' , 'nav_version'=>0 , 'environnement'=>'' );
	// Variable à analyser
	$UserAgent = ($UserAgent) ? strtolower($UserAgent) : strtolower($_SERVER['HTTP_USER_AGENT']) ;
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

function afficher_navigateurs_modernes($chemin_image)
{
	$tab_navigo = array(
		'chrome'   => array( 13 , 'http://www.google.fr/chrome') ,
		'firefox'  => array(  6 , 'http://www.mozilla-europe.org/fr/') ,
		'opera'    => array( 11 , 'http://www.opera-fr.com/telechargements/') ,
		'safari'   => array(  5 , 'http://www.apple.com/fr/safari/download/') ,
		'explorer' => array(  9 , 'http://windows.microsoft.com/fr-FR/internet-explorer/products/ie/home')
	);
	$tab_chaine = array();
	foreach($tab_navigo as $key => $tab_infos)
	{
		$tab_chaine[$key] = '<a class="lien_ext" href="'.$tab_infos[1].'"><img src="'.$chemin_image.'/navigateur/'.$key.'18.gif" alt="'.ucfirst($key).'" /> '.ucfirst($key).' '.$tab_infos[0].'</a>';
	}
	// Affichage
	return $tab_chaine;
}

function afficher_navigateurs_alertes($hr_avant='',$chemin_image='./_img',$hr_apres='')
{
	$tab_infos = css_browser_selector(); // array( 'nav_modele' , 'nav_version' );
	$alertes = '';
	$alerte_ancien = '<div class="astuce">Votre navigateur est dépassé ! Utilisez une version récente pour une navigation plus sure, rapide et efficace.</div>';
	if($tab_infos['nav_modele']=='explorer')
	{
		if($tab_infos['nav_version']<7)      $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Internet Explorer est utilisable à partir de sa version 7.</div>';
		elseif($tab_infos['nav_version']<9)  $alertes .= '<div class="danger">Votre navigateur dysfonctionne ! Internet Explorer est particulièrement déconseillé avant sa version 9.</div>';
	}
	elseif($tab_infos['nav_modele']=='firefox')
	{
		if($tab_infos['nav_version']<3)      $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Firefox est utilisable à partir de sa version 3.</div>';
		elseif($tab_infos['nav_version']<4)  $alertes .= $alerte_ancien;
	}
	elseif($tab_infos['nav_modele']=='opera')
	{
		if($tab_infos['nav_version']<9)      $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Opéra est utilisable à partir de sa version 9.</div>';
		elseif($tab_infos['nav_version']<11) $alertes .= $alerte_ancien;
	}
	elseif($tab_infos['nav_modele']=='safari')
	{
		if($tab_infos['nav_version']<3)      $alertes .= '<div class="danger">Votre navigateur est trop ancien pour utiliser <em>SACoche</em> ! Safari est utilisable à partir de sa version 3.</div>';
		elseif($tab_infos['nav_version']<5)  $alertes .= $alerte_ancien;
	}
	return ($alertes) ? $hr_avant.$alertes.'<div class="astuce">Installez '.implode(' ou ',afficher_navigateurs_modernes($chemin_image)).'.</div>'.$hr_apres : '' ;
}
?>