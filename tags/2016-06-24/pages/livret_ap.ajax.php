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

$action       = (isset($_POST['f_action']))    ? Clean::texte( $_POST['f_action'])    : '';
$ap_id        = (isset($_POST['f_id']))        ? Clean::entier($_POST['f_id'])        : 0;
$page_ref     = (isset($_POST['f_page']))      ? Clean::id(    $_POST['f_page'])      : '';
$groupe_id    = (isset($_POST['f_groupe']))    ? Clean::entier($_POST['f_groupe'])    : 0;
$nombre       = (isset($_POST['f_nombre']))    ? Clean::entier($_POST['f_nombre'])    : 0;
$matiere_id_1 = (isset($_POST['f_matiere_1'])) ? Clean::entier($_POST['f_matiere_1']) : 0;
$prof_id_1    = (isset($_POST['f_prof_1']))    ? Clean::entier($_POST['f_prof_1'])    : 0;
$matiere_id_2 = (isset($_POST['f_matiere_2'])) ? Clean::entier($_POST['f_matiere_2']) : 0;
$prof_id_2    = (isset($_POST['f_prof_2']))    ? Clean::entier($_POST['f_prof_2'])    : 0;
$matiere_id_3 = (isset($_POST['f_matiere_3'])) ? Clean::entier($_POST['f_matiere_3']) : 0;
$prof_id_3    = (isset($_POST['f_prof_3']))    ? Clean::entier($_POST['f_prof_3'])    : 0;
$matiere_id_4 = (isset($_POST['f_matiere_4'])) ? Clean::entier($_POST['f_matiere_4']) : 0;
$prof_id_4    = (isset($_POST['f_prof_4']))    ? Clean::entier($_POST['f_prof_4'])    : 0;
$matiere_id_5 = (isset($_POST['f_matiere_5'])) ? Clean::entier($_POST['f_matiere_5']) : 0;
$prof_id_5    = (isset($_POST['f_prof_5']))    ? Clean::entier($_POST['f_prof_5'])    : 0;
$titre        = (isset($_POST['f_titre']))     ? Clean::texte( $_POST['f_titre'])     : '';

$test2 = ( ($nombre>=2) && ( !$matiere_id_2 || !$prof_id_2 ) ) ? TRUE : FALSE ;
$test3 = ( ($nombre>=3) && ( !$matiere_id_3 || !$prof_id_3 ) ) ? TRUE : FALSE ;
$test4 = ( ($nombre>=4) && ( !$matiere_id_4 || !$prof_id_4 ) ) ? TRUE : FALSE ;
$test5 = ( ($nombre>=5) && ( !$matiere_id_5 || !$prof_id_5 ) ) ? TRUE : FALSE ;

if( !$page_ref || !$groupe_id || ($nombre<1) || ($nombre>5) || !$matiere_id_1 || !$prof_id_1 || $test2 || $test3 || $test4 || $test5 || !$titre || !DB_STRUCTURE_LIVRET::DB_tester_page_avec_dispositif( $page_ref , 'ap' ) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

$tab_matiere_prof = array( $matiere_id_1.'~'.$prof_id_1 , $matiere_id_2.'~'.$prof_id_2 , $matiere_id_3.'~'.$prof_id_3 , $matiere_id_4.'~'.$prof_id_4 , $matiere_id_5.'~'.$prof_id_5 );
if( count(array_unique($tab_matiere_prof)) != $nombre+1 )
{
  Json::end( FALSE , 'Couples { matière / enseignant } identiques !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouvel accompagnement personnalisé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') || ($action=='dupliquer') )
{
  // Vérifier que l'accompagnement personnalisé est disponible
  // => Sans objet car il peut y avoir plusieurs AP sur une même classe.
  // Insérer l'enregistrement
  $ap_id = DB_STRUCTURE_LIVRET::DB_ajouter_ap( $page_ref , $groupe_id , $titre );
  $tab_matiere_prof_id = array();
  for( $i=1 ; $i<=$nombre ; $i++ )
  {
    $tab_matiere_prof_id[$i] = ${'matiere_id_'.$i}.'_'.${'prof_id_'.$i};
    DB_STRUCTURE_LIVRET::DB_ajouter_ap_jointure( $ap_id , ${'matiere_id_'.$i} , ${'prof_id_'.$i} );
  }
  // Afficher le retour
  Json::add_str('<tr id="id_'.$ap_id.'" class="new">');
  Json::add_str(  '<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str(  '<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str(  '<td data-id="'.implode(' ',$tab_matiere_prof_id).'">{{MATIERE_PROF_NOM}}</td>');
  Json::add_str(  '<td>'.html($titre).'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="modifier" title="Modifier cet A.P."></q>');
  Json::add_str(    '<q class="dupliquer" title="Dupliquer cet A.P."></q>');
  Json::add_str(    '<q class="supprimer" title="Supprimer cet A.P."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un accompagnement personnalisé existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $ap_id )
{
  // Vérifier que l'accompagnement personnalisé est disponible
  // => Sans objet car il peut y avoir plusieurs AP sur une même classe.
  // Mettre à jour l'enregistrement
  // Remarque : il est possible qu'il n'y ait aucun changement, on ne s'en préoccupe pas.
  // Remarque : on ne fait pas dans la dentelle pour les jointures : on les supprime et on les crée de nouveau.
  DB_STRUCTURE_LIVRET::DB_modifier_ap( $ap_id , $page_ref , $groupe_id , $titre );
  DB_STRUCTURE_LIVRET::DB_supprimer_ap_jointure( $ap_id );
  for( $i=1 ; $i<=$nombre ; $i++ )
  {
    $tab_matiere_prof_id[$i] = ${'matiere_id_'.$i}.'_'.${'prof_id_'.$i};
    DB_STRUCTURE_LIVRET::DB_ajouter_ap_jointure( $ap_id , ${'matiere_id_'.$i} , ${'prof_id_'.$i} );
  }
  // Afficher le retour
  Json::add_str('<td data-id="'.$page_ref.'">{{PAGE_MOMENT}}</td>');
  Json::add_str('<td data-id="'.$groupe_id.'">{{GROUPE_NOM}}</td>');
  Json::add_str('<td data-id="'.implode(' ',$tab_matiere_prof_id).'">{{MATIERE_PROF_NOM}}</td>');
  Json::add_str('<td>'.html($titre).'</td>');
  Json::add_str('<td class="nu">');
  Json::add_str(  '<q class="modifier" title="Modifier cet A.P."></q>');
  Json::add_str(  '<q class="dupliquer" title="Dupliquer cet A.P."></q>');
  Json::add_str(  '<q class="supprimer" title="Supprimer cet A.P."></q>');
  Json::add_str('</td>');
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un accompagnement personnalisé existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $ap_id )
{
  // On ne supprime pas d'éventuelles saisies, comme ça pas de risque en cas de mauvaise manipulation, le ménage sera de toutes façons fait avec l'initialisation annuelle.
  // Effacer l'enregistrement
  DB_STRUCTURE_LIVRET::DB_supprimer_ap( $ap_id );
  // Afficher le retour
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
