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

class OutilCSV
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Retourner un tableau de lignes à partir d'un texte en se basant sur les retours chariot.
   * Utilisé notamment lors de la récupération d'un fichier CSV.
   * 
   * @param string   $texte
   * @return array
   */
  public static function extraire_lignes($texte)
  {
    $texte = trim($texte);
    $texte = str_replace('"','',$texte);
    $texte = str_replace(array("\r\n","\n\n","\r\r","\r","\n"),'®',$texte);
    return explode('®',$texte);
  }

  /**
   * Déterminer la nature du séparateur d'un fichier CSV.
   * 
   * @param string   $ligne   la première ligne du fichier
   * @return string
   */
  public static function extraire_separateur($ligne)
  {
    $tab_separateur = array( ';'=>0 , ','=>0 , ':'=>0 , "\t"=>0 );
    foreach($tab_separateur as $separateur => $occurrence)
    {
      $tab_separateur[$separateur] = mb_substr_count($ligne,$separateur);
    }
    arsort($tab_separateur);
    reset($tab_separateur);
    return key($tab_separateur);
  }

}
?>