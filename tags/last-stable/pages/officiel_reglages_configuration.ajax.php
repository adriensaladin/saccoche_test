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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$objet                          = (isset($_POST['objet']))                            ? $_POST['objet']                                           : '';

$releve_appreciation_rubrique   = (isset($_POST['f_releve_appreciation_rubrique']))   ? Clean::entier($_POST['f_releve_appreciation_rubrique'])   : 0;
$releve_appreciation_generale   = (isset($_POST['f_releve_appreciation_generale']))   ? Clean::entier($_POST['f_releve_appreciation_generale'])   : 0;
$releve_ligne_supplementaire    = (isset($_POST['f_releve_check_supplementaire']))    ? Clean::texte($_POST['f_releve_ligne_supplementaire'])     : '';
$releve_assiduite               = (isset($_POST['f_releve_assiduite']))               ? 1                                                         : 0;
$releve_retroactif              = (isset($_POST['f_releve_retroactif']))              ? Clean::texte($_POST['f_releve_retroactif'])               : '';
$releve_only_socle              = (isset($_POST['f_releve_only_socle']))              ? 1                                                         : 0;
$releve_etat_acquisition        = (isset($_POST['f_releve_etat_acquisition']))        ? 1                                                         : 0;
$releve_moyenne_scores          = (isset($_POST['f_releve_moyenne_scores']))          ? 1                                                         : 0;
$releve_pourcentage_acquis      = (isset($_POST['f_releve_pourcentage_acquis']))      ? 1                                                         : 0;
$releve_conversion_sur_20       = (isset($_POST['f_releve_conversion_sur_20']))       ? 1                                                         : 0;
$releve_cases_nb                = (isset($_POST['f_releve_cases_nb']))                ? Clean::entier($_POST['f_releve_cases_nb'])                : 0;
$releve_aff_coef                = (isset($_POST['f_releve_aff_coef']))                ? 1                                                         : 0;
$releve_aff_socle               = (isset($_POST['f_releve_aff_socle']))               ? 1                                                         : 0;
$releve_aff_domaine             = (isset($_POST['f_releve_aff_domaine']))             ? 1                                                         : 0;
$releve_aff_theme               = (isset($_POST['f_releve_aff_theme']))               ? 1                                                         : 0;
$releve_couleur                 = (isset($_POST['f_releve_couleur']))                 ? Clean::texte($_POST['f_releve_couleur'])                  : '';
$releve_legende                 = (isset($_POST['f_releve_legende']))                 ? Clean::texte($_POST['f_releve_legende'])                  : '';

$bulletin_appreciation_rubrique = (isset($_POST['f_bulletin_appreciation_rubrique'])) ? Clean::entier($_POST['f_bulletin_appreciation_rubrique']) : 0;
$bulletin_appreciation_generale = (isset($_POST['f_bulletin_appreciation_generale'])) ? Clean::entier($_POST['f_bulletin_appreciation_generale']) : 0;
$bulletin_ligne_supplementaire  = (isset($_POST['f_bulletin_check_supplementaire']))  ? Clean::texte($_POST['f_bulletin_ligne_supplementaire'])   : '';
$bulletin_assiduite             = (isset($_POST['f_bulletin_assiduite']))             ? 1                                                         : 0;
$bulletin_retroactif            = (isset($_POST['f_bulletin_retroactif']))            ? Clean::texte($_POST['f_bulletin_retroactif'])             : '';
$bulletin_only_socle            = (isset($_POST['f_bulletin_only_socle']))            ? 1                                                         : 0;
$bulletin_fusion_niveaux        = (isset($_POST['f_bulletin_fusion_niveaux']))        ? 1                                                         : 0;
$bulletin_barre_acquisitions    = (isset($_POST['f_bulletin_barre_acquisitions']))    ? 1                                                         : 0;
$bulletin_acquis_texte_nombre   = (isset($_POST['f_bulletin_acquis_texte_nombre']))   ? 1                                                         : 0;
$bulletin_acquis_texte_code     = (isset($_POST['f_bulletin_acquis_texte_code']))     ? 1                                                         : 0;
$bulletin_moyenne_scores        = (isset($_POST['f_bulletin_moyenne_scores']))        ? 1                                                         : 0;
$bulletin_conversion_sur_20     = (isset($_POST['f_bulletin_conversion_sur_20']))     ? Clean::entier($_POST['f_bulletin_conversion_sur_20'])     : 0; // Est transmis à 0 si f_bulletin_pourcentage coché
$bulletin_moyenne_classe        = (isset($_POST['f_bulletin_moyenne_classe']))        ? 1                                                         : 0;
$bulletin_moyenne_generale      = (isset($_POST['f_bulletin_moyenne_generale']))      ? 1                                                         : 0;
$bulletin_couleur               = (isset($_POST['f_bulletin_couleur']))               ? Clean::texte($_POST['f_bulletin_couleur'])                : '';
$bulletin_legende               = (isset($_POST['f_bulletin_legende']))               ? Clean::texte($_POST['f_bulletin_legende'])                : '';

