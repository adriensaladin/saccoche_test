<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
 * Fonctions de nettoyage des chaînes avant stockage ou affichage.
 * 
 * Les conseils à suivre que l'on donne génréralement sont les suivants :
 * + Desactiver magic_quotes_gpc() pour ne pas avoir à jouer conditionnellement avec stripslashes() et addslashes()
 * + Avant stockage dans la BDD utiliser mysql_real_escape_string() et intval()
 * + Avant affichage utiliser htmlspecialchars() couplé à nl2br() si on veut les sauts de ligne (hors textarea)
 * Ici c'est inutile, les fonctions mises en place et la classe PDO s'occupent de tout.
 * 
 */

// Obsolète depuis PHP 5.3.0, supprimé depuis PHP 6.0.0.
function anti_magic_quotes_gpc()
{
	if(get_magic_quotes_gpc())
	{
		$_POST = array_map('stripslashes',$_POST);
		$_GET = array_map('stripslashes',$_GET);
		$_COOKIE = array_map('stripslashes',$_COOKIE);
	}
}
anti_magic_quotes_gpc();

/*
	Quelques "sous-fonctions" utilisées
	Attention ! strtr() renvoie n'importe quoi en UTF-8 car il fonctionne octet par octet et non caractère par caractère, or l'UTF-8 est multi-octets...
*/

define( 'LATIN1_LC_CHARS' , utf8_decode('abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïñòóôõöœøŕšùúûüýÿžðþ') );
define( 'LATIN1_UC_CHARS' , utf8_decode('ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖŒØŔŠÙÚÛÜÝŸŽÐÞ') );
define( 'LATIN1_YES_ACCENT' , utf8_decode('ÀÁÂÃÄÅàáâãäåÞþÇçÐðÈÉÊËèéêëÌÍÎÏìíîïÑñÒÓÔÕÖØòóôõöøŔŕŠšßÙÚÛÜùúûüÝŸýÿŽž') );
define( 'LATIN1_NOT_ACCENT' , utf8_decode('AAAAAAaaaaaaBbCcDdEEEEeeeeIIIIiiiiNnOOOOOOooooooRrSssUUUUuuuuYYyyZz') );

// Fonction pour remplacer mb_detect_encoding() à cause d'un bug => http://fr2.php.net/manual/en/function.mb-detect-encoding.php#81936
function perso_mb_detect_encoding_utf8($text)
{
	return (mb_detect_encoding($text.' ',"auto",TRUE)=='UTF-8');
}

// Equivalent de "strtoupper()" pour mettre en majuscules y compris les caractères accentués
function perso_strtoupper($text)
{
	return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_UPPER,'UTF-8') : strtr($text,LATIN1_LC_CHARS,LATIN1_UC_CHARS) ;
}

// Equivalent de "strtolower()" pour mettre en minuscules y compris les caractères accentués
function perso_strtolower($text)
{
	return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_LOWER,'UTF-8') : strtr($text,LATIN1_UC_CHARS,LATIN1_LC_CHARS) ;
}

// Enlever les accents
function clean_accents($text)
{
	return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode(strtr(utf8_decode($text),LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT)) : strtr($text,LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT) ;
}

// Enlever les caractères diacritiques
function clean_diacris($text)
{
	$bad = array('Ç','ç','Æ' ,'æ' ,'Œ' ,'œ' );
	$bon = array('C','c','AE','ae','OE','oe');
	return str_replace($bad,$bon,$text);
}

// Equivalent de "ucwords()" adaptée aux caractères accentués et aux expressions séparées par autre chose qu'une espace (virgule, point, tiret, parenthèse...)
function perso_ucwords($text)
{
	return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_TITLE,'UTF-8') : trim(preg_replace('/([^a-z'.LATIN1_LC_CHARS.']|^)([a-z'.LATIN1_LC_CHARS.'])/e', 'stripslashes("$1".perso_strtoupper("$2"))', perso_strtolower($text)));
}

