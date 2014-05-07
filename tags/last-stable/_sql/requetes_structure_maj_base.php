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

// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes servent à mettre à jour la base.

// Ce script est appelé automatiquement si besoin lorsque :
// - un administrateur vient de restaurer une base
// - un utilisateur vient de se connecter

// La méthode DB_version_base(), déjà définie dans la classe DB_STRUCTURE_PUBLIC, est redéfinie ici.
// Elle est invoquée systématiquement à chaque étape, au cas où des mises à jour simultanées seraient lancées (c'est déjà arrivé) malgré les précautions prises (fichier de blocage).

class DB_STRUCTURE_MAJ_BASE extends DB
{

  /**
   * Retourner la version de la base de l'établissement
   *
   * @param void
   * @return string
   */
  public static function DB_version_base()
  {
    $DB_SQL = 'SELECT parametre_valeur ';
    $DB_SQL.= 'FROM sacoche_parametre ';
    $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
    $DB_VAR = array(':parametre_nom'=>'version_base');
    return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }


  /**
   * Mettre à jour la base de l'établissement
   *
   * @param string   $version_base_structure_actuelle
   * @return void
   */
  public static function DB_maj_base($version_base_structure_actuelle)
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // On s'arrête si c'est un pb de fichier non récupéré ou de base inaccessible
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( !VERSION_BASE_STRUCTURE || !$version_base_structure_actuelle )
    {
      exit_error( 'Erreur MAJ BDD' /*titre*/ , 'Fichier avec version de la base manquant, ou base inaccessible.' /*contenu*/ );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Le fichier commençant à devenir volumineux, les mises à jour ont été archivées par années dans des fichiers séparés.
    // Lors d'un changement d'année n -> n+1, la mise à jour s'effectue dans le fichier n+1 (les 2 sont en fait possible, mais cela évite d'oublier sa création).
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $annee_version_actuelle = (int)substr($version_base_structure_actuelle,0,4);
    $annee_version_derniere = (int)substr(VERSION_BASE_STRUCTURE,0,4);

    for( $annee=$annee_version_actuelle ; $annee<=$annee_version_derniere ; $annee++ )
    {
      require(CHEMIN_DOSSIER_SQL.'requetes_structure_maj_base_'.$annee.'.inc.php');
    }


  }

}
?>
