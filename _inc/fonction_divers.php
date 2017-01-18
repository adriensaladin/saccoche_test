<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// 
// VT et FF sont déjà nettoyés par Clean::ctrl()


/*
 * Réciproque de html()
 * 
 * @param string
 * @return string
 */
function html_decode($text)
{
  return htmlspecialchars_decode($text,ENT_COMPAT);
}

/*
 * Conversion des retours chariot en balises BR
 * 
 * @param string
 * @return string
 */
function convertCRtoBR($text)
{
  return str_replace( Clean::tab_crlf() , '<br />' , $text );
}

/*
 * Conversion des retours chariot en JS pour textarea
 * 
 * "\x0D" = CR  Carriage Return (retour de chariot)
 * "\x0A" = LF  Line Feed (saut de ligne / ligne suivante)
 * 
 * @param string
 * @return string
 */
function convertCRtoJS($text)
{
  return str_replace( Clean::tab_crlf() , '\n' , $text );
}

/**
 * Fonctions utilisées avec array_filter() ; teste si différent de FALSE et de NULL.
 * @return bool
 */
function non_vide($n)
{
  return ($n!==FALSE) && ($n!==NULL) ;
}

/**
 * Fonctions utilisées avec array_filter() ; teste si différent d'une chaîne de texte inconsistante.
 * @return bool
 */
function non_chaîne_vide($n)
{
  return strlen(trim($n)) ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent de zéro.
 * @return bool
 */
function non_zero($n)
{
  return $n!==0 ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si strictement positif.
 * @return bool
 */
function positif($n)
{
  return $n>0 ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent "X" (pas "PA" car désormais cela peut être saisi).
 * @return bool
 */
function sans_rien($note)
{
  return $note!='X' ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent de 2.
 * @return bool
 */
function is_renseigne($etat)
{
  return $etat!=2 ;
}

?>