// Enlever les guillemets éventuels entourants des champs dans un fichier csv (fonction utilisée avec "array_map()")
function clean_csv($text)
{
	if(mb_strlen($text)>1)
	{
		$tab_guillemets = array('"','\'');
		$premier = mb_substr($text,0,1);
		$dernier = mb_substr($text,-1);
		if( ($premier==$dernier) && (in_array($premier,$tab_guillemets)) )
		{
			$text = mb_substr($text,1,-1);
		}
	}
	return $text;
}


function clean_symboles($text)
{
	$bad = array('&','<','>','\\','"','\'','`','’');
	$bon = '';
	return str_replace($bad,$bon,$text);
}

/*
	Les fonctions centrales à modifier sans avoir à modifier tous les scripts.
	En général il s'agit d'harmoniser les données de la base ou d'aider l'utilisateur (en évitant les problèmes de casse par exemple).
	Le login est davantage nettoyé car il y a un risque d'engendrer des comportements incertains (à l'affichage ou à l'enregistrement) avec les applications externes (pmwiki, phpbb...).
*/
function clean_login($text)    { return str_replace(' ','', perso_strtolower( clean_accents( clean_diacris( clean_symboles( trim($text) ) ) ) ) ); }
function clean_password($text) { return trim($text); }
function clean_ref($text)      { return perso_strtoupper( trim($text) ); }
function clean_nom($text)      { return perso_strtoupper( trim($text) ); }
function clean_uai($text)      { return perso_strtoupper( trim($text) ); }
function clean_prenom($text)   { return perso_ucwords( trim($text) ); }
function clean_texte($text)    { return trim($text); }
function clean_courriel($text) { return perso_strtolower( trim($text) ); }
function clean_url($text)      { return perso_strtolower( trim($text) ); }
function clean_entier($text)   { return intval($text); }
function clean_decimal($text)  { return floatval($text); }

/*
	Convertit les caractères spéciaux (&"'<>) en entité HTML pour éviter des problèmes d'affichage (INPUT, SELECT, TEXTAREA, XML...).
	Pour que les retours à la lignes soient convertis en <br /> il faut coupler dette fontion à la fonction nl2br()
*/
function html($text)
{
	// Ne pas modifier ce code à la légère : les résultats sont différents suivant que ce soit un affichage direct ou ajax, suivant la version de PHP (5.1 ou 5.3)...
	return (perso_mb_detect_encoding_utf8($text)) ? htmlspecialchars($text,ENT_COMPAT,'UTF-8') : utf8_encode(htmlspecialchars($text,ENT_COMPAT)) ;
}

/*
	Convertit l'utf-8 en windows-1252 pour compatibilité avec FPDF
*/
function pdf($text)
{
	mb_substitute_character(0x00A0);	// Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
	return mb_convert_encoding($text,'Windows-1252','UTF-8');
}

/*
	Convertit l'utf-8 en windows-1252 pour un export CSV compatible avec Ooo et Word.
*/
function csv($text)
{
	mb_substitute_character(0x00A0);	// Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
	return mb_convert_encoding($text,'Windows-1252','UTF-8');
}

/*
	Nettoie le BOM éventuel d'un fichier UTF-8
	Code inspiré de http://libre-d-esprit.thinking-days.net/2009/03/et-bom-le-script/
*/

function deleteBOM($file)
{
	$fcontenu = file_get_contents($file);
	if (substr($fcontenu,0,3) == "\xEF\xBB\xBF")	// Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
	{
		file_put_contents($file, substr($fcontenu,3));	// Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
	}
}

/*
	Effacer d'anciens fichiers temporaires sur le serveur
	On transmet en paramètre à la fonction : le dossier à vider + le délai d'expiration en minutes
*/

function effacer_fichiers_temporaires($dossier,$nb_minutes)
{
	$date_limite = time() - $nb_minutes*60;
	$tab_fichier = scandir($dossier);
	unset($tab_fichier[0],$tab_fichier[1]);	// fichiers '.' et '..'
	foreach($tab_fichier as $fichier_nom)
	{
		$chemin_fichier = $dossier.'/'.$fichier_nom;
		$extension = pathinfo($chemin_fichier,PATHINFO_EXTENSION);
		$date_unix = filemtime($chemin_fichier);
		if( (is_file($chemin_fichier)) && ($date_unix<$date_limite) && ($extension!='htm') )
		{
			unlink($chemin_fichier);
		}
	}
}

?>