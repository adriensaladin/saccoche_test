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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent que la base WEBMESTRE (donc une installation multi-structure).
// Ces méthodes ne concernent que le webmestre.

// Ce script est appelé automatiquement si besoin lorsque :
// - un webmestre vient de se connecter

class DB_WEBMESTRE_MAJ_BASE extends DB
{

/**
 * Retourner la version de la base du webmestre
 *
 * @param void
 * @return string
 */
public static function DB_version_base()
{
  // Au début, la table avec l'info de version n'existait pas
  $DB_TAB = DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME , 'SHOW TABLE STATUS LIKE "sacoche_parametre"');
  if(empty($DB_TAB))
  {
    return '2010-06-24';
  }
  $DB_SQL = 'SELECT parametre_valeur ';
  $DB_SQL.= 'FROM sacoche_parametre ';
  $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
  $DB_VAR = array(':parametre_nom'=>'version_base');
  return DB::queryOne(SACOCHE_WEBMESTRE_BD_NAME , $DB_SQL , $DB_VAR);
}


/**
 * Mettre à jour la base du webmestre
 *
 * @param string   $version_base_webmestre_actuelle
 * @return void
 */
public static function DB_maj_base($version_base_webmestre_actuelle)
{

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // On s'arrête si c'est un pb de fichier non récupéré ou de base inaccessible
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if( !VERSION_BASE_WEBMESTRE || !$version_base_webmestre_actuelle )
  {
    exit_error( 'Erreur MAJ BDD' /*titre*/ , 'Fichier avec version de la base webmestre manquant.' /*contenu*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2010-06-24 => 2013-02-15
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2010-06-24')
  {
    // Créer la table supplémentaire
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'CREATE TABLE sacoche_parametre ( parametre_nom    VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "", parametre_valeur VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", PRIMARY KEY (parametre_nom) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "version_base" , "" ) ' );
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-02-15';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification de champs
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE geo_id geo_id SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0" ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_localisation structure_localisation VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_id geo_id SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT  ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_ordre geo_ordre SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT "0"  ' );
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_geo CHANGE geo_nom geo_nom VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""  ' );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-02-15 => 2013-05-21
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-02-15')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-05-21';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_convention
    $reload_sacoche_convention = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_convention.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-05-21 => 2013-06-01
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-05-21')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-01';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table sacoche_partenaire
    $reload_sacoche_partenaire = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_partenaire.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-01 => 2013-06-08
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-01')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-08';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à la table sacoche_convention
    if(empty($reload_sacoche_convention))
    {
      DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_convention ADD convention_creation DATE DEFAULT NULL AFTER convention_date_fin ');
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-08 => 2013-06-09
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-08')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-06-09';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // rechargement de la table sacoche_convention si inexistante (bug à la création)
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_WEBMESTRE.'sacoche_convention.sql');
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , $requetes );
    DB::close(SACOCHE_WEBMESTRE_BD_NAME);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // MAJ 2013-06-09 => 2013-12-03
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  if($version_base_webmestre_actuelle=='2013-06-09')
  {
    // Actualisation date de version
    $version_base_webmestre_actuelle = '2013-12-03';
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_webmestre_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification d'un champ
    DB::query(SACOCHE_WEBMESTRE_BD_NAME , 'ALTER TABLE sacoche_structure CHANGE structure_contact_courriel structure_contact_courriel VARCHAR(63)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
  }

}

}
?>