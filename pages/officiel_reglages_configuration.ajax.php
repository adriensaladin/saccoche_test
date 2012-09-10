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

$objet                          = (isset($_POST['objet']))                            ? $_POST['objet']                                          : '';

$releve_appreciation_rubrique   = (isset($_POST['f_releve_appreciation_rubrique']))   ? Clean::entier($_POST['f_releve_appreciation_rubrique'])   : 0;
$releve_appreciation_generale   = (isset($_POST['f_releve_appreciation_generale']))   ? Clean::entier($_POST['f_releve_appreciation_generale'])   : 0;
$releve_moyenne_scores          = (isset($_POST['f_releve_moyenne_scores']))          ? 1                                                        : 0;
$releve_pourcentage_acquis      = (isset($_POST['f_releve_pourcentage_acquis']))      ? 1                                                        : 0;
$releve_cases_nb                = (isset($_POST['f_releve_cases_nb']))                ? Clean::entier($_POST['f_releve_cases_nb'])                : 0;
$releve_aff_coef                = (isset($_POST['f_releve_aff_coef']))                ? 1                                                        : 0;
$releve_aff_socle               = (isset($_POST['f_releve_aff_socle']))               ? 1                                                        : 0;
$releve_aff_domaine             = (isset($_POST['f_releve_aff_domaine']))             ? 1                                                        : 0;
$releve_aff_theme               = (isset($_POST['f_releve_aff_theme']))               ? 1                                                        : 0;
$releve_couleur                 = (isset($_POST['f_releve_couleur']))                 ? Clean::texte($_POST['f_releve_couleur'])                  : '';
$releve_legende                 = (isset($_POST['f_releve_legende']))                 ? Clean::texte($_POST['f_releve_legende'])                  : '';

$bulletin_appreciation_rubrique = (isset($_POST['f_bulletin_appreciation_rubrique'])) ? Clean::entier($_POST['f_bulletin_appreciation_rubrique']) : 0;
$bulletin_appreciation_generale = (isset($_POST['f_bulletin_appreciation_generale'])) ? Clean::entier($_POST['f_bulletin_appreciation_generale']) : 0;
$bulletin_moyenne_scores        = (isset($_POST['f_bulletin_moyenne_scores']))        ? 1                                                        : 0;
$bulletin_note_sur_20           = (isset($_POST['f_bulletin_note_sur_20']))           ? Clean::entier($_POST['f_bulletin_note_sur_20'])           : 0; // Est transmis à 0 si f_bulletin_pourcentage coché
$bulletin_moyenne_classe        = (isset($_POST['f_bulletin_moyenne_classe']))        ? 1                                                        : 0;
$bulletin_moyenne_generale      = (isset($_POST['f_bulletin_moyenne_generale']))      ? 1                                                        : 0;
$bulletin_couleur               = (isset($_POST['f_bulletin_couleur']))               ? Clean::texte($_POST['f_bulletin_couleur'])                : '';
$bulletin_legende               = (isset($_POST['f_bulletin_legende']))               ? Clean::texte($_POST['f_bulletin_legende'])                : '';

