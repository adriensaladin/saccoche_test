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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2013-12-15 => 2014-01-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2013-12-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_pages_nb" , "optimise" )' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-07 => 2014-01-14
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2014-01-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-14';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de paramètres
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_professeurs" , "0000-00-00" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_eleves"      , "0000-00-00" )' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "date_last_import_parents"     , "0000-00-00" )' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-14 => 2014-01-20
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2014-01-14')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-20';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification sacoche_parametre (paramètres CAS pour ENT Cartable de Savoie)
    $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="connexion_nom"' );
    if($connexion_nom=='cartabledesavoie')
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="www.cartabledesavoie.com" WHERE parametre_nom="cas_serveur_host" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-20 => 2014-01-31
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2014-01-20')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-01-31';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression de la Note de Vie Scolaire des fiches brevet
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_brevet_epreuve WHERE brevet_epreuve_code=112 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_brevet_saisie ' ); // retirer seulement brevet_epreuve_code IN(112,255) ne suffit pas
    // matière renommée
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom="7 Autonomie et initiative" WHERE matiere_id=9908 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-01-31 => 2014-02-07
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2014-01-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-07';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // valeur renommée dans sacoche_niveau
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom="Classe pour l\'inclusion scolaire" WHERE niveau_id=21' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2014-02-07 => 2014-02-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($version_base_structure_actuelle=='2014-02-07')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2014-02-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // niveau ajouté
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 6, 0, 1, 68, "P3S", "", "Cycle SEGPA") ' );
    }
    // table sacoche_demande modifiée
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande CHANGE user_id eleve_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande ADD prof_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Dans le cas où l\'élève adresse sa demande à un prof donné." AFTER item_id ' );
  }
}

?>
