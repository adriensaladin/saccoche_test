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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.

class DB_STRUCTURE_SIECLE extends DB
{

/**
 * ajouter_import
 * Enregistre le XML transmis en JSON.
 *
 * @param string   $import_objet
 * @param string   $import_annee
 * @param array    $import_xml
 * @return void
 */
public static function DB_ajouter_import( $import_objet , $import_annee , $import_xml )
{
  $DB_SQL = 'UPDATE sacoche_siecle_import ';
  $DB_SQL.= 'SET siecle_import_date=:import_date, siecle_import_annee=:import_annee, siecle_import_contenu=:import_contenu ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array(
    ':import_objet'   => $import_objet,
    ':import_date'    => TODAY_MYSQL,
    ':import_annee'   => $import_annee,
    ':import_contenu' => json_encode( (array)$import_xml ),
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_import_contenu
 * Retourne le JSON enregistré en ARRAY
 *
 * @param string   $import_objet
 * @return array
 */
public static function DB_recuperer_import_contenu( $import_objet )
{
  $DB_SQL = 'SELECT siecle_import_contenu ';
  $DB_SQL.= 'FROM sacoche_siecle_import ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array( ':import_objet' => $import_objet );
  $import_json = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return json_decode( $import_json , TRUE );
}

/**
 * recuperer_import_date_annee
 * Retourne le JSON enregistré en ARRAY
 *
 * @param string   $import_objet
 * @return array
 */
public static function DB_recuperer_import_date_annee( $import_objet )
{
  $DB_SQL = 'SELECT siecle_import_date, siecle_import_annee ';
  $DB_SQL.= 'FROM sacoche_siecle_import ';
  $DB_SQL.= 'WHERE siecle_import_objet=:import_objet ';
  $DB_VAR = array( ':import_objet' => $import_objet );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>