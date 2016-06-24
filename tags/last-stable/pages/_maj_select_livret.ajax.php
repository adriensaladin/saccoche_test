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

// Mettre à jour l'élément de formulaire "select_professeurs"

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$select     = (isset($_POST['f_select']))     ? Clean::lettres($_POST['f_select'])     : '';
$page_ref   = (isset($_POST['f_page_ref']))   ? Clean::id(     $_POST['f_page_ref'])   : '';
$groupe_id  = (isset($_POST['f_groupe_id']))  ? Clean::entier( $_POST['f_groupe_id'])  : 0;
$matiere_id = (isset($_POST['f_matiere_id'])) ? Clean::entier( $_POST['f_matiere_id']) : 0;
$prof_id    = (isset($_POST['f_prof_id']))    ? Clean::entier( $_POST['f_prof_id'])    : 0;

if( ($select=='groupes') && $page_ref )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_OPT_groupes_for_page( $page_ref );
  $selection = $groupe_id;
}

else if( ($select=='matieres') && $groupe_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_groupe( $groupe_id );
  $selection = $matiere_id;
}

else if( ($select=='profs_matiere') && $groupe_id && $matiere_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe_matiere( $groupe_id , $matiere_id );
  $selection = $prof_id;
}

else if( ($select=='profs') && $groupe_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_profs_groupe_matiere( $groupe_id );
  $selection = $prof_id;
}

else
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// Affichage du retour.

$option_first = ( is_string($DB_TAB) || (count($DB_TAB)>1) ) ? '' : FALSE ;

Json::end( TRUE , HtmlForm::afficher_select( $DB_TAB , FALSE /*select_nom*/ , $option_first , $selection , FALSE /*optgroup*/ , FALSE /*multiple*/ ) );

?>
