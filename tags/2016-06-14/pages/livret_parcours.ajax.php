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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$parcours_code = (isset($_POST['f_parcours'])) ? Clean::ref($_POST['f_parcours'])  : '';
$action        = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])  : '';
$parcours_id   = (isset($_POST['f_id']))       ? Clean::entier($_POST['f_id'])     : 0;
$page_ref      = (isset($_POST['f_page']))     ? Clean::id($_POST['f_page'])       : '';
$groupe_id     = (isset($_POST['f_groupe']))   ? Clean::entier($_POST['f_groupe']) : 0;
$prof_id       = (isset($_POST['f_prof']))     ? Clean::entier($_POST['f_prof'])   : 0;

if( !$parcours_code || !$page_ref || !$groupe_id || !$prof_id || !DB_STRUCTURE_LIVRET::DB_tester_page_avec_dispositif( $page_ref , 'parcours' , $parcours_code ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') || ($action=='dupliquer') )
{
  // Vérifier que le parcours est disponible
  if( DB_STRUCTURE_LIVRET::DB_tester_parcours( $parcours_code , $page_ref , $groupe_id ) )
  {
    Json::end( FALSE , 'Parcours déjà existant sur cette classe !' );
  }
  // Insérer l'enregistrement
  $parcours_id = DB_STRUCTURE_LIVRET::DB_ajouter_parcours( $parcours_code , $page_ref , $groupe_id , $prof_id );
  // Afficher le retour
  Json::add_str('<tr id="id_'.$parcours_id.'" class="new">');
  Json::add_str(  '<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str(  '<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str(  '<td data-id="'.$prof_id.'">{{PROF_NOM}}</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier ce parcours."></q>');
  Json::add_str(    '<q class="dupliquer" title="Dupliquer ce parcours."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer ce parcours."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un parcours existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $parcours_id )
{
  // Vérifier que le parcours est disponible
  if( DB_STRUCTURE_LIVRET::DB_tester_parcours( $parcours_code , $page_ref , $groupe_id , $parcours_id ) )
  {
    Json::end( FALSE , 'Parcours déjà existant sur cette classe !' );
  }
  // Mettre à jour l'enregistrement
  // Remarque : il est possible qu'il n'y ait aucun changement, on ne s'en préoccupe pas.
  DB_STRUCTURE_LIVRET::DB_modifier_parcours( $parcours_id , $parcours_code , $page_ref , $groupe_id , $prof_id );
  // Afficher le retour
  Json::add_str('<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str('<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str('<td data-id="'.$prof_id.'">{{PROF_NOM}}</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier ce parcours."></q>');
  Json::add_str(  '<q class="dupliquer" title="Dupliquer ce parcours."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer ce parcours."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un parcours existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $parcours_id )
{
  // On ne supprime pas d'éventuelles saisies, comme ça pas de risque en cas de mauvaise manipulation, le ménage sera de toutes façons fait avec l'initialisation annuelle.
  // Effacer l'enregistrement
  DB_STRUCTURE_LIVRET::DB_supprimer_parcours( $parcours_id );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
