<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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

class To
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /*
   * Convertir l'utf-8 en windows-1252 pour compatibilité avec FPDF
   * 
   * @param string
   * @return string
   */
  public static function pdf($text)
  {
    mb_substitute_character(0x00A0);  // Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
    return mb_convert_encoding($text,'Windows-1252','UTF-8');
  }

  /*
   * Convertir l'utf-8 en windows-1252 pour un export CSV compatible avec Ooo et Word.
   * 
   * @param string
   * @return string
   */
  public static function csv($text)
  {
    mb_substitute_character(0x00A0);  // Pour mettre " " au lieu de "?" en remplacement des caractères non convertis.
    return mb_convert_encoding($text,'Windows-1252','UTF-8');
  }

  /*
   * Convertir un contenu en UTF-8 si besoin ; à effectuer en particulier pour les imports tableur.
   * Remarque : si on utilise utf8_encode() ou mb_convert_encoding() sans le paramètre 'Windows-1252' ça pose des pbs pour '’' 'Œ' 'œ' etc.
   * 
   * @param string
   * @return string
   */
  public static function utf8($text)
  {
    return ( (!perso_mb_detect_encoding_utf8($text)) || (!mb_check_encoding($text,'UTF-8')) ) ? mb_convert_encoding($text,'UTF-8','Windows-1252') : $text ;
  }

  /**
   * Nettoie le BOM éventuel d'un contenu UTF-8.
   * Code inspiré de http://libre-d-esprit.thinking-days.net/2009/03/et-bom-le-script/
   * 
   * @param string
   * @return string
   */
  public static function deleteBOM($text)
  {
    return (substr($text,0,3) == "\xEF\xBB\xBF") ? substr($text,3) : $text ; // Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
  }

  /**
   * Echappe les caractères LaTeX.
   * 
   * @param string
   * @return string
   */
  public static function latex($text)
  {
    $tab_bad = array( '–' ,  '$' ,  '&' ,  '%' ,  '#' ,  '_' ,  '{' ,  '}' ,  '^' , '\\' );
    $tab_bon = array( '-' , '\$' , '\&' , '\%' , '\#' , '\_' , '\{' , '\}' , '\^' , '\textbackslash{}' );
    return str_replace( $tab_bad , $tab_bon , $text );
  }

}

?>