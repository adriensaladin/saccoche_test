<?php
/**
 * @version $Id:$
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * PHP CSS Browser Selector v0.0.1
 * @author Bastian Allgeier (http://bastian-allgeier.de)
 * http://bastian-allgeier.de/css_browser_selector
 * License: http://creativecommons.org/licenses/by/2.5/
 * Credits: This is a php port from Rafael Lima's original Javascript CSS Browser Selector: http://rafael.adm.br/css_browser_selector
 * ****************************************************************************************************
 * 
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
	elseif(strstr($UserAgent,'firefox/0'))
	{
		$tab_retour[] = $gecko.' ff0';
	}
	elseif(strstr($UserAgent,'firefox/1'))
	{
		$tab_retour[] = $gecko.' ff1';
	}
	elseif(strstr($UserAgent,'firefox/2'))
	{
		$tab_retour[] = $gecko.' ff2';
	}
	elseif(strstr($UserAgent,'firefox/3.5'))
	{
		$tab_retour[] = $gecko.' ff3 ff3_5';
	}
	elseif(strstr($UserAgent,'firefox/3'))
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

?>