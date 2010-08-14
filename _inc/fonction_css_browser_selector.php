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
	// Variable à analyser
	$UserAgent = ($UserAgent) ? strtolower($UserAgent) : strtolower($_SERVER['HTTP_USER_AGENT']) ;
	// Initialisation de variables
	$gecko  = 'gecko';
	$webkit = 'webkit';
	$safari = 'safari';
	$tab_retour = array();
	// Détection du navigateur
	if( (!preg_match('/opera|webtv/i', $UserAgent)) && (preg_match('/msie\s(\d)/',$UserAgent,$array)) )
	{
		$tab_retour[] = 'ie ie'.$array[1];
	}
	elseif( strstr($UserAgent, 'firefox/0') || strstr($UserAgent, 'iceweasel/0') || strstr($UserAgent, 'icecat/0') )
	{
		$tab_retour[] = $gecko.' ff0';
	}
	elseif( strstr($UserAgent, 'firefox/1') || strstr($UserAgent, 'iceweasel/1') || strstr($UserAgent, 'icecat/1') )
	{
		$tab_retour[] = $gecko.' ff1';
	}
	elseif( strstr($UserAgent, 'firefox/2') || strstr($UserAgent, 'iceweasel/2') || strstr($UserAgent, 'icecat/2') )
	{
		$tab_retour[] = $gecko.' ff2';
	}
	elseif(strstr($UserAgent,'firefox/3.5'))
	{
		$tab_retour[] = $gecko.' ff3 ff3_5';
	}
	elseif( strstr($UserAgent, 'firefox/3') || strstr($UserAgent, 'iceweasel/3') || strstr($UserAgent, 'icecat/3') )
	{
		$tab_retour[] = $gecko.' ff3';
	}
	elseif(strstr($UserAgent,'gecko/'))
	{
		$tab_retour[] = $gecko;
	}
	elseif(preg_match('/opera(\s|\/)(\d+)/',$UserAgent,$array))
	{
		$tab_retour[] = 'opera opera'.$array[2];
	}
	elseif(strstr($UserAgent,'konqueror'))
	{
		$tab_retour[] = 'konqueror';
	}
	elseif(strstr($UserAgent,'chrome'))
	{
		$tab_retour[] = $webkit.' '.$safari.' chrome';
	}
	elseif(strstr($UserAgent,'iron'))
	{
		$tab_retour[] = $webkit.' '.$safari.' iron';
	}
	elseif(strstr($UserAgent,'applewebkit/'))
	{
		$tab_retour[] = (preg_match('/version\/(\d+)/i', $UserAgent, $array)) ? $webkit.' '.$safari.' '.$safari.$array[1] : $webkit.' '.$safari;
	}
	elseif(strstr($UserAgent,'mozilla/'))
	{
		$tab_retour[] = $gecko;
	}
	// Détection de l'environnement
	if(strstr($UserAgent,'j2me'))
	{
		$tab_retour[] = 'mobile';
	}
	elseif(strstr($UserAgent,'iphone'))
	{
		$tab_retour[] = 'iphone';
	}
	elseif(strstr($UserAgent,'ipod'))
	{
		$tab_retour[] = 'ipod';
	}
	elseif(strstr($UserAgent,'mac'))
	{
		$tab_retour[] = 'mac';
	}
	elseif(strstr($UserAgent,'darwin'))
	{
		$tab_retour[] = 'mac';
	}
	elseif(strstr($UserAgent,'webtv'))
	{
		$tab_retour[] = 'webtv';
	}
	elseif(strstr($UserAgent,'win'))
	{
		$tab_retour[] = 'win';
	}
	elseif(strstr($UserAgent,'freebsd'))
	{
		$tab_retour[] = 'freebsd';
	}
	elseif( (strstr($UserAgent, 'x11')) || (strstr($UserAgent, 'linux')) )
	{
		$tab_retour[] = 'linux';
	}
	// Envoi du résultat
	return join(' ', $tab_retour);
}

function afficher_navigateurs_modernes($chemin_image)
{
	$tab_navigateurs = array();
	$tab_navigateurs[] = '<a class="lien_ext" href="http://www.google.fr/chrome"><img src="'.$chemin_image.'/navigateur/chrome18.gif" alt="Chrome" /> Chrome 5</a>';
	$tab_navigateurs[] = '<a class="lien_ext" href="http://www.mozilla-europe.org/fr/"><img src="'.$chemin_image.'/navigateur/firefox18.gif" alt="Firefox" /> Firefox 3</a>';
	$tab_navigateurs[] = '<a class="lien_ext" href="http://www.opera-fr.com/telechargements/"><img src="'.$chemin_image.'/navigateur/opera18.gif" alt="Opéra" /> Opéra 10</a>';
	$tab_navigateurs[] = '<a class="lien_ext" href="http://www.apple.com/fr/safari/"><img src="'.$chemin_image.'/navigateur/safari18.gif" alt="Safari" /> Safari 4</a>';
	return implode(' ou ',$tab_navigateurs);
}

function afficher_navigateurs_alertes($hr_avant='',$chemin_image='./_img',$hr_apres='')
{
	$chaine_detection = css_browser_selector();
	$alertes = '';
	if( strpos($chaine_detection,'ie')!==false )
	{
		$alertes .= '<div class="danger">Votre navigateur <img src="'.$chemin_image.'/navigateur/explorer18.gif" alt="Explorer" /> Internet Explorer est irrespectueux des normes et dysfonctionne.</div>';
	}
	if( strpos($chaine_detection,'ie4') || strpos($chaine_detection,'ie5') || strpos($chaine_detection,'ie6') || strpos($chaine_detection,'ff0') || strpos($chaine_detection,'ff1') || strpos($chaine_detection,'ff2') || strpos($chaine_detection,'opera8') )
	{
		$alertes .= '<div class="danger">Votre navigateur est trop ancien pour permettre d\'utiliser <em>SACoche</em> correctement !</div>';
	}
	return ($alertes) ? $hr_avant.$alertes.'<div class="astuce">Utilisez '.afficher_navigateurs_modernes($chemin_image).'.</div>'.$hr_apres : '' ;
}
?>