$socle_appreciation_rubrique    = (isset($_POST['f_socle_appreciation_rubrique']))    ? Clean::entier($_POST['f_socle_appreciation_rubrique'])    : 0;
$socle_appreciation_generale    = (isset($_POST['f_socle_appreciation_generale']))    ? Clean::entier($_POST['f_socle_appreciation_generale'])    : 0;
$socle_only_presence            = (isset($_POST['f_socle_only_presence']))            ? 1                                                        : 0;
$socle_pourcentage_acquis       = (isset($_POST['f_socle_pourcentage_acquis']))       ? 1                                                        : 0;
$socle_etat_validation          = (isset($_POST['f_socle_etat_validation']))          ? 1                                                        : 0;
$socle_couleur                  = (isset($_POST['f_socle_couleur']))                  ? Clean::texte($_POST['f_socle_couleur'])                   : '';
$socle_legende                  = (isset($_POST['f_socle_legende']))                  ? Clean::texte($_POST['f_socle_legende'])                   : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire "Relevé d'évaluations"
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($objet=='releve')
{
	$tab_parametres = array();
	$tab_parametres['officiel_releve_appreciation_rubrique'] = $releve_appreciation_rubrique;
	$tab_parametres['officiel_releve_appreciation_generale'] = $releve_appreciation_generale;
	$tab_parametres['officiel_releve_moyenne_scores']        = $releve_moyenne_scores;
	$tab_parametres['officiel_releve_pourcentage_acquis']    = $releve_pourcentage_acquis;
	$tab_parametres['officiel_releve_cases_nb']              = $releve_cases_nb;
	$tab_parametres['officiel_releve_aff_coef']              = $releve_aff_coef;
	$tab_parametres['officiel_releve_aff_socle']             = $releve_aff_socle;
	$tab_parametres['officiel_releve_aff_domaine']           = $releve_aff_domaine;
	$tab_parametres['officiel_releve_aff_theme']             = $releve_aff_theme;
	$tab_parametres['officiel_releve_couleur']               = $releve_couleur;
	$tab_parametres['officiel_releve_legende']               = $releve_legende;
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE'] = $releve_appreciation_rubrique ;
	$_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE'] = $releve_appreciation_generale ;
	$_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES']        = $releve_moyenne_scores ;
	$_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS']    = $releve_pourcentage_acquis ;
	$_SESSION['OFFICIEL']['RELEVE_CASES_NB']              = $releve_cases_nb ;
	$_SESSION['OFFICIEL']['RELEVE_AFF_COEF']              = $releve_aff_coef ;
	$_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE']             = $releve_aff_socle ;
	$_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE']           = $releve_aff_domaine ;
	$_SESSION['OFFICIEL']['RELEVE_AFF_THEME']             = $releve_aff_theme ;
	$_SESSION['OFFICIEL']['RELEVE_COULEUR']               = $releve_couleur ;
	$_SESSION['OFFICIEL']['RELEVE_LEGENDE']               = $releve_legende ;
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire "Bulletin scolaire"
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($objet=='bulletin')
{
	$tab_parametres = array();
	$tab_parametres['officiel_bulletin_appreciation_rubrique'] = $bulletin_appreciation_rubrique;
	$tab_parametres['officiel_bulletin_appreciation_generale'] = $bulletin_appreciation_generale;
	$tab_parametres['officiel_bulletin_moyenne_scores']        = $bulletin_moyenne_scores;
	$tab_parametres['officiel_bulletin_note_sur_20']           = $bulletin_note_sur_20;
	$tab_parametres['officiel_bulletin_moyenne_classe']        = $bulletin_moyenne_classe;
	$tab_parametres['officiel_bulletin_moyenne_generale']      = $bulletin_moyenne_generale;
	$tab_parametres['officiel_bulletin_couleur']               = $bulletin_couleur;
	$tab_parametres['officiel_bulletin_legende']               = $bulletin_legende;
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE'] = $bulletin_appreciation_rubrique ;
	$_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE'] = $bulletin_appreciation_generale ;
	$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']        = $bulletin_moyenne_scores ;
	$_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']           = $bulletin_note_sur_20 ;
	$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']        = $bulletin_moyenne_classe ;
	$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']      = $bulletin_moyenne_generale ;
	$_SESSION['OFFICIEL']['BULLETIN_COULEUR']               = $bulletin_couleur ;
	$_SESSION['OFFICIEL']['BULLETIN_LEGENDE']               = $bulletin_legende ;
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire "État de maîtrise du socle"
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($objet=='socle')
{
	$tab_parametres = array();
	$tab_parametres['officiel_socle_appreciation_rubrique']   = $socle_appreciation_rubrique;
	$tab_parametres['officiel_socle_appreciation_generale']   = $socle_appreciation_generale;
	$tab_parametres['officiel_socle_only_presence']           = $socle_only_presence;
	$tab_parametres['officiel_socle_pourcentage_acquis']      = $socle_pourcentage_acquis;
	$tab_parametres['officiel_socle_etat_validation']         = $socle_etat_validation;
	$tab_parametres['officiel_socle_couleur']                 = $socle_couleur;
	$tab_parametres['officiel_socle_legende']                 = $socle_legende;
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE']   = $socle_appreciation_rubrique ;
	$_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE']   = $socle_appreciation_generale ;
	$_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE']           = $socle_only_presence ;
	$_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS']      = $socle_pourcentage_acquis ;
	$_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION']         = $socle_etat_validation ;
	$_SESSION['OFFICIEL']['SOCLE_COULEUR']                 = $socle_couleur ;
	$_SESSION['OFFICIEL']['SOCLE_LEGENDE']                 = $socle_legende ;
	exit('ok');
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

else
{
	echo'Erreur avec les données transmises !';
}
?>