$socle_appreciation_rubrique    = (isset($_POST['f_socle_appreciation_rubrique']))    ? Clean::entier($_POST['f_socle_appreciation_rubrique'])    : 0;
$socle_appreciation_generale    = (isset($_POST['f_socle_appreciation_generale']))    ? Clean::entier($_POST['f_socle_appreciation_generale'])    : 0;
$socle_ligne_supplementaire     = (isset($_POST['f_socle_check_supplementaire']))     ? Clean::texte($_POST['f_socle_ligne_supplementaire'])      : '';
$socle_assiduite                = (isset($_POST['f_socle_assiduite']))                ? 1                                                         : 0;
$socle_only_presence            = (isset($_POST['f_socle_only_presence']))            ? 1                                                         : 0;
$socle_pourcentage_acquis       = (isset($_POST['f_socle_pourcentage_acquis']))       ? 1                                                         : 0;
$socle_etat_validation          = (isset($_POST['f_socle_etat_validation']))          ? 1                                                         : 0;
$socle_couleur                  = (isset($_POST['f_socle_couleur']))                  ? Clean::texte($_POST['f_socle_couleur'])                   : '';
$socle_legende                  = (isset($_POST['f_socle_legende']))                  ? Clean::texte($_POST['f_socle_legende'])                   : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement des formulaires "Relevé d'évaluations" + "Bulletin scolaire" + "État de maîtrise du socle"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_variables = array(
  'releve'   => array( 'appreciation_rubrique', 'appreciation_generale', 'ligne_supplementaire', 'assiduite', 'retroactif', 'only_socle', 'etat_acquisition', 'moyenne_scores', 'pourcentage_acquis', 'conversion_sur_20', 'cases_nb', 'aff_coef', 'aff_socle', 'aff_domaine', 'aff_theme', 'couleur', 'legende' ),
  'bulletin' => array( 'appreciation_rubrique', 'appreciation_generale', 'ligne_supplementaire', 'assiduite', 'retroactif', 'only_socle', 'fusion_niveaux', 'barre_acquisitions', 'acquis_texte_code', 'acquis_texte_nombre', 'moyenne_scores', 'conversion_sur_20', 'moyenne_classe', 'moyenne_generale', 'couleur', 'legende' ),
  'socle'    => array( 'appreciation_rubrique', 'appreciation_generale', 'ligne_supplementaire', 'assiduite', 'only_presence', 'pourcentage_acquis', 'etat_validation', 'couleur', 'legende' ),
);

if( isset($tab_variables[$objet]) )
{
  $tab_parametres = array();
  foreach( $tab_variables[$objet] as $option )
  {
    $variable_nom    = $objet.'_'.$option;
    $variable_valeur = ${$variable_nom};
    // On modifie la session
    $_SESSION['OFFICIEL'][strtoupper($variable_nom)] = $variable_valeur;
    // Pour modifier dans la base
    $tab_parametres['officiel_'.$variable_nom] = $variable_valeur;
  }
  DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
