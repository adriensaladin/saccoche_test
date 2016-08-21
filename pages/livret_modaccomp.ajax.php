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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='initialiser')){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action      = (isset($_POST['f_action'])) ? $_POST['f_action']            : '';
$modaccomp   = (isset($_POST['f_modaccomp']))  ? $_POST['f_modaccomp']     : '';
$commentaire = (isset($_POST['f_commentaire']))  ? $_POST['f_commentaire'] : '';
// Avant c'était un tableau qui est transmis, mais à cause d'une limitation possible "suhosin" / "max input vars", on est passé à une concaténation en chaine...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

//
// Retirer une association
//

if($action=='retirer')
{
  // dispositif & élève
  if( !$modaccomp || empty($tab_eleve) )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // go
  $eleve_id = $tab_eleve[0];
  DB_STRUCTURE_LIVRET::DB_supprimer_eleve_modaccomp( $modaccomp , $eleve_id );
  // on s'arrête là
  Json::end( TRUE );
}

//
// Modifier un commentaire
//

if($action=='modifier_commentaire')
{
  // dispositif & élève & commentaire
  if( empty($tab_eleve) || !$commentaire )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // go
  $eleve_id = $tab_eleve[0];
  DB_STRUCTURE_LIVRET::DB_modifier_eleve_modaccomp( 'PPRE' , $eleve_id , $commentaire );
  // on s'arrête là
  Json::end( TRUE );
}

//
// Modifier des associations
//

if($action=='associer')
{
  // dispositif
  if(!DB_STRUCTURE_LIVRET::DB_tester_modaccomp($modaccomp))
  {
    Json::end( FALSE , 'Modalité "'.$objet.'" inconnue !' );
  }
  // liste des élèves
  if(empty($tab_eleve))
  {
    Json::end( FALSE , 'Aucun compte élève récupéré !' );
  }
  // commentaire
  if( ($modaccomp=='PPRE') && !$commentaire )
  {
    Json::end( FALSE , 'Descriptif du PPRE manquant !' );
  }
  if( ($modaccomp!='PPRE') && $commentaire )
  {
    $commentaire = '';
  }
  // go
  foreach($tab_eleve as $eleve_id)
  {
    DB_STRUCTURE_LIVRET::DB_modifier_eleve_modaccomp( $modaccomp , $eleve_id , $commentaire );
  }
}

//
// Affichage du bilan des affectations des dispositifs aux élèves
//

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_modaccomp();
if(empty($DB_TAB))
{
  Json::end( TRUE  , '<tr class="vide"><td class="nu" colspan="4"></td><td class="nu"></td></tr>' );
}
foreach($DB_TAB as $DB_ROW)
{
  $q_modifier = ($DB_ROW['info_complement']) ? '<q class="modifier" title="Modifier ce commentaire."></q>' : '' ;
  Json::add_str('<tr id="id_'.$DB_ROW['user_id'].'_'.$DB_ROW['livret_modaccomp_code'].'">');
  Json::add_str(  '<td>'.html($DB_ROW['groupe_nom']).'</td>');
  Json::add_str(  '<td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</td>');
  Json::add_str(  '<td>'.$DB_ROW['livret_modaccomp_code'].'</td>');
  Json::add_str(  '<td>'.html($DB_ROW['info_complement']).$q_modifier.'</td>');
  Json::add_str(  '<td class="nu">');
  Json::add_str(    '<q class="supprimer" title="Retirer ce dispositif (aucune confirmation ne sera demandée)."></q>');
  Json::add_str(  '</td>');
  Json::add_str('</tr>');
}
Json::end( TRUE );
?